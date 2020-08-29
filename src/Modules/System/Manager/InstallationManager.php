<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/05/2020
 * Time: 13:25
 */
namespace App\Modules\System\Manager;

use App\Manager\StatusManager;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\Assess\Entity\Scale;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Form\Entity\MySQLSettings;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use function apache_get_modules;

/**
 * Class InstallationManager
 * @package App\Modules\System\Manager
 */
class InstallationManager
{
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;
    
    /**
     * @var UrlHelper
     */
    private UrlHelper $urlHelper;

    /**
     * InstallationManager constructor.
     * @param Environment $twig
     * @param UrlHelper $urlHelper
     */
    public function __construct(Environment $twig, UrlHelper $urlHelper)
    {
        $this->twig = $twig;
        $this->urlHelper = $urlHelper;
        TranslationHelper::setDomain('System');
    }

    /**
     * check
     * @param array $systemRequirements
     * @return Response
     */
    public function check(array $systemRequirements): string
    {
        TranslationHelper::setDomain('System');
        if (false === realpath($this->getParameterPath(false)) && false !== realpath($this->getParameterPath(false).'.dist'))
        {
            $this->getLogger()->debug(TranslationHelper::translate('The parameter file needs to be created'));
            if (false === $this->copyDistFile('quoll')) {
                $this->getLogger()->error(TranslationHelper::translate('Not able to copy the parameter file.'));
                return $this->twig->render('error/error.html.twig',
                    [
                        'extendedError' => 'Unable to copy the quoll.yaml file from the distribution file in /config/packages directory.',
                        'extendedParams' => [],
                        'manager' => $this,
                    ]
                );
            } else {
                $config = $this->readParameterFile();
                $config['parameters']['absoluteURL'] = str_replace('/installation/check/', '', $this->urlHelper->getAbsoluteUrl('/installation/check/'));
                $config['parameters']['guid'] = str_replace(['{','-','}'], '', uniqid("", true));
                $config['parameters']['timezone'] = ini_get('date.timezone');
                $this->writeParameterFile($config);
                $this->getLogger()->notice(TranslationHelper::translate('The parameter file has been created.'));
            }
        }

        $this->copyDistFile('role_hierarchy');

        $version = Yaml::parse(file_get_contents(__DIR__.'/../../../../config/packages/version.yaml'));
        $version = $version['parameters'];

        $ready = true;

        $systemDisplay = [];
        $systemDisplay['System Requirements'] = [];
        $systemDisplay['System Requirements']['PHP Version'] = [];
        $systemDisplay['System Requirements']['PHP Version']['name'] = 'PHP Version';
        $systemDisplay['System Requirements']['PHP Version']['comment'] = 'Quoll {version} requires PHP Version {php_version} or higher';
        $systemDisplay['System Requirements']['PHP Version']['comment_params'] = ['{version}' => $version['version'], '{php_version}' => $systemRequirements['php']];
        $systemDisplay['System Requirements']['PHP Version']['detail'] = PHP_VERSION;
        $systemDisplay['System Requirements']['PHP Version']['result'] = version_compare(PHP_VERSION, $systemRequirements['php'], '>=');
        $ready &= $systemDisplay['System Requirements']['PHP Version']['result'];

        $systemDisplay['System Requirements']['MySQL PDO Support'] = [];
        $systemDisplay['System Requirements']['MySQL PDO Support']['name'] = 'MySQL PDO Support';
        $systemDisplay['System Requirements']['MySQL PDO Support']['comment'] = '';
        $systemDisplay['System Requirements']['MySQL PDO Support']['detail'] = extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed';
        $systemDisplay['System Requirements']['MySQL PDO Support']['result'] = extension_loaded('pdo_mysql');
        $ready &= $systemDisplay['System Requirements']['MySQL PDO Support']['result'];

        $apacheModules = apache_get_modules();
        $systemDisplay['System Requirements']['mod_rewrite'] = [];
        $systemDisplay['System Requirements']['mod_rewrite']['name'] = 'Apache Module {name}';
        $systemDisplay['System Requirements']['mod_rewrite']['comment'] = '';
        $systemDisplay['System Requirements']['mod_rewrite']['detail'] = in_array('mod_rewrite', $apacheModules) ? 'Enabled' : 'N/A';
        $systemDisplay['System Requirements']['mod_rewrite']['result'] = in_array('mod_rewrite', $apacheModules);
        $ready &= $systemDisplay['System Requirements']['mod_rewrite']['result'];

        foreach($systemRequirements['extensions'] as $extension){
            $installed = extension_loaded($extension);
            $systemDisplay['System Requirements'][$extension] = [];
            $systemDisplay['System Requirements'][$extension]['name'] = 'PHP Extension {name}';
            $systemDisplay['System Requirements'][$extension]['comment'] = '';
            $systemDisplay['System Requirements'][$extension]['detail'] = $installed ? 'Installed' : 'Not Installed';
            $systemDisplay['System Requirements'][$extension]['result'] = $installed;
            $ready &= $systemDisplay['System Requirements'][$extension]['result'];
        }

        $message['class'] = 'success';
        $message['text'] = 'The directory containing the configuration files is writable, so the installation may proceed.';

        if ($this->getParameterPath() !== false && !is_writable($this->getParameterPath())) {
            $message['class'] = 'error';
            $message['text'] = 'The file quoll.yaml is not writable, so the installer cannot proceed.';
            $this->getLogger()->error(TranslationHelper::translate($message['text'] ));
        } else { //No config, so continue installer
            if (!is_writable(dirname($this->getParameterPath()))) { // Ensure that config directory is writable
                $message['class'] = 'error';
                $message['text'] = 'The directory containing the configuration files is not currently writable, so the installer cannot proceed.';
                $this->getLogger()->error(TranslationHelper::translate($message['text'] ));
            }
        }

        if (!$ready){
            $message['class'] = 'error';
            $message['text'] = 'One or more of the system requirements listed above is not configured correctly.';
            $this->getLogger()->error(TranslationHelper::translate($message['text'] ));
        }

        if ($message['class'] === 'success')
            $this->getLogger()->notice(TranslationHelper::translate($message['text'] ));

        return
            $this->twig->render('installation/check.html.twig',
                [
                    'systemRequirements' => $systemRequirements,
                    'systemDisplay' => $systemDisplay,
                    'ready' => $ready,
                    'message' => $message,
                ]
            )
            ;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Logger.
     *
     * @param LoggerInterface $logger
     * @return InstallationManager
     */
    public function setLogger(LoggerInterface $logger): InstallationManager
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * getParameterPath
     * @param bool $test
     * @param string $name
     * @return false|string
     */
    protected function getParameterPath(bool $test = true, string $name = 'quoll')
    {
        if ($test)
            return realpath(__DIR__ . '/../../../../config/packages/' . $name . '.yaml');
        return realpath(__DIR__ . '/../../../../config/packages') . '/' . $name . '.yaml';
    }

    /**
     * copyDistFile
     *
     * 19/08/2020 10:33
     * @param string $name
     * @return string|bool
     */
    protected function copyDistFile(string $name): string
    {
        if (false === $this->getParameterPath(true, $name)) {
            return copy($this->getParameterPath(false, $name).'.dist', $this->getParameterPath(false, $name));
        }
        return false;
    }

    /**
     * readParameterFile
     * @return array
     */
    private function readParameterFile(): array
    {
        if ($this->getParameterPath())
            return Yaml::parse(file_get_contents($this->getParameterPath()));
        return [];
    }

    /**
     * writeParameterFile
     * @param array $config
     */
    private function writeParameterFile(array $config)
    {
        if ($this->getParameterPath())
            file_put_contents($this->getParameterPath(), Yaml::dump($config, 8));
    }

    /**
     * getLocale
     * @return string
     */
    public function getLocale(): string
    {
        $config = $this->readParameterFile();
        return $config['parameters']['locale'] ?: 'en_GB';
    }

    /**
     * setLocale
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $config = $this->readParameterFile();
        $config['parameters']['locale'] = $locale;
        $this->getLogger()->notice(TranslationHelper::translate('The locale was set to {locale}', ['{locale}' => $locale]), ['locale' => $locale]);
        $this->writeParameterFile($config);
    }

    /**
     * setInstallationStatus
     * @param string $status
     */
    public function setInstallationStatus(string $status)
    {
        $config = $this->readParameterFile();
        $config['parameters']['installation']['status'] = $status;
        if ($status === 'complete') {
            $config['parameters']['installed'] = true;
            unset($config['parameters']['installation']);
        }
        $this->writeParameterFile($config);
        $this->getLogger()->notice(TranslationHelper::translate('The installation status was set to {status}.', ['{status}' => $status]));
    }

    /**
     * readCurrentMySQLSettings
     * @param MySQLSettings $setting
     */
    public function readCurrentMySQLSettings(MySQLSettings $setting): void
    {
        $config = $this->readParameterFile();

        $setting->setHost($config['parameters']['databaseServer'])
            ->setDbname($config['parameters']['databaseName'])
            ->setPort($config['parameters']['databasePort'])
            ->setUser($config['parameters']['databaseUsername'])
            ->setPassword($config['parameters']['databasePassword'])
            ->setPrefix($config['parameters']['databasePrefix']);

    }

    /**
     * setMySQLSettings
     *
     * 26/08/2020 15:58
     * @param FormInterface $form
     * @param StatusManager $status
     */
    public function setMySQLSettings(FormInterface $form, StatusManager $status)
    {
        $setting = $form->getData();
        $config = $this->readParameterFile();

        $config['parameters']['databaseServer']         = $setting->getHost();
        $config['parameters']['databaseName']           = $setting->getDbname();
        $config['parameters']['databasePort']           = $setting->getPort();
        $config['parameters']['databaseUsername']       = $setting->getUser();
        $config['parameters']['databasePassword']       = $setting->getPassword();
        $config['parameters']['databasePrefix']         = $setting->getPrefix();

        $this->writeParameterFile($config);


        $this->getLogger()->notice('The MySQL Database settings have been successfully tested and saved. You can now proceed to build the database.');

        $status->success('The MySQL Database settings have been successfully tested and saved. You can now proceed to build the database.', [],'System');
    }

    /**
     * setAdministrator
     * @param FormInterface $form
     */
    public function setAdministrator(FormInterface $form)
    {
        $user = ProviderFactory::getRepository(SecurityUser::class)->loadUserByUsernameOrEmail($form->get('username')->getData()) ?: new SecurityUser();
        $person = $user->getPerson() ?: new Person();
        $person->setTitle($form->get('title')->getData())
            ->setSurname($form->get('surname')->getData())
            ->setFirstName($form->get('firstName')->getData())
            ->setPreferredName($form->get('firstName')->getData())
            ->setStatus('Full')
            ->setSecurityUser($user)
            ->setOfficialName($form->get('firstName')->getData().' '.$form->get('surname')->getData());
        $encoder = new NativePasswordEncoder();

        $password = $encoder->encodePassword($form->get('password')->getData(), null);
        $user->setPerson($person)
            ->setUsername($form->get('username')->getData())
            ->setPassword($password)
            ->setCanLogin(true)
            ->setSuperUser(true)
            ->setPasswordForceReset(false)
            ->setSecurityRoles(['ROLE_SYSTEM_ADMIN']);
        $contact = $person->getContact() ?: new Contact($person);
        $contact->setEmail($form->get('email')->getData());
        $staff = $person->getStaff() ?: new Staff($person);
        $staff->setViewCalendarSchool(true)
            ->setViewCalendarSpaceBooking(true)
            ->setType('Support')
            ->setJobTitle('System Administrator');
        $pd = $person->getPersonalDocumentation() ?: new PersonalDocumentation($person);
        $em = ProviderFactory::getEntityManager();

        $em->persist($person);
        $em->flush();
        $settings = SettingFactory::getSettingManager();
        $settings->set('System','organisationAdministrator', $person);
        $settings->set('System','organisationDBA', $person);
        $settings->set('System','organisationAdmissions', $person);
        $settings->set('System','organisationHR', $person);

    }

    /**
     * setSystemSettings
     *
     * 27/08/2020 09:05
     * @param FormInterface $form
     */
    public function setSystemSettings(FormInterface $form)
    {
        $settingProvider = SettingFactory::getSettingManager();

        $settingProvider->set('System', 'systemName', $form->get('systemName')->getData());
        $settingProvider->set('System', 'installType', $form->get('installType')->getData());
        $settingProvider->set('System', 'organisationName', $form->get('organisationName')->getData());
        $settingProvider->set('System', 'organisationAbbreviation', $form->get('organisationAbbreviation')->getData());
        $settingProvider->set('System', 'currency', $form->get('currency')->getData());
        $settingProvider->set('System', 'country', $form->get('country')->getData());
        $settingProvider->set('System', 'timezone', $form->get('timezone')->getData());

        $scale = ProviderFactory::getRepository(Scale::class)->findOneBy(['abbreviation' => 'FLG']);
        $settingProvider->set('System', 'defaultAssessmentScale', $scale);

        $config = $this->readParameterFile();
        $config['parameters']['installed'] = true;
        $config['parameters']['install_date'] = date('Y-m-d');
        unset($config['parameters']['installation']);
        $this->writeParameterFile($config);
    }
}

<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
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

use App\Modules\People\Entity\Person;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Form\Entity\MySQLSettings;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

/**
 * Class InstallationManager
 * @package App\Modules\System\Manager
 */
class InstallationManager
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var UrlHelper
     */
    private $urlHelper;

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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function check(array $systemRequirements): string
    {
        TranslationHelper::setDomain('System');
        if (false === realpath($this->getParameterPath(false)) && false !== realpath($this->getParameterPath(false).'.dist'))
        {
            $this->getLogger()->debug(TranslationHelper::translate('The parameter file needs to be created'));
            if (false === copy($this->getParameterPath(false).'.dist', $this->getParameterPath(false))) {
                $this->getLogger()->error(TranslationHelper::translate('Not able to copy the parameter file.'));
                return $this->twig->render($this->twig->render('error/error.html.twig',
                    [
                        'extendedError' => 'Unable to copy the quoll.yaml file from the distribution file in /config/packages directory.',
                        'extendedParams' => [],
                        'manager' => $this,
                    ]
                ));
            } else {
                $config = $this->readParameterFile();
                $config['parameters']['absoluteURL'] = str_replace('/installation/check/', '', $this->urlHelper->getAbsoluteUrl('/installation/check/'));
                $config['parameters']['guid'] = str_replace(['{','-','}'], '', com_create_guid());
                $config['parameters']['timezone'] = ini_get('date.timezone');
                $this->writeParameterFile($config);
                $this->getLogger()->notice(TranslationHelper::translate('The parameter file has been created.'));
            }
        }

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

        $apacheModules = \apache_get_modules();
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
            dump($this->getParameterPath(),dirname($this->getParameterPath()));
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
     * @return false|string
     */
    protected function getParameterPath(bool $test = true)
    {
        if ($test)
            return realpath(__DIR__ . '/../../../../config/packages/quoll.yaml');
        return realpath(__DIR__ . '/../../../../config/packages') . '/quoll.yaml';
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
     * @param FormInterface $form
     * @return array
     */
    public function setMySQLSettings(FormInterface $form): array
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

        return [
            'status' => 'success',
            'errors' => [
                [
                    'class' => 'success',
                    'message' => TranslationHelper::translate('The MySQL Database settings have been successfully tested and saved. You can now proceed to build the database.')
                ]
            ]
        ];
    }
    /**
     * buildDatabase
     * @param KernelInterface $kernel
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function buildDatabase(KernelInterface $kernel, Request $request): Response
    {
        $this->manager->setLogger($this->getLogger());
        $this->manager->installation($kernel);
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $this->getLogger()->notice(TranslationHelper::translate('Module Installation commenced.'));

        $this->setInstallationStatus('system');
        $this->getLogger()->notice(TranslationHelper::translate('The database build was completed.'));

        return new RedirectResponse($request->server->get('REQUEST_SCHEME') . '://' . $request->server->get('SERVER_NAME') . '/install/installation/system/');
    }

    /**
     * setAdministrator
     * @param FormInterface $form
     */
    public function setAdministrator(FormInterface $form)
    {
        $person = ProviderFactory::getRepository(Person::class)->loadUserByUsernameOrEmail($form->get('username')->getData()) ?: new Person();
        $person->setTitle($form->get('title')->getData());
        $person->setSurname($form->get('surname')->getData());
        $person->setFirstName($form->get('firstName')->getData());
        $person->setPreferredName($form->get('firstName')->getData());
        $person->setOfficialName($form->get('firstName')->getData().' '.$form->get('surname')->getData());
        $person->setusername($form->get('username')->getData());
        $encoder = new NativePasswordEncoder();

        $password = $encoder->encodePassword($form->get('password')->getData(), null);
        $person->setPassword($password);
        $person->setStatus('Full');
        $person->setCanLogin('Y');
        $person->setPrimaryRole('ROLE_SYSTEM_ADMIN');
        $person->setEmail($form->get('email')->getData());
        $person->setViewCalendarSchool('Y');
        $person->setViewCalendarSpaceBooking('Y');
        $em = ProviderFactory::getEntityManager();
        $em->persist($person);
        $em->flush();

        $staff = new Staff();
        $staff->setType('Support')
            ->setJobTitle('System Administrator')
            ->setPerson($person);
        $em->persist($staff);
        $em->flush();
        new SecurityUser($person);
    }

    /**
     * setSystemSettings
     * @param FormInterface $form
     */
    public function setSystemSettings(FormInterface $form)
    {
        $settingProvider = ProviderFactory::create(Setting::class);

        $settingProvider->setSettingByScope('System', 'systemName', $form->get('systemName')->getData());
        $settingProvider->setSettingByScope('System', 'installType', $form->get('installType')->getData());
        $settingProvider->setSettingByScope('System', 'organisationName', $form->get('organisationName')->getData());
        $settingProvider->setSettingByScope('System', 'organisationNameShort', $form->get('organisationNameShort')->getData());
        $settingProvider->setSettingByScope('System', 'currency', $form->get('currency')->getData());
        $config = $this->readParameterFile();
        $config['parameters']['timezone'] = $form->get('timezone')->getData();
        $config['parameters']['country'] = $form->get('country')->getData();
        $config['parameters']['installed'] = true;
        $config['parameters']['install_date'] = date('Y-m-d');
        unset($config['parameters']['installation']);
        $this->writeParameterFile($config);
    }
}
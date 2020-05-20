<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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

use App\Util\TranslationHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
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
        $configFile = $this->getQuollParameterPath(false);
        TranslationHelper::setDomain('System');
        if (false === realpath($this->getQuollParameterPath(false)) && false !== realpath($this->getQuollParameterPath(false).'.dist'))
        {
            $this->getLogger()->debug(TranslationHelper::translate('The parameter file needs to be created'));
            if (false === copy($this->getQuollParameterPath(false).'.dist', $this->getQuollParameterPath(false))) {
                $this->getLogger()->error(TranslationHelper::translate('Not able to copy the parameter file.'));
                return $this->twig->render($this->twig->render('legacy/error.html.twig',
                    [
                        'extendedError' => 'Unable to copy the quoll.yaml file from the distribution file in /config/packages directory.',
                        'extendedParams' => [],
                        'manager' => $this,
                    ]
                ));
            } else {
                $config = $this->readQuollYaml();
                $config['parameters']['absoluteURL'] = str_replace('/installation/check/', '', $this->urlHelper->getAbsoluteUrl('/installation/check/'));
                $config['parameters']['guid'] = str_replace(['{','-','}'], '', com_create_guid());
                $this->writeQuollYaml($config);
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

        if ($this->getQuollParameterPath() !== false && !is_writable($this->getQuollParameterPath())) {
            $message['class'] = 'error';
            $message['text'] = 'The file quoll.yaml is not writable, so the installer cannot proceed.';
            $this->getLogger()->error(TranslationHelper::translate($message['text'] ));
        } else { //No config, so continue installer
            dump($this->getQuollParameterPath(),dirname($this->getQuollParameterPath()));
            if (!is_writable(dirname($this->getQuollParameterPath()))) { // Ensure that config directory is writable
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
     * getQuollParameterPath
     */
    protected function getQuollParameterPath(bool $test = true)
    {
        if ($test)
            return realpath(__DIR__ . '/../../../../config/packages/quoll.yaml');
        return realpath(__DIR__ . '/../../../../config/packages') . '/quoll.yaml';
    }

    /**
     * readQuollYaml
     * @return array
     */
    private function readQuollYaml(): array
    {
        if ($this->getQuollParameterPath())
            return Yaml::parse(file_get_contents($this->getQuollParameterPath()));
        return [];
    }

    /**
     * writeQuollYaml
     * @param array $config
     */
    private function writeQuollYaml(array $config)
    {
        if ($this->getQuollParameterPath())
            file_put_contents($this->getQuollParameterPath(), Yaml::dump($config, 8));
    }
}
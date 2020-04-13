<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 6/07/2019
 * Time: 14:50
 */

namespace App\Util;

use App\Session\GibbonSession;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GlobalHelper
 * @package App\Util
 */
class GlobalHelper
{
    /**
     * @var RequestStack
     */
    private static $stack;

    /**
     * @var ParameterBagInterface
     */
    private static $params;

    /**
     * GlobalHelper constructor.
     * @param RequestStack $stack
     */
    public function __construct(RequestStack $stack, ParameterBagInterface $params)
    {
        self::$stack = $stack;
        self::$params =  $params;
    }

    private static $page;

    /**
     * @return mixed
     */
    public static function getPage()
    {
        return self::$page;
    }

    /**
     * @param mixed $page
     */
    public static function setPage($page): void
    {
        self::$page = $page;
    }

    /**
     * getIPAddress
     * @return array|bool|false|string
     */
    public static function getIPAddress(string $ip = null)
    {
        if (null !== $ip)
            return $ip;

        $request = self::getRequest();
        if ($request->server->has('HTTP_CLIENT_IP'))
            return $request->server->get('HTTP_CLIENT_IP');
        else if($request->server->has('HTTP_X_FORWARDED_FOR'))
            return $request->server->get('HTTP_X_FORWARDED_FOR');
        else if($request->server->has('HTTP_X_FORWARDED'))
            return $request->server->get('HTTP_X_FORWARDED');
        else if($request->server->has('HTTP_FORWARDED_FOR'))
            return $request->server->get('HTTP_FORWARDED_FOR');
        else if($request->server->has('HTTP_FORWARDED'))
            return $request->server->get('HTTP_FORWARDED');
        else if($request->server->has('REMOTE_ADDR'))
            return $request->server->get('REMOTE_ADDR');

        return false;
    }

    /**
     * @var Request
     */
    private static $request;

    /**
     * getRequest
     * @return Request|null
     */
    public static function getRequest(bool $master = false): ?Request
    {
        if (!$master && (null === self::$request || self::$request !== self::$stack->getCurrentRequest()))
            self::$request = self::$stack->getCurrentRequest();
        if ($master && self::$request !== self::$stack->getMasterRequest())
            self::$request = self::$stack->getMasterRequest();
        return self::$request;
    }


    /**
     * readKookaburraYaml
     * @return array
     */
    public static function readKookaburraYaml(): array
    {
        $configFile = __DIR__ . '/../../config/packages/kookaburra.yaml';
        if (realpath($configFile))
            return Yaml::parse(file_get_contents($configFile));
        return [];
    }

    /**
     * writeKookaburraYaml
     * @param array $config
     */
    public static function writeKookaburraYaml(array $config): void
    {
        $configFile = __DIR__ . '/../../config/packages/kookaburra.yaml';
        if (realpath($configFile))
            file_put_contents($configFile, Yaml::dump($config, 8));
    }

    /**
     * num2alpha
     * @param $n
     * @return string
     */
    public static function num2alpha($n)
    {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n%26 + 0x41) . $r;
        }
        return $r;
    }

    /**
     * localAssetorURL
     * @param string $name
     */
    public static function localAssetURL(string $name)
    {
        if (stripos($name, 'http') === 0)
            return $name;

        //  Local Asset
        $path = realpath(__DIR__ . '/../../public');
        $asset = realpath($name);
        if (strpos($asset, $path) === 0)
        {
            return self::getRequest()->getUriForPath(str_replace($path, '', $asset));
        }
        // No Idea, so programmer to fix.
        return $name;
    }

    /**
     * @var SessionInterface
     */
    private static $session;

    /**
     * getSession
     * @return SessionInterface|null
     */
    public static function getSession(): ?SessionInterface
    {
        if (null === self::$session) {
            if (null === self::getRequest())
                return null;
            if (null === self::getRequest()->getSession()) {
                self::$session = new GibbonSession();
                if (!self::$session->isStarted())
                    self::$session->start();
                self::getRequest()->setSession(self::$session);
            }
            if (null === self::$session)
                self::$session = self::getRequest()->getSession();
        }

        return self::$session;
    }

    /**
     * hasParam
     * @param string $name
     * @return bool
     */
    public static function hasParam(string $name): bool
    {
        return self::$params->has($name);
    }

    /**
     * getParam
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public static function getParam(string $name, $default = null)
    {
        return self::hasParam($name) ? self::$params->get($name) : $default;
    }

    /**
     * getParameter
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public static function getParameter(string $name, $default = null)
    {
        return self::getParam($name, $default);
    }

    /**
     * getCurrentRoutePath
     * @return string
     */
    public static function getCurrentRoutePath(): string
    {
        return UrlGeneratorHelper::getUrl(self::getRequest()->get('_route'), self::getRequest()->get('_route_params'), true);
    }

}
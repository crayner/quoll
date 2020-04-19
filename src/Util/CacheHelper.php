<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/08/2019
 * Time: 14:10
 */

namespace App\Util;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CacheHelper
{
    /**
     * @var bool
     */
    private static $caching;

    /**
     * @var SessionInterface
     */
    private static $session;

    /**
     * CacheHelper constructor.
     * @param bool $caching
     */
    public function __construct(bool $caching)
    {
        self::$caching = $caching;
    }

    /**
     * @return SessionInterface
     */
    public static function getSession(): SessionInterface
    {
        return self::$session;
    }

    /**
     * setSession
     * @param SessionInterface $session
     */
    public static function setSession(SessionInterface $session)
    {
        self::$session = $session;
    }

    /**
     * isStale
     * @param string $name
     * @param int $interval
     * @return bool
     * @throws \Exception
     */
    public static function isStale(string $name, int $interval = 10): bool
    {
        if (!self::isCaching())
            return true;
        $interval = $interval + rand(0, $interval) - intval($interval/2);
        $cacheTime = self::getSession()->get(self::getCacheName($name), null);
        if (null === $cacheTime || $cacheTime->getTimestamp() < self::intervalDateTime($interval)->getTimestamp())
            return true;
        return false;
    }

    /**
     * intervalDateTime
     * @param int $interval
     * @return \DateTime
     * @throws \Exception
     */
    public static function intervalDateTime(int $interval): \DateTime
    {
        return new \DateTimeImmutable('- ' . strval($interval * 30 + rand(0, $interval * 60)) . ' Seconds');
    }

    /**
     * setCacheValue
     * @param string $name
     * @param $content
     * @param int $interval
     * @throws \Exception
     */
    public static function setCacheValue(string $name, $content, int $interval = 10)
    {
        if (self::isCaching()) {
            self::getSession()->set($name, $content);
            self::getSession()->set(self::getCacheName($name), new \DateTimeImmutable('+ ' . $interval . ' Minutes'));
        }
    }

    /**
     * getCacheValue
     * @param string $name
     * @return mixed
     */
    public static function getCacheValue(string $name)
    {
        if (self::isCaching())
            return self::getSession()->has($name) ? self::getSession()->get($name) : null;
        return null;
    }

    /**
     * @return bool
     */
    public static function isCaching(): bool
    {
        return self::$caching;
    }

    public static function clearCacheValue(string $name)
    {
        self::getSession()->clear($name);
        self::getSession()->clear(self::getCacheName($name));
    }

    /**
     * getCacheName
     * @param string $name
     * @return string
     */
    private static function getCacheName(string $name): string
    {
        return $name . '_cacheTime';
    }
}
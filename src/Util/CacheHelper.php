<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
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

use App\Manager\ParameterFileManager;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CacheHelper
 * @package App\Util
 * @author Craig Rayner <craig@craigrayner.com>
 *
 * All intervals expressed in this class are measured in minutes.
 */
class CacheHelper
{
    /**
     * @var bool
     */
    private static bool $caching = true;

    /**
     * @var SessionInterface
     */
    private static SessionInterface $session;

    /**
     * @param bool $caching
     */
    public static function setCaching(bool $caching): void
    {
        self::$caching = $caching;
    }

    /**
     * @return SessionInterface|null
     */
    public static function getSession(): ?SessionInterface
    {
        return isset(self::$session) ? self::$session : null;
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
     */
    public static function isStale(string $name, int $interval = 10): bool
    {
        if (!self::isCaching() || !self::$session->has($name)) return true;

        try {
            $cacheTime = self::getSession()->get(self::getCacheName($name));
            if (null === $cacheTime || $cacheTime->getTimestamp() < self::intervalDateTime($interval)->getTimestamp() || self::isDirty($name, $cacheTime)) {
                self::clearCacheValue($name);
                return true;
            }
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * intervalDateTime
     * @param int $interval
     * @return DateTimeImmutable
     * @throws Exception
     */
    public static function intervalDateTime(int $interval): DateTimeImmutable
    {
        // Convert to seconds and randomly set interval to 0.5 * $interval + rand(zero to interval) as a date time immutable
        return new DateTimeImmutable('- ' . strval($interval * 30 + rand(0, $interval * 60)) . ' Seconds');
    }

    /**
     * setCacheValue
     * @param string $name
     * @param $content
     * @param int $interval
     */
    public static function setCacheValue(string $name, $content, int $interval = 10)
    {
        if (self::isCaching()) {
            self::getSession()->set($name, $content);
            try {
                self::getSession()->set(self::getCacheName($name), new DateTimeImmutable('+ ' . $interval . ' Minutes'));
            } catch (Exception $e) {
                self::getSession()->remove(self::getCacheName($name));
            }
        }
    }

    /**
     * getCacheValue
     * @param string $name
     * @param int $interval
     * @return mixed
     */
    public static function getCacheValue(string $name, int $interval = 10)
    {
        if (self::isCaching() && !self::isStale($name, $interval))
            return self::getSession()->has($name) ? self::getSession()->get($name) : null;
        return null;
    }

    /**
     * @return bool
     */
    public static function isCaching(): bool
    {
        return self::$caching && self::getSession() !== null;
    }

    /**
     * clearCacheValue
     * @param string $name
     */
    public static function clearCacheValue(string $name)
    {
        if (self::getSession()) {
            self::getSession()->remove($name);
            self::getSession()->remove(self::getCacheName($name));
        }
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

    /**
     * isDirty
     *
     * 24/09/2020 09:42
     * @param string $name
     * @param DateTimeImmutable $cacheTime
     * @return bool
     */
    private static function isDirty(string $name, DateTimeImmutable $cacheTime): bool
    {
        $config = ParameterFileManager::readParameterFile();

        if (!key_exists('cache_dirty', $config['parameters'])) return false;
        if (!key_exists($name, $config['parameters']['cache_dirty'])) return false;

        return $cacheTime->setTimestamp() < $config['parameters']['cache_dirty']->getTimestamp();
    }

    /**
     * setCacheDirty
     *
     * 24/09/2020 09:34
     * @param string $name
     */
    public static function setCacheDirty(string $name)
    {
        $config = ParameterFileManager::readParameterFile();
        if (!key_exists('cache_dirty', $config['parameters'])) $config['parameters']['cache_dirty'] = [];
        $config['parameters']['cache_dirty'][$name] = new DateTimeImmutable('now');
        ParameterFileManager::writeParameterFile($config);
    }
}

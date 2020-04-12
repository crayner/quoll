<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 24/11/2018
 * Time: 14:00
 */
namespace App\Modules\System\Util;

use Doctrine\DBAL\Exception\DriverException;
use App\Modules\System\Entity\I18n;
use Kookaburra\UserAdmin\Entity\Person;
use App\Modules\System\Provider\I18nProvider;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use App\Modules\People\Util\UserHelper;
use Symfony\Component\Intl\Countries;

class LocaleHelper
{
    /**
     * @var string
     */
    private static $locale;

    /**
     * @var I18n|null
     */
    private static $entity;

    /**
     * @var I18nProvider
     */
    private static $provider;

    /**
     * LocaleHelper constructor.
     * @param string $locale
     */
    public function __construct(ProviderFactory $providerFactory, string $locale = null)
    {
        self::$locale = $locale;
        self::$provider = $providerFactory->getProvider(I18n::class);
        self::getLocale();
    }

    /**
     * getCurrentLocale
     * @return string|null
     */
    private static function getCurrentLocale()
    {
        $user = UserHelper::getCurrentUser();
        if ($user instanceof Person)
            self::$locale = ! empty($user->getI18nPersonal()) && ! empty($user->getI18nPersonal()->getCode()) ? $user->getI18nPersonal()->getCode() : self::$locale;

        if (null === self::$locale)
            self::getDefaultLocale('en_GB');
        if (null === self::$locale)
            self::$locale = 'en_GB';
        return self::$locale;
    }

    /**
     * getLocale
     * @param bool $refresh
     * @return string
     */
    public static function getLocale(bool $refresh = false): string
    {
        if (null === self::$locale || $refresh)
            self::$locale = self::getCurrentLocale();
        return self::$locale;
    }

    /**
     * getDefaultLocale
     * @param string $locale
     * @return string
     */
    public static function getDefaultLocale(string $locale): string
    {
        if ($locale !== 'en_GB' || empty(self::$provider))
            return $locale;
        try {
            return self::$provider->getRepository()->findSystemDefaultCode() ?: $locale;
        } catch (ConnectionException $e) {
            return $locale;
        } catch (\ErrorException $e) {
            return $locale;
        } catch (TableNotFoundException $e) {
            return $locale;
        }
    }

    /**
     * getCountryName
     * @param string $code
     * @return string
     */
    public static function getCountryName(string $code): string
    {
        return Countries::getName($code);
    }

    /**
     * getRtl
     * @param string $code
     * @return bool
     */
    public static function getRtl(string $code): bool
    {
        return self::getEntity($code) ? self::getEntity($code)->isRtl() : false;
    }

    /**
     * @return I18n|null
     */
    public static function getEntity(string $code): ?I18n
    {
        if (null === self::$entity) {
            try {
                self::$entity = self::$provider->getRepository()->findOneBy(['code' => $code]);
            } catch (DriverException $e) {
                self::$entity = null;
            }
        }
        return self::$entity;
    }
}
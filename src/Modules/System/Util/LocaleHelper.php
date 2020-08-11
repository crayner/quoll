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
 * Date: 15/04/2020
 * Time: 14:22
 */

namespace App\Modules\System\Util;

use App\Modules\System\Entity\Locale;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;

/**
 * Class LocaleHelper
 * @package App\Modules\System\Util
 */
class LocaleHelper
{
    /**
     * @var string
     */
    private static $locale = 'en_GB';

    /**
     * @var Locale|null
     */
    private static $localeEntity;

    /**
     * getLocale
     * @param Request|null $request
     * @return string
     */
    public static function getLocale(?Request $request = null): string
    {
        self::$locale = 'en_GB';
        if (null === $request) {
            $request = ProviderFactory::getRequest();
        }
        if ($request->getDefaultLocale() !== null)
            self::$locale = $request->getDefaultLocale();
        if ($request->getLocale() !== null)
            self::$locale = $request->getLocale();
        try {
            return ProviderFactory::create(Locale::class)->isValidLocaleCode(self::$locale) ? self::$locale : 'en_GB';
        } catch (\PDOException | PDOException | TableNotFoundException | DriverException $e) {
            return 'en_GB';
        }
    }

    /**
     * getCountryName
     * @param string $code
     * @return string
     */
    public static function getCountryName(string $code): string
    {
        if (strlen($code) === 3) {
            return Countries::getAlpha3Name($code);
        }
        return Countries::getName($code);
    }

    /**
     * getDateFormat
     * @param string $style
     * @return string
     * 9/08/2020 08:29
     */
    public static function getDateFormat(string $style = ''): string
    {
        // $style cane be "", php or regex
        $request = ProviderFactory::getRequest();
        self::$localeEntity = ProviderFactory::getRepository(Locale::class)->findOneByCode(self::getLocale($request));
        if (null === self::$localeEntity) {
            self::$localeEntity = ProviderFactory::getRepository(Locale::class)->findOneBySystemDefault(true);
        }
        if (strtolower($style) === 'php') {
            return self::$localeEntity->getDateFormatPHP();
        }
        if (strtolower($style) === 'regex') {
            return self::$localeEntity->getDateFormatRegEx();
        }
        return self::$localeEntity->getDateFormat();
    }
}

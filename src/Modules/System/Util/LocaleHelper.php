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
 * Date: 15/04/2020
 * Time: 14:22
 */

namespace App\Modules\System\Util;

use App\Modules\System\Entity\I18n;
use App\Provider\ProviderFactory;
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
     * getLocale
     * @param Request $request
     * @return string
     */
    public static function getLocale(Request $request): string
    {
        self::$locale = 'en_GB';
        if ($request->getDefaultLocale() !== null)
            self::$locale = $request->getDefaultLocale();
        if ($request->getLocale() !== null)
            self::$locale = $request->getLocale();
        return ProviderFactory::create(I18n::class)->isValidLocaleCode(self::$locale) ? self::$locale : 'en_GB';
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
}
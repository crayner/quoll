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
 * Date: 27/07/2019
 * Time: 13:58
 */

namespace App\Util;

use App\Modules\System\Entity\Locale;
use App\Provider\ProviderFactory;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Format
 * @package App\Util
 */
class Format
{
    /**
     * @var bool
     */
    private static $setup = false;

    /**
     * @var array
     */
    protected static $settings = [
        'dateFormatPHP'     => 'd/m/Y',
        'dateTimeFormatPHP' => 'd/m/Y H:i',
        'timeFormatPHP'     => 'H:i',
    ];

    /**
     * getSetting
     * @param string $name
     * @return mixed
     */
    public static function getSetting(string $name): ?string
    {
        return static::$settings[$name];
    }

    /**
     * Sets the formatting options from session i18n and database settings.
     * @param SessionInterface|null $session
     */
    public static function setupFromSession(?SessionInterface $session = null)
    {
        if (self::$setup || null === $session)
            return ;

        $settings = [];
        $settings['locale'] = $session->has('locale') ? $session->get('locale') : ProviderFactory::getRepository(Locale::class)->findOneBy(['systemDefault' => true]);
        $settings['dateFormatPHP'] = $settings['locale']->getDateFormatPHP();
        $settings['dateTimeFormatPHP'] = $settings['locale']->getDateFormatPHP() . ' H:i';
        $settings['timeFormatPHP'] = 'H:i';

        self::setup($settings);

        self::$setup = true;
    }

    /**
     * Sets the internal formatting options from an array.
     *
     * @param array $settings
     */
    public static function setup(array $settings)
    {
        static::$settings = array_replace(static::$settings, $settings);
    }

    /**
     * Formats a YYYY-MM-DD date with the language-specific format. Optionally provide a format string to use instead.
     *
     * @param DateTime|string $dateString
     * @param bool $format
     * @return string
     * @throws \Exception
     */
    public static function date(string $dateString, bool $format = false)
    {
        self::setupFromSession();
        if (empty($dateString)) return '';
        $date = static::createDateTime($dateString);
        return $date ? $date->format($format ? $format : static::$settings['dateFormatPHP']) : $dateString;
    }

    /**
     * createDateTime
     * @param $dateOriginal
     * @param null $expectedFormat
     * @param null $timezone
     * @return DateTimeImmutable
     * @throws \Exception
     */
    private static function createDateTime($dateOriginal, $expectedFormat = null, $timezone = null): DateTime
    {
        if ($dateOriginal instanceof DateTime || $dateOriginal instanceof DateTimeImmutable) return $dateOriginal;
        self::setupFromSession();

        return !empty($expectedFormat)
            ? DateTime::createFromFormat($expectedFormat, $dateOriginal, $timezone)
            : new DateTime($dateOriginal, $timezone);
    }

    /**
     * Converts a date in the language-specific format to YYYY-MM-DD.
     *
     * @param $dateString
     * @return string
     * @throws \Exception
     */
    public static function dateConvert($dateString)
    {
        if (empty($dateString)) return '';
        $date = static::createDateTime($dateString, static::$settings['dateFormatPHP']);
        return $date ? $date->format('Y-m-d') : $dateString;
    }

    /**
     * Converts a YYYY-MM-DD date to a Unix timestamp.
     *
     * @param $dateString
     * @param null $timezone
     * @return int
     * @throws \Exception
     */
    public static function timestamp($dateString, $timezone = null)
    {
        if (strlen($dateString) == 10) $dateString .= ' 00:00:00';
        $date = static::createDateTime($dateString, 'Y-m-d H:i:s', $timezone);
        return $date ? $date->getTimestamp() : 0;
    }
}
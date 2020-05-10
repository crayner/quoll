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
 * Date: 9/05/2020
 * Time: 12:22
 */
namespace App\Manager;

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;

/**
 * Class PhoneCodes
 * @package App\Manager
 */
class PhoneCodes
{
    /**
     * @var array
     */
    public static $codes;

    /**
     * @var array
     * Codes here are not found in the Symfony Intl lists, but are already in ISO-3166
     * usually with a status not equal to 'officially assigned'.
     * These codes are in the phone codes list.
     */
    public static $missing_codes = [
        'alpha2Name' => [
            'AN' => 'Netherlands Antilles'
        ],
        'alpha3Name' => [
            'ANT' => 'Netherlands Antilles'
        ],
        'alpha3alpha2' => [
            'ANT' => 'AN'
        ],
    ];

    /**
     * getPath
     * @return string
     */
    protected static function readCodes(): array
    {
        if (self::$codes === null) {
            self::$codes = json_decode(file_get_contents(realpath(__DIR__ . '/../../config/iddcodes/codes.json')), true);
            self::$codes = self::$codes['Names'];
        }
        return self::$codes;
    }

    /**
     * getIddCode
     * @param string $alpha2 Alpha 2 Code
     * @return string
     */
    public static function getIddCode(string $alpha2): string
    {
        $alpha2 = strtoupper($alpha2);
        if (key_exists($alpha2, self::readCodes())) {
            return self::$codes[$alpha2];
        }
        throw new MissingResourceException(sprintf('The phone IDD Code is not available for %s.', $alpha2));
    }

    /**
     * getAlpha3IddCode
     * @param string $alpha3 Alpha 3 Code
     * @return string
     */
    public static function getAlpha3IddCode(string $alpha3): string
    {
        $alpha3 = strtoupper($alpha3);
        try {
            $alpha2 = Countries::getAlpha2Code($alpha3);
        } catch (MissingResourceException $e) {
            $alpha2 = self::$missing_codes['alpha3alpha2'][$alpha3];
        }
        if (key_exists($alpha2, self::readCodes())) {
            return self::$codes[$alpha2];
        }
        throw new MissingResourceException(sprintf('The phone IDD Code is not available for %s.', $alpha3));
    }

    /**
     * getIddCodes
     * @return array
     */
    public static function getIddCodes(): array
    {
        return self::readCodes();
    }

    /**
     * getIddCodeChoices
     * @param bool $useAlpha3
     * @return array
     */
    public static function getIddCodeChoices(bool $useAlpha3 = false): array
    {
        $result = [];
        foreach(self::readCodes() as $q=>$w) {
            try {
                $value = $useAlpha3 ? Countries::getAlpha3Code($q) : $q;
                $result[] = new ChoiceView([$q => $w], $value, Countries::getName($q) . ' (+' . $w . ')');
            } catch (MissingResourceException $e) {
                $alpha3 = array_flip(self::$missing_codes['alpha3alpha2']);
                $value = $useAlpha3 ? $alpha3[$q] : $q;
                $name = self::$missing_codes['alpha2Name'][$q];
                $result[] = new ChoiceView([$q => $w], $value, $name . ' (+' . $w . ')');
            }
        }
        uasort($result, function($a,$b) {
            return $a->label > $b->label ? 1 : -1;
        });

        return array_values($result);
    }
}
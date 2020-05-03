<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 24/11/2018
 * Time: 16:38
 */
namespace App\Manager\Traits;

use App\Util\TranslationHelper;

/**
 * Traits BooleanList
 * @package App\Manager\Traits
 */
trait BooleanList
{
    /**
     * @var array
     */
    private static $booleanList = [
        'Y',
        'N',
    ];

    /**
     * getBooleanList
     * @return array
     */
    public static function getBooleanList(): array
    {
        return self::$booleanList;
    }

    /**
     * checkBoolean
     * @param string $value
     * @param string|null $default
     * @return string|null
     */
    private static function checkBoolean(?string $value, ?string $default = 'Y')
    {
        return in_array($value, self::getBooleanList()) ? $value : $default;
    }

    /**
     * isTrueOrFalse
     * @param string $yesOrNo
     * @return bool
     */
    private function isTrueOrFalse(string $yesOrNo): bool
    {
        return $yesOrNo === 'Y';
    }

    /**
     * getYesNo
     * @param bool $w
     * @return string
     */
    private static function getYesNo(bool $w): string
    {
        return $w ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages');
    }
}
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
    protected static function checkBoolean(?string $value, ?string $default = 'Y')
    {
        return in_array($value, self::getBooleanList()) ? $value : $default;
    }

    /**
     * isTrueOrFalse
     * @param string $yesOrNo
     * @return bool
     */
    protected function isTrueOrFalse(string $yesOrNo): bool
    {
        return $yesOrNo === 'Y';
    }

    /**
     * getYesNo
     * @param bool $w
     * @return string
     */
    protected static function getYesNo(bool $w): string
    {
        return TranslationHelper::translate($w ? 'Yes' : 'No', [], 'messages');
    }
}
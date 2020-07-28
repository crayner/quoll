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
 * Date: 1/12/2019
 * Time: 08:40
 */

namespace App\Modules\People\Manager;

use App\Modules\People\Entity\Person;
use App\Modules\System\Manager\SettingFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonNameManager
 * @package App\Modules\People\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonNameManager
{
    /**
     * @var string[]
     */
    private static $styleList = [
        'Standard',
        'Formal',
        'Reversed',
        'Preferred',
        'Short',
    ];
    /**
     * @var string[]
     */
    private static $personTypeList = [
        'General',
        'Student',
        'Staff',
        'CareGiver',
    ];
    /**
     * @var string[]
     */
    private static $nameParts = [
        'initial',
        'firstName',
        'surname',
        'preferredName',
        'officialName',
        'title',
    ];

    /**
     * formatName
     * @param Person $person
     * @param string $personType
     * @param string $style
     * 25/07/2020 11:31
     */
    public static function formatName(Person $person, string $personType = 'General', string $style = 'Standard'): string
    {
        $format = self::getFormat($personType, $style);
        $result = $format;
        foreach (self::getNameParts() as $part) {
            $method = 'get' . ucfirst($part);
            $result = str_replace('['.$part.']', $person->$method(), $result);
        }
        return trim($result);
    }

    /**
     * formatNameQuery
     * @param string $alias
     * @param string $personType
     * @param string $style
     * 25/07/2020 11:31
     */
    public static function formatNameQuery(string $alias, string $personType = 'General', string $style = 'Standard'): string
    {
        $format = self::getFormat($personType, $style);
        $w = explode(']', $format);
        $result = '';
        foreach($w as $item) {
            $x = strpos($item, '[');
            if ($x === false) {
                $result .= ",'" . $item . "'";
            } else if ($x === 0 && in_array(substr($item, 1), self::getNameParts())) {
                $result .= ',' . $alias . '.' . substr($item, 1);
            } else {
                $result .= ",'" . substr($item, 0, $x) . "'";
                $item = substr($item, $x);
                if (in_array(substr($item, 1), self::getNameParts())) {
                    $result .= ',' . $alias . '.' . substr($item, 1);
                }
            }
        }
        return trim($result, ',');
    }

    /**
     * getFormat
     * @param string $personType
     * @param string $style
     * @return array|bool|int|object|string|null
     * @throws \Exception
     * 25/07/2020 11:42
     */
    public static function getFormat(string $personType = 'General', string $style = 'Standard')
    {
        if (!in_array($style, self::getStyleList())) {
            $style = 'Standard';
        }
        if (!in_array($personType, self::getPersonTypeList())) {
            $personType = 'General';
        }
       return SettingFactory::getSettingManager()->get('People', 'formatName'.$personType.$style, '[firstName] [surname]');
    }

    /**
     * getStyleList
     * @return array|string[]
     * 25/07/2020 11:34
     */
    public static function getStyleList(): array
    {
        return self::$styleList;
    }

    /**
     * getPersonTypeList
     * @return array|string[]
     * 25/07/2020 11:39
     */
    public static function getPersonTypeList(): array
    {
        return self::$personTypeList;
    }

    /**
     * @return string[]
     */
    public static function getNameParts(): array
    {
        return self::$nameParts;
    }
}

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
 * Date: 16/08/2020
 * Time: 13:33
 */
namespace App\Modules\Security\Manager;

/**
 * Class RoleCategories
 * @package App\Modules\Security\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RoleCategories
{
    /**
     * @var string[]
     */
    private static array $categoryList = [
        'Staff',
        'Student',
        'Care Giver',
        'Contact',
        'System',
    ];

    /**
     * getCategoryList
     *
     * 16/08/2020 13:34
     * @return string[]
     */
    public static function getCategoryList(): array
    {
        return self::$categoryList;
    }
}

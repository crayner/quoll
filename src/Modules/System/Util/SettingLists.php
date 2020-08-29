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
 * Date: 27/08/2020
 * Time: 09:33
 */
namespace App\Modules\System\Util;

/**
 * Class SettingLists
 * @package App\Modules\System\Util
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingLists
{
    /**
     * getInstallationTypeList
     *
     * 27/08/2020 09:17
     * @return string[]
     */
    public static function getInstallationTypeList(): array
    {
        return [
            'Production',
            'Testing',
            'Development',
        ];
    }

}
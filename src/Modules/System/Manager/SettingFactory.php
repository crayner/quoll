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
 * Date: 5/07/2020
 * Time: 11:41
 */
namespace App\Modules\System\Manager;
/**
 * Class SettingFactory
 * @package App\Modules\System\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingFactory
{
    /**
     * @var SettingManager
     */
    private static $instance;

    /**
     * SettingFactory constructor.
     * @param SettingManager $instance
     */
    public function __construct(SettingManager $instance)
    {
        self::$instance = $instance;
    }

    /**
     * getSettingManager
     * @return SettingManager
     * 5/07/2020 11:44
     */
    public static function getSettingManager(): ?SettingManager
    {
        return self::$instance;
    }
}

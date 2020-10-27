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
 * Date: 24/10/2020
 * Time: 10:28
 */
namespace App\Modules\Attendance\Manager\Hidden;

/**
 * Class AttendanceRecorderLogManager
 *
 * 24/10/2020 10:29
 * @package App\Modules\Attendance\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRecorderLogManager
{
    /**
     * @var array
     */
    private static array $logAttendance = [
        'All',
        'Daily Only',
        'Class Only',
        'None',
    ];

    /**
     * LogAttendance
     *
     * @return array
     */
    public static function getLogAttendance(): array
    {
        return self::$logAttendance;
    }
}
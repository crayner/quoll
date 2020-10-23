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
 * Date: 19/10/2020
 * Time: 15:32
 */
namespace App\Modules\Attendance\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class AttendanceLogTime
 *
 * 19/10/2020 15:33
 * @package App\Modules\Attendance\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation()
 */
class AttendanceLogTime extends Constraint
{
    const ATTENDANCE_TIME_ERROR = '0150ca0d-b556-45f5-9167-67bd64d4bcf9';

    /**
     * @var array|string[]
     */
    protected static $errorNames = [
        self::ATTENDANCE_TIME_ERROR => 'ATTENDANCE_TIME_ERROR',
    ];

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

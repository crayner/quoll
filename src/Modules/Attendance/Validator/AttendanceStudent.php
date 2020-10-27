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
 * Time: 13:01
 */
namespace App\Modules\Attendance\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class AttendanceStudent
 *
 * 19/10/2020 13:02
 * @package App\Modules\Attendance\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation()
 */
class AttendanceStudent extends Constraint
{
    const DUPLICATE_ATTENDANCE_ERROR = 'e055cc14-dd43-4c90-840c-dc51b3ba7496';

    /**
     * @var array|string[]
     */
    protected static $errorNames = [
        self::DUPLICATE_ATTENDANCE_ERROR => 'DUPLICATE_ATTENDANCE_ERROR',
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

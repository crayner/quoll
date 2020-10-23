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

use App\Manager\EntityInterface;
use App\Modules\Attendance\Entity\AttendanceLogClass;
use App\Modules\Attendance\Entity\AttendanceLogRollGroup;
use App\Modules\Attendance\Entity\AttendanceLogStudent;
use App\Modules\Attendance\Manager\AttendanceByRollGroupManager;
use App\Modules\System\Manager\SettingFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AttendanceLogTimeValidator
 *
 * 19/10/2020 15:33
 * @package App\Modules\Attendance\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceLogTimeValidator extends ConstraintValidator
{
    /**
     * validate
     *
     * 23/10/2020 11:31
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->isValid($value)) return;

        $times = SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']);
        $time = null;
        if (count($times) === 1) $time = $times[0];
        if (count($times) > 1 && array_search($value->getDailyTime(), $times) === false) {
            $this->context->buildViolation('The value is not valid. Use one of: "{times}"')
                ->setCode(AttendanceLogTime::ATTENDANCE_TIME_ERROR)
                ->setParameter('{times}', implode('","', $times))
                ->atPath('time')
                ->setTranslationDomain('Attendance')
                ->addViolation();
            return;
        }
    }

    /**
     * isValid
     *
     * 23/10/2020 11:32
     * @param object $value
     * @return bool
     */
    private function isValid(object $value): bool
    {
        if ($value instanceof AttendanceByRollGroupManager) return true;
        if ($value instanceof AttendanceLogStudent) return true;
        if ($value instanceof AttendanceLogRollGroup) return true;
        return false;
    }
}
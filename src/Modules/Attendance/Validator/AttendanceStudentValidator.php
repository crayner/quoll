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
 * Time: 13:03
 */
namespace App\Modules\Attendance\Validator;

use App\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AttendanceStudentValidator
 *
 * 19/10/2020 13:03
 * @package App\Modules\Attendance\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceStudentValidator extends ConstraintValidator
{
    /**
     * validate
     *
     * 19/10/2020 13:03
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Modules\Attendance\Entity\AttendanceStudent) return;

        if ($value->getAttendanceClass() === null && $value->getAttendanceRollGroup() === null) {
            $this->context->buildViolation('The Class or Roll Group must be selected.')
                ->setCode(AttendanceStudent::DUPLICATE_ATTENDANCE_ERROR)
                ->atPath('attendanceClass')
                ->setTranslationDomain('Attendance')
                ->addViolation();
            $this->context->buildViolation('The Class or Roll Group must be selected.')
                ->setCode(AttendanceStudent::DUPLICATE_ATTENDANCE_ERROR)
                ->atPath('attendanceRollGroup')
                ->setTranslationDomain('Attendance')
                ->addViolation();
            return;
        }

        if (ProviderFactory::getRepository(\App\Modules\Attendance\Entity\AttendanceStudent::class)->hasDuplicates($value)) {
            $this->context->buildViolation('The student attendance record is a duplicated')
                ->setCode(AttendanceStudent::DUPLICATE_ATTENDANCE_ERROR)
                ->atPath('date')
                ->setTranslationDomain('Attendance')
                ->addViolation();
            $this->context->buildViolation('The student attendance record is a duplicated')
                ->setCode(AttendanceStudent::DUPLICATE_ATTENDANCE_ERROR)
                ->atPath('time')
                ->setTranslationDomain('Attendance')
                ->addViolation();
            $this->context->buildViolation('The student attendance record is a duplicated')
                ->setCode(AttendanceStudent::DUPLICATE_ATTENDANCE_ERROR)
                ->atPath('student')
                ->setTranslationDomain('Attendance')
                ->addViolation();
            return;
        }
    }
}

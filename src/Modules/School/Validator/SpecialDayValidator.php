<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/10/2019
 * Time: 14:55
 */

namespace App\Modules\School\Validator;

use App\Modules\School\Entity\AcademicYearSpecialDay;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SpecialDayValidator
 * @package App\Modules\School\Validator
 */
class SpecialDayValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof AcademicYearSpecialDay)
            return;

        if ($value->getDate() < $value->getAcademicYear()->getFirstDay() || $value->getDate() > $value->getAcademicYear()->getLastDay())
            $this->context->buildViolation('The date must be in the specified academic year.')
                ->atPath('date')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();

        if ($value->getType() === 'School Closure')
            return;

        if (null === $value->getSchoolOpen()){
            $this->context->buildViolation('A timing change requires all times to be entered.')
                ->atPath('schoolOpen')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();
        }

        if (null === $value->getSchoolStart()){
            $this->context->buildViolation('A timing change requires all times to be entered.')
                ->atPath('schoolStart')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();
        }

        if (null === $value->getSchoolEnd()){
            $this->context->buildViolation('A timing change requires all times to be entered.')
                ->atPath('schoolEnd')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();
        }

        if (null === $value->getSchoolClose()){
            $this->context->buildViolation('A timing change requires all times to be entered.')
                ->atPath('schoolClose')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();
        }

        if (null === $value->getSchoolOpen() || null === $value->getSchoolStart() || null === $value->getSchoolEnd() || null === $value->getSchoolClose()){
            return ;
        }

        if ($value->getSchoolOpen() > $value->getSchoolStart() || $value->getSchoolOpen() > $value->getSchoolEnd() || $value->getSchoolOpen() > $value->getSchoolClose())
            $this->context->buildViolation('The time is not valid for this day.')
                ->atPath('schoolOpen')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();

        if ($value->getSchoolEnd() < $value->getSchoolOpen() || $value->getSchoolEnd() < $value->getSchoolStart() || $value->getSchoolEnd() > $value->getSchoolClose())
            $this->context->buildViolation('The time is not valid for this day.')
                ->atPath('schoolEnd')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();

        if ($value->getSchoolClose() < $value->getSchoolOpen() || $value->getSchoolClose() < $value->getSchoolStart() || $value->getSchoolClose() < $value->getSchoolEnd())
            $this->context->buildViolation('The time is not valid for this day.')
                ->atPath('schoolClose')
                ->setTranslationDomain('SchoolAdmin')
                ->addViolation();

    }

}
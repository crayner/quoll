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
 * Date: 3/10/2019
 * Time: 13:45
 */

namespace App\Modules\School\Validator;

use App\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AcademicYearValidator
 * @package App\Modules\School\Validator
 */
class AcademicYearValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Modules\School\Entity\AcademicYear)
            return ;

        if ($value->getFirstDay() >= $value->getLastDay())
            $this->context->buildViolation('The first day must be before the last day.')
                ->atPath('firstDay')
                ->setCode(AcademicYear::INVALID_ACADEMIC_YEAR_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();

        if (null === $value->getFirstDay())
            return;

        $last = new \DateTimeImmutable($value->getFirstDay()->format('Y-m-d') . ' 00:00:00 +1 Year -1 Day');
        
        if ($value->getLastDay() === null) {
            $this->context->buildViolation('The last day value is not valid.')
                ->atPath('lastDay')
                ->setCode(AcademicYear::INVALID_ACADEMIC_YEAR_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return;
        }
        
        if ($value->getLastDay()->format('Y-m-d') !== $last->format('Y-m-d')) {
            $this->context->buildViolation('The school academic year should cover a whole year.')
                ->atPath('lastDay')
                ->setCode(AcademicYear::INVALID_ACADEMIC_YEAR_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
        }
        if (ProviderFactory::create(\App\Modules\School\Entity\AcademicYear::class)->isAcademicYearOverlap($value)) {
            $this->context->buildViolation('The school academic year should not overlap another academic year.')
                ->atPath('name')
                ->setCode(AcademicYear::INVALID_ACADEMIC_YEAR_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
        }
    }
}
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
 * Date: 21/12/2019
 * Time: 20:44
 */

namespace App\Modules\School\Validator;

use App\Modules\School\Entity\AcademicYearTerm;
use App\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class TermValidator
 * @package App\Modules\SchoolAdmin\Validator
 */
class TermValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        if ($value->getLastDay() <= $value->getFirstDay()){
            $this->context->buildViolation('The last day of the term is before the first day of the term.')
                ->setTranslationDomain($constraint->transDomain)
                ->atPath('lastDay')
                ->addViolation();
        }
        // Check Term against the Year.
        if (empty($value->getAcademicYear()))
            return;

        if ($value->getAcademicYear()->getFirstDay() > $value->getFirstDay()) {
            $this->context->buildViolation('The first day of the term is before the first day of the academic year ({first-day}).)')
                ->setParameter('{first-day}', $value->getAcademicYear()->getFirstDay()->format('d M Y'))
                ->setTranslationDomain($constraint->transDomain)
                ->atPath('firstDay')
                ->addViolation();
        }

        if ($value->getAcademicYear()->getLastDay() < $value->getLastDay()) {
            $this->context->buildViolation('The last day of the term is after the last day of the academic year ({last-day}).)')
                ->setParameter('{last-day}', $value->getAcademicYear()->getLastDay()->format('d M Y'))
                ->setTranslationDomain($constraint->transDomain)
                ->atPath('lastDay')
                ->addViolation();
        }

        //  compare to existing terms in the year
        $terms = ProviderFactory::getRepository(AcademicYearTerm::class)->findOtherTerms($value);

        foreach($terms as $term)
        {
            if ($value->getFirstDay() >= $term->getFirstDay() && $value->getFirstDay() <= $term->getLastDay()) {
                $this->context->buildViolation('The dates overlap {name}: {first} - {last}')
                    ->setTranslationDomain($constraint->transDomain)
                    ->setParameters(['{name}' => $term->getName(), '{first}' => $term->getFirstDay()->format('d M Y'), '{last}' => $term->getLastDay()->format('d M Y')])
                    ->atPath('firstDay')
                    ->addViolation();
            }

            if ($value->getLastDay() >= $term->getFirstDay() && $value->getLastDay() <= $term->getLastDay()) {
                $this->context->buildViolation('The dates overlap {name}: {first} - {last}')
                    ->setTranslationDomain($constraint->transDomain)
                    ->setParameters(['{name}' => $term->getName(), '{first}' => $term->getFirstDay()->format('d M Y'), '{last}' => $term->getLastDay()->format('d M Y')])
                    ->atPath('lastDay')
                    ->addViolation();
            }

            if ($value->getFirstDay() <= $term->getFirstDay() && $value->getLastDay() >= $term->getLastDay()) {
                $this->context->buildViolation('The dates overlap {name}: {first} - {last}')
                    ->setTranslationDomain($constraint->transDomain)
                    ->setParameters(['{name}' => $term->getName(), '{first}' => $term->getFirstDay()->format('d M Y'), '{last}' => $term->getLastDay()->format('d M Y')])
                    ->atPath('firstDay')
                    ->addViolation();
            }
        }
    }

}
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
 * Date: 4/09/2020
 * Time: 12:30
 */
namespace App\Modules\Enrolment\Validator;

use App\Modules\Enrolment\Entity\CourseClassPerson;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ClassPersonValidator
 * @package App\Modules\Enrolment\Validation
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ClassPersonValidator extends ConstraintValidator
{
    /**
     * validate
     *
     * 4/09/2020 12:41
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof CourseClassPerson || $value->getPerson() === null || $value->getRole() === null) return;

        if ($value->getPerson()->isStudent() && strpos($value->getRole(), 'Student') === 0) return;

        if (!$value->getPerson()->isStudent() && strpos($value->getRole(), 'Student') === false) return;

        $this->context->buildViolation('This person must not be assigned as a "{role}".')
            ->setTranslationDomain('Enrolment')
            ->atPath('person')
            ->setParameter('{role}',$value->getRole())
            ->setCode(ClassPerson::INVALID_ROLE_ERROR)
            ->addViolation();
    }
}

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
 * Date: 11/07/2020
 * Time: 12:56
 */
namespace App\Modules\People\Validator;

use App\Modules\People\Entity\FamilyMember;
use App\Modules\People\Entity\ParentContact;
use App\Modules\Student\Entity\Student;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class FamilyMemberNotBlankValidator
 * @package App\Modules\People\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FamilyMemberNotBlankValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     * 11/07/2020 13:08
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof FamilyMember) return;

        if (!$value->getStudent() instanceof Student && !$value->getParent() instanceof ParentContact) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(FamilyMemberNotBlank::PARENT_AND_STUDENT_ERROR)
                ->atPath($constraint->atPath)
                ->addViolation();
        }
    }
}

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
 * Date: 28/07/2020
 * Time: 14:14
 */
namespace App\Modules\People\Validator;

use App\Modules\People\Entity\Person;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class StaffStudentValidator
 * @package App\Modules\People\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffStudentValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     * 28/07/2020 14:17
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Person || $value->getStaff() === null || $value->getStudent() === null) return;
        dump($value);

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain($constraint->transDomain)
            ->setCode(StaffStudent::STAFF_STUDENT_ERROR)
            ->addViolation();
    }
}

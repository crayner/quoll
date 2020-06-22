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
 * Date: 5/04/2020
 * Time: 07:21
 */

namespace App\Modules\People\Validator;

use App\Modules\People\Entity\Person;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UsernameValidator
 * @package App\Modules\People\Validator
 */
class UsernameValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Person || $value->isCanLogin() === false)
            return;

        if ($value->getUsername() === null || $value->getUsername() === '')
            $value->setUsername($value->getEmail());

        if ($value->getUsername() === null || $value->getUsername() === '')
            $this->context->buildViolation('This value should not be blank.')
                ->setTranslationDomain('validators')
                ->atPath('username')
                ->setCode(Username::USERNAME_UNIQUE_ERROR)
                ->addViolation();

        if (in_array($value->getSecurityRoles(), [null, '']))
            $this->context->buildViolation('This value should not be blank.')
                ->setTranslationDomain('validators')
                ->setCode(Username::PRIMARY_ROLE_NOT_SET_ERROR)
                ->atPath('securityRoles')
                ->addViolation();
    }
}
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
 * Time: 09:35
 */
namespace App\Modules\Security\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SecurityUserValidator
 * @package App\Modules\Security\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityUserValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     * 28/07/2020 09:36
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Modules\Security\Entity\SecurityUser && !$value->isCanLogin()) {
            $value->setUsername(null)
                ->setSecurityRoles([]);
        };

        if (empty($value->getUsername())) {
            $this->context->buildViolation('This value must bot be blank')
                ->atPath('username')
                ->setCode(SecurityUser::USERNAME_ERROR)
                ->addViolation();
        }

        if (empty($value->getSecurityRoles())) {
            $this->context->buildViolation('This value must bot be blank')
                ->atPath('securityRoles')
                ->setCode(SecurityUser::SECURITY_ROLES_ERROR)
                ->addViolation();
        }
    }
}

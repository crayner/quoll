<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 19/08/2019
 * Time: 18:11
 */

namespace App\Modules\Security\Validator;

use App\Modules\People\Util\UserHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CurrentPasswordValidator
 * @package App\Modules\Security\Validator
 */
class CurrentPasswordValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $user = UserHelper::getCurrentSecurityUser();
        if (!$user->isPasswordValid($value)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->translationDomain)
                ->addViolation();
        }
    }

}
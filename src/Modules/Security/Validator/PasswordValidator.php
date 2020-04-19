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
 * Date: 25/07/2019
 * Time: 14:00
 */

namespace App\Modules\Security\Validator;

use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PasswordValidator
 * @package App\Modules\Security\Validator
 */
class PasswordValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $settingProvider = ProviderFactory::create(Setting::class);

        $alpha = $settingProvider->getSettingByScopeAsboolean('System', 'passwordPolicyAlpha');
        $numeric = $settingProvider->getSettingByScopeAsBoolean('System', 'passwordPolicyNumeric');
        $punctuation = $settingProvider->getSettingByScopeAsBoolean('System', 'passwordPolicyNonAlphaNumeric');
        $minLength = $settingProvider->getSettingByScopeAsInteger('System', 'passwordPolicyMinLength');

        if ($alpha && ! preg_match('/.*(?=.*[a-z])(?=.*[A-Z]).*/', $value))
            $this->context->buildViolation('The password must contain both lower and uppercase characters.')
                ->setTranslationDomain('Security')
                ->addViolation();

        if ($numeric && ! preg_match('/.*[0-9]/', $value))
            $this->context->buildViolation('The password must contain as least one number.')
                ->setTranslationDomain('Security')
                ->addViolation();

        if ($punctuation && ! preg_match('/[^a-zA-Z0-9]/', $value))
            $this->context->buildViolation('The password must contain as least one non alpha-numeric character.')
                ->setTranslationDomain('Security')
                ->addViolation();

        if ($minLength > 0 && mb_strlen($value) < $minLength)
            $this->context->buildViolation('The password must be a minimum of {minLength} characters long.')
                ->setParameter('{minLength}', $minLength)
                ->setTranslationDomain('Security')
                ->addViolation();

        if ($constraint->assumeCurrentUser) {
            $user = UserHelper::getCurrentSecurityUser();
            if ($user instanceof SecurityUser) {
                if ($user->isPasswordValid($value)) {
                    $this->context->buildViolation('Your request failed because your new password is the same as your current password.')
                        ->setTranslationDomain('Security')
                        ->addViolation();
                }
            }
        }
    }
}
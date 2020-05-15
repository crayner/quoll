<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 15/05/2020
 * Time: 11:52
 */
namespace App\Modules\People\Validator;

use App\Modules\People\Manager\PhoneCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PhoneValidator
 * @package App\Modules\People\Validator
 */
class PhoneValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Modules\People\Entity\Phone) {
            return;
        }

        if ($value->getCountry() === null || $value->getPhoneNumber() === null || ($regex = PhoneCodes::getValidationRegex($value->getCountry())) === null) {
            return;
        }

        $phoneNumber = preg_replace('/[^0-9]/', '', $value->getPhoneNumber());
        $matches = [];
        if (preg_match($regex, $phoneNumber, $matches) < 1 || $matches[0] !== $phoneNumber) {
            $this->context->buildViolation($constraint->message)
                ->atPath('phoneNumber')
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(Phone::INVALID_PHONE_ERROR)
                ->setParameter('{value}', $value->getPhoneNumber())
                ->setParameter('{country}', PhoneCodes::getAlpha3Name($value->getCountry()))
                ->addViolation();

        }
    }

}
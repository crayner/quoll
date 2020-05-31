<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 7/05/2020
 * Time: 10:11
 */
namespace App\Modules\People\Validator;

use App\Modules\People\Entity\Locality;
use App\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AddressValidator
 * @package App\Modules\People\Validator
 */
class AddressValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Modules\People\Entity\Address)
            return;

        if (!$value->getLocality() instanceof Locality)
            return;

        $addresses = ProviderFactory::getRepository(\App\Modules\People\Entity\Address::class)->getSingleStringAddress($value);
        foreach($addresses as $q=>$w) {
            $addresses[$q] = $w['name'];
        }
        $testValue = $value->getFlatUnitDetails().$value->getPropertyName().$value->getStreetNumber().$value->getStreetName().$value->getPostCode().$value->getLocality()->getId();

        if (in_array($testValue,$addresses)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(Address::DUPLICATE_ADDRESS_ERROR)
                ->atPath('streetName')
                ->addViolation();
            return;
        }

        $testValue = preg_replace('/[^a-zA-Z0-9]/', '', $testValue);
        foreach($addresses as $q=>$w) {
            $addresses[$q] = preg_replace('/[^a-zA-Z0-9]/', '', $w);
        }

        if (in_array($testValue,$addresses)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(Address::DUPLICATE_ADDRESS_ERROR)
                ->atPath('streetName')
                ->addViolation();
        }
    }
}
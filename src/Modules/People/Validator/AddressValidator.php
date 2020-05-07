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
            if (!$value->isEqualTo($w)) {
                $addresses[$q] = $w->getFlatUnitDetails() . $w->getStreetNumber() . $w->getStreetName() . $w->getPropertyName() . $w->getLocality()->getName() . $w->getLocality()->getTerritory() . $w->getLocality()->getPostCode() . $w->getLocality()->getCountry();
            }
        }
        $address = $value->getFlatUnitDetails() . $value->getStreetNumber() . $value->getStreetName() . $value->getPropertyName() . $value->getLocality()->getName() . $value->getLocality()->getTerritory() . $value->getLocality()->getPostCode() . $value->getLocality()->getCountry();

        if (in_array($address,$addresses)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(Address::DUPLICATE_ADDRESS_ERROR)
                ->atPath('streetName')
                ->addViolation();
            return;
        }

        foreach($addresses as $q=>$w) {
            $addresses[$q] = str_replace([' ', "\n", "\r", "\t", ',', '.', "'", '`'], '', $w);
        }
        $address = str_replace([' ', "\n", "\r", "\t", ',', '.', "'", '`'], '', $address);

        if (in_array($address,$addresses)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(Address::DUPLICATE_ADDRESS_ERROR)
                ->atPath('streetName')
                ->addViolation();
            return;
        }
    }
}
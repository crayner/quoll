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
 * Date: 13/05/2020
 * Time: 14:04
 */
namespace App\Modules\People\Validator;

use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Modules\People\Manager\AddressManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PostCodeValidator
 * @package App\Modules\People\Validator
 */
class PostCodeValidator extends ConstraintValidator
{
    /**
     * @var AddressManager
     */
    private $manager;

    /**
     * PostCodeValidator constructor.
     * @param AddressManager $manager
     */
    public function __construct(AddressManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof Address || $value instanceof Locality)) {
            return;
        }

        if (!$this->manager->isValidPostCode($value)) {
            $this->context->buildViolation($constraint->message)
                ->setCode(PostCode::INVALID_POSTCODE_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->atPath('postCode')
                ->setParameter('{value}', $value instanceof Address ? $value->getPostCode().$value->getLocality()->getPostCode() : $value->getPostCode())
                ->addViolation();
        }
    }

}
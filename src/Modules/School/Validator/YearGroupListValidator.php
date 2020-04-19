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
 * Date: 27/09/2019
 * Time: 11:45
 */

namespace App\Modules\School\Validator;

use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class YearGroupListValidator
 * @package App\Modules\School\Validator
 */
class YearGroupListValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ('' === $value || null === $value || [] === $value)
            return;

        if (!is_array($value))
            $value = explode(',', $value);
        while (isset($value[0]) && $value[0] === '')
            unset($value[0]);

        foreach($value as $id)
        {
            if (intval(trim($id)) > 0)
                $id = intval(trim($id));
            $yearGroup = ProviderFactory::getRepository(YearGroup::class)->findOneBy([$constraint->fieldName => $id]);
            if (!$yearGroup instanceof YearGroup)
            {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{value}', $id)
                    ->atPath($constraint->propertyPath)
                    ->setTranslationDomain('messages')
                    ->addViolation();
            }
        }
    }

}
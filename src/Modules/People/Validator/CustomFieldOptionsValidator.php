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
 * Date: 18/05/2020
 * Time: 15:37
 */
namespace App\Modules\People\Validator;

use App\Modules\People\Entity\CustomField;
use App\Util\TranslationHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CustomFieldOptionsValidator
 * @package App\Modules\People\Validator
 */
class CustomFieldOptionsValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof CustomField) {
            return;
        }

        switch ($value->getFieldType()) {
            case 'text':
                if (null === $value->getOptions() || !key_exists('rows', $value->getOptions()))
                {
                    $value->setOptions(['rows' => 20]);
                }
                $rows = intval($value->getOptions()['rows']);
                if ($rows < 1 || $rows > 20) {
                    $this->context->buildViolation($constraint->message)
                        ->setTranslationDomain($constraint->transDomain)
                        ->setCode(CustomFieldOptions::INVALID_OPTIONS_ERROR)
                        ->setParameter('{value}', $rows)
                        ->setParameter('{type}', TranslationHelper::translate('customfield.fieldtype.' . $value->getFieldType(), [], 'People'))
                        ->atPath('options')
                        ->addViolation();
                }
                break;
            case 'short_string':
                if (null === $value->getOptions() || !key_exists('length', $value->getOptions()))
                {
                    $value->setOptions(['length' => 191]);
                }
                $length = intval($value->getOptions()['length']);
                if ($length < 1 || $length > 191) {
                    $this->context->buildViolation($constraint->message)
                        ->setTranslationDomain($constraint->transDomain)
                        ->setCode(CustomFieldOptions::INVALID_OPTIONS_ERROR)
                        ->setParameter('{value}', $length)
                        ->setParameter('{type}', TranslationHelper::translate('customfield.fieldtype.' . $value->getFieldType(), [], 'People'))
                        ->atPath('options')
                        ->addViolation();
                }
                break;
            case 'choice':
                if (null === $value->getOptions())
                {
                    $value->setOptions([]);
                }
                if (count($value->getOptions()) < 1) {
                    $this->context->buildViolation($constraint->message)
                        ->setTranslationDomain($constraint->transDomain)
                        ->setCode(CustomFieldOptions::INVALID_OPTIONS_ERROR)
                        ->setParameter('{value}', implode(',',$value->getOptions()))
                        ->setParameter('{type}', TranslationHelper::translate('customfield.fieldtype.' . $value->getFieldType(), [], 'People'))
                        ->atPath('options')
                        ->addViolation();
                }
                break;
            default:
                $value->setOptions(null);
                return;
        }



/*
        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain($constraint->transDomain)
            ->setCode(CustomFieldOptions::INVALID_OPTIONS_ERROR)
            ->setParameter('{value}', $value->getOptions())
            ->setParameter('{type}', TranslationHelper::translate($value->getFieldType(), [], 'People'))
            ->atPath('options')
            ->addViolation();

*/

    }

}
<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 9/09/2019
 * Time: 10:00
 */

namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ImageValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReactImageValidator extends ImageValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ReactImage)
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\ReactImage');

        if (null === $value || '' === $value)
            return;

        $value = realpath($value) ?: realpath(__DIR__ . '/../../public' . $value) ?: '';
        parent::validate($value, $constraint);
    }
}
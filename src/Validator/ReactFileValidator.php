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
 * Date: 9/09/2019
 * Time: 08:52
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ReactFileValidator
 * @package App\Validator
 */
class ReactFileValidator extends FileValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$constraint instanceof ReactFile)
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ReactFile');

        $value = realpath($value) ?: realpath(__DIR__ . '/../../public' . $value) ?: '';
        parent::validate($value, $constraint);
    }
}
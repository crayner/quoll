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
 * Date: 22/04/2020
 * Time: 08:58
 */

namespace App\Form\Transform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class YesNoTransformer
 * @package App\Form\Transform
 */
class NoOnEmptyTransformer implements DataTransformerInterface
{
    /**
     * transform
     * @param mixed $value
     * @return mixed
     */
    public function transform($value)
    {
        return $value === 'No' ? null : $value;
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return mixed|string
     */
    public function reverseTransform($value)
    {
        return $value === null || $value === '' ? 'No' : $value;
    }
}

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
 * Date: 12/12/2019
 * Time: 11:51
 */

namespace App\Form\Transform;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ToggleToBooleanTransformer
 * @package App\Form\Transform
 */
class ToggleToBooleanTransformer implements DataTransformerInterface
{
    /**
     * transform
     * @param mixed $value
     * @return string
     */
    public function transform($value): string
    {
        return $value ? 'Y' : 'N';
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return bool
     */
    public function reverseTransform($value): string
    {
        return $value === 'Y';
    }

}
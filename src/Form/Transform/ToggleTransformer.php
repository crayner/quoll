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
 * Class ToggleTransformer
 * @package App\Form\Transform
 */
class ToggleTransformer implements DataTransformerInterface
{
    /**
     * @var boolean
     */
    private $useBoolean;

    /**
     * ToggleToBooleanTransformer constructor.
     * @param bool $useBoolean
     */
    public function __construct(bool $useBoolean)
    {
        $this->useBoolean = $useBoolean;
    }

    /**
     * transform
     * @param mixed $value
     * @return string
     */
    public function transform($value): string
    {
        if ($this->useBoolean && is_bool($value))
            return $value ? 'Y' : 'N';
        return $value !=='N' ? 'Y' : 'N';
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return bool
     */
    public function reverseTransform($value): string
    {
        if ($this->useBoolean)
            return $value === 'N' ? false : true;

        return $value === 'N' ? 'N' : 'Y';
    }

}
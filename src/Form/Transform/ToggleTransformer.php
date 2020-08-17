<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
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
    private bool $useBoolean = true;

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
     * @return mixed
     */
    public function transform($value)
    {
        if ($this->useBoolean && is_bool($value)) {
            return $value;
        }

        if (is_bool($value))
            return $value ? 'Y' : 'N';
        return $value === 'Y' ? 'Y' : 'N';
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return string
     */
    public function reverseTransform($value): string
    {
        if ($this->useBoolean)
            return $value === 'Y';

        return $value === 'N' ? 'N' : 'Y';
    }

}
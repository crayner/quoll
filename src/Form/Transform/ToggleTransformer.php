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
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ToggleTransformer implements DataTransformerInterface
{
    /**
     * @var bool
     */
    private bool $useBoolean = true;

    /**
     * ToggleTransformer constructor.
     * @param bool $useBoolean
     */
    public function __construct(bool $useBoolean)
    {
        $this->useBoolean = $useBoolean;
    }

    /**
     * transform
     *
     * 1/09/2020 11:11
     * @param mixed $value
     * @return mixed|string
     */
    public function transform($value)
    {
        if (is_bool($value))
            return $value ? 'Y' : 'N';
        return $value === 'Y' || $value === '1' ? 'Y' : 'N';
    }

    /**
     * reverseTransform
     *
     * 1/09/2020 11:11
     * @param mixed $value
     * @return string
     */
    public function reverseTransform($value): string
    {
        if ($this->useBoolean)
            return $value === 'Y' || $value === '1';

        return $value === 'Y' || $value === '1' ? 'Y' : 'N';
    }

}
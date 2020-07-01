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
 * Date: 30/06/2020
 * Time: 16:15
 */

namespace App\Form\Transform;


use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SimpleArrayTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $separator = ',';

    /**
     * SimpleArrayTransformer constructor.
     * @param string $separator
     */
    public function __construct(string $separator = ',')
    {
        $this->separator = $separator;
    }

    /**
     * transform
     * @param mixed $value
     * @return mixed|void
     * 30/06/2020 16:16
     */
    public function transform($value)
    {
        if (is_array($value)) {
            $value = implode($this->separator, $value);
        }

        return $value;
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return mixed|void
     * 30/06/2020 16:16
     */
    public function reverseTransform($value)
    {
        if (empty($value)) return [];

        if (!is_array($value)) {
            $value = explode($this->separator, $value);
        }

        return $value;
    }
}
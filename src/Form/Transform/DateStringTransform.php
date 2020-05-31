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
 * Date: 29/12/2019
 * Time: 13:05
 */
namespace App\Form\Transform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class DateStringTransform
 * @package App\Form\Transform
 */
class DateStringTransform implements DataTransformerInterface
{
    /**
     * @var bool
     */
    private $immutable;

    /**
     * DateStringTransform constructor.
     * @param bool $immutable
     */
    public function __construct(bool $immutable)
    {
        $this->immutable = $immutable;
    }

    /**
     * transform
     * @param mixed $value
     * @return mixed|string
     */
    public function transform($value)
    {
        if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable)
            return $value->format('Y-m-d');
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return \DateTime|\DateTimeImmutable|mixed
     * @throws \Exception
     */
    public function reverseTransform($value)
    {
        if (is_string($value)) {
            try {
                $date = $this->immutable ? new \DateTimeImmutable($value) : new \DateTime($value);
            } catch(\Exception $e) {
                $date = null;
            }
        }
        if (!($date instanceof \DateTime || $date instanceof \DateTimeImmutable))
            throw new TransformationFailedException(sprintf('The date value %s id not valid.', $value));
        return $date;
    }
}
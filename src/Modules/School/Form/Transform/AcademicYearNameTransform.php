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
 * Time: 12:23
 */
namespace App\Modules\School\Form\Transform;

use App\Modules\School\Entity\AcademicYear;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class AcademicYearNameTransform
 * @package App\Modules\School\Form\Transform
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearNameTransform implements DataTransformerInterface
{
    /**
     * transform
     * @param mixed $value
     * @return mixed|string|null
     */
    public function transform($value)
    {
        if ($value instanceof AcademicYear)
            return $value->getName();
        throw new TransformationFailedException(sprintf('The value %s is not an Academic Year.', $value));
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform($value)
    {
        if (is_string($value))
            $year = ProviderFactory::getRepository(AcademicYear::class)->findOneByName($value);
        if (!$year instanceof AcademicYear)
            throw new TransformationFailedException(sprintf('The value %s could not be transformed into an Academic Year.', $value));
        return $year;
    }

}
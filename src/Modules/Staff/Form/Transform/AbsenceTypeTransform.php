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
 * Date: 29/05/2020
 * Time: 07:57
 */
namespace App\Modules\Staff\Form\Transform;

use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class AbsenceTypeTransform
 * @package App\Modules\Staff\Form\Transform
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AbsenceTypeTransform implements DataTransformerInterface
{
    /**
     * transform
     * @param mixed $value
     * @return mixed
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return StaffAbsenceType|mixed
     */
    public function reverseTransform($value)
    {
        if ($value instanceof StaffAbsenceType && $value->getSequenceNumber() === 0) {
            if ($value->getId() === null) {
                $last = ProviderFactory::getRepository(StaffAbsenceType::class)->findHighestSequence() + 1;
            } else {
                $last = ProviderFactory::getRepository(StaffAbsenceType::class)->find($value->getId())->getSequenceNumber();
            }
            $value->setSequenceNumber($last);
        }
        return $value;
    }
}
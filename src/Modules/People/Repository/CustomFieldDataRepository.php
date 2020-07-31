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
 * Date: 17/05/2020
 * Time: 15:03
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\CustomFieldData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class CustomFieldDataRepository
 * @package App\Modules\People\Repository
 */
class CustomFieldDataRepository extends ServiceEntityRepository
{
    /**
     * CustomFieldDataRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomFieldData::class);
    }

    /**
     * countCustomField
     * @param CustomField $field
     * @return int
     * 1/08/2020 08:39
     */
    public function countCustomField(CustomField $field): int
    {
        try {
            return intval($this->createQueryBuilder('d')
                ->select('COUNT(d.id)')
                ->where('d.customField = :field')
                ->setParameter('field', $field)
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}

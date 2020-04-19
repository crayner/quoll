<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\School\Repository;

use App\Modules\School\Entity\Facility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FacilityRepository
 * @package App\Modules\School\Repository
 */
class FacilityRepository extends ServiceEntityRepository
{
    /**
     * FacilityRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facility::class);
    }

    /**
     * findAllIn
     * @param array $spaces
     * @return array
     */
    public function findAllIn(array $spaces): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.id IN (:spaces)')
            ->setParameter('spaces', $spaces, Connection::PARAM_INT_ARRAY)
            ->orderBy('f.name')
            ->getQuery()
            ->getResult();
    }
}

<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Staff\Repository;

use App\Modules\Staff\Entity\StaffAbsenceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StaffAbsenceTypeRepository
 * @package App\Modules\Staff\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffAbsenceTypeRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffAbsenceType::class);
    }

    /**
     * findHighestSequence
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findHighestSequence(): int
    {
        return intval($this->createQueryBuilder('a')
            ->select('a.sequenceNumber')
            ->orderBy('a.sequenceNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult()
        );
    }
}

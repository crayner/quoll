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
namespace App\Modules\Attendance\Repository;

use App\Modules\Attendance\Entity\AttendanceCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AttendanceCodeRepository
 * @package App\Modules\Attendance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceCodeRepository extends ServiceEntityRepository
{
    /**
     * AttendanceCodeRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceCode::class);
    }

    /**
     * findActive
     * @param bool $asArray
     * @return array
     */
    public function findActive(bool $asArray = false): array
    {
        $query = $this->createQueryBuilder('a', 'a.id')
            ->where('a.active = :yes')
            ->setParameter('yes', 'Y')
            ->orderBy('a.sortOrder', 'ASC')
            ->getQuery();
        if ($asArray)
            return $query->getArrayResult();
        return $query->getResult();
    }

    /**
     * findDefaultAttendanceCode
     * @return AttendanceCode|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDefaultAttendanceCode(): ?AttendanceCode
    {
        return $this->createQueryBuilder('ac')
            ->orderBy('ac.sortOrder', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * findAttendanceTypeList
     * @return array
     * 12/06/2020 13:55\
     */
    public function findAttendanceTypeList():array
    {
        return $this->createQueryBuilder('c')
            ->select(['c.name'])
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * nextSortOrder
     * @return int
     * 12/06/2020 13:55
     */
    public function nextSortOrder(): int
    {
        try {
            return intval($this->createQueryBuilder('c')
                ->select(['c.sortOrder'])
                ->orderBy('c.sortOrder', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult()) + 1;
        } catch (NoResultException | NonUniqueResultException | TableNotFoundException $e) {
            return 1;
        }
    }
}

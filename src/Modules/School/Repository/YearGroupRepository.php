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
 * Date: 5/12/2018
 * Time: 22:29
 */
namespace App\Modules\School\Repository;

use App\Modules\School\Entity\YearGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class YearGroupRepository
 * @package App\Modules\School\Repository
 */
class YearGroupRepository extends ServiceEntityRepository
{
    /**
     * YearGroupRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YearGroup::class);
    }

    /**
     * findCurrentYearGroups
     * @return array
     * 24/06/2020 14:57
     */
    public function findCurrentYearGroups(): array
    {
        return $this->createQueryBuilder('yg')
            ->orderBy('yg.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
        ;
    }

    /**
     * findByYearGroupIDList
     * @param array $list
     * @param string $key
     * @return array
     */
    public function findByYearGroupIDList(array $list, string $key): array
    {
        return $this->createQueryBuilder('yg', 'yg.'.$key)
            ->where('yg.id in (:list)')
            ->select(['yg.id','yg.'.$key])
            ->setParameter('list', $list, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findByYearGroupList
     * @param $list
     * @param $key
     * @return array
     */
    public function findByYearGroupList($list, $key): array
    {
        return $this->createQueryBuilder('yg', 'yg.id')
            ->where('yg.' . $key . ' in (:list)')
            ->select(['yg.id','yg.' . $key])
            ->setParameter('list', $list, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findAllInArray
     * @param array $data
     * @return array
     */
    public function findAllInArray(array $data): array
    {
        foreach($data as $q=>$w)
            $data[$q] = intval($w);
        return $this->createQueryBuilder('y')
            ->where('y.id in (:years)')
            ->setParameter('years', $data, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * findNextSequence
     * @return int
     * 2/06/2020 16:53
     */
    public function findNextSortOrder(): int
    {
        try {
            return intval($this->createQueryBuilder('y')
                ->select('y.sortOrder')
                ->orderBy('y.sortOrder', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult()) + 1;
        } catch (NoResultException | NonUniqueResultException $e) {
            return 1;
        }
    }

    /**
     * findNextYearGroup
     *
     * 10/10/2020 09:39
     * @param YearGroup $yearGroup
     * @return YearGroup|null
     */
    public function findNextYearGroup(YearGroup $yearGroup): ?YearGroup
    {
        try {
            return $this->createQueryBuilder('yg')
                ->setMaxResults(1)
                ->where('yg.sortOrder > :currentOrder')
                ->orderBy('yg.sortOrder', 'ASC')
                ->setParameter('currentOrder', $yearGroup->getSortOrder())
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}

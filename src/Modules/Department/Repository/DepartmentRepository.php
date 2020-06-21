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
namespace App\Modules\Department\Repository;

use App\Modules\Department\Entity\Department;
use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class DepartmentRepository
 * @package App\Modules\School\Repository
 */
class DepartmentRepository extends ServiceEntityRepository
{
    /**
     * DepartmentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    /**
     * findPagination
     * @return mixed
     */
    public function findPagination()
    {
        return $this->createQueryBuilder('d')
            ->select(['d','s','p'])
            ->leftJoin('d.staff', 's')
            ->leftJoin('s.person', 'p')
            ->orderBy('d.name', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->groupBy('d.id')
            ->getQuery()
            ->getResult();
    }
}

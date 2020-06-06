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
use App\Modules\Department\Entity\DepartmentStaff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class DepartmentStaffRepository
 * @package App\Modules\Department\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DepartmentStaffRepository extends ServiceEntityRepository
{
    /**
     * DepartmentStaffRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepartmentStaff::class);
    }

    /**
     * findStaffByDepartment
     * @param Department $department
     * @return array
     */
    public function findStaffByDepartment(Department $department): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.department = :department')
            ->setParameter('department', $department)
            ->leftJoin('s.person', 'p')
            ->select(['s','p'])
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

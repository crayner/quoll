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
namespace App\Modules\Department\Repository;

use App\Modules\Department\Entity\Department;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     * countWhenPersonIsHeadOf
     * @param Person $headTeacher
     * @param array $staffIDList
     * @param bool $includeAssistant
     * @return int
     * 19/06/2020 14:19
     */
    public function countWhenPersonIsHeadOf(Person $headTeacher, array $staffIDList, bool $includeAssistant): int
    {
        $coordinatorList = [];
        $coordinatorList[] = 'Coordinator';
        if ($includeAssistant) {
            $coordinatorList[] = 'Assistant Coordinator';
        }
        try {
            return intval($this->createQueryBuilder('ht')
                ->select(['COUNT(d.id)'])
                ->leftJoin('ht.department', 'd')
                ->leftJoin('d.staff', 's')
                ->leftJoin('s.person','p')
                ->where('p.id IN (:staffList)')
                ->andWhere('ht.person = :headTeacher')
                ->andWhere('ht.role IN (:leaders)')
                ->setParameter('headTeacher', $headTeacher)
                ->setParameter('staffList', $staffIDList, Connection::PARAM_STR_ARRAY)
                ->setParameter('leaders', $coordinatorList, Connection::PARAM_STR_ARRAY)
                ->getQuery()
                ->getSingleResult()
            );
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

}

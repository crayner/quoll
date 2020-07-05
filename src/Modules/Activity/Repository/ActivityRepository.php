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
namespace App\Modules\Activity\Repository;

use App\Modules\Activity\Entity\Activity;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ActivityRepository
 * @package App\Modules\Activity\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActivityRepository extends ServiceEntityRepository
{
    /**
     * ActivityRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * findByStaff
     * @param Person $person
     * @return array
     */
    public function findByStaff(Person $person): array
    {
        return $this->createQueryBuilder('a')
            ->select('DISTINCT a')
            ->leftJoin('a.staff', 'a_s')
            ->where('a_s.person = :person')
            ->setParameter('person', $person)
            ->andWhere('a.academicYear = :academicYear')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }

    /**
     * findByStudent
     * @param Person $person
     * @return array
     */
    public function findByStudent(Person $person): array
    {
        return $this->createQueryBuilder('a')
            ->select('DISTINCT a')
            ->leftJoin('a.students', 'a_s')
            ->where('a_s.person = :person')
            ->setParameter('person', $person)
            ->andWhere('a.academicYear = :academicYear')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }

    /**
     * findForPagination
     * @param bool $activeOnly
     * @return array
     */
    public function findForPagination(bool $activeOnly = true): array
    {
        $query = $this->createQueryBuilder('a')
            ->select(['a','s','d', 'a_s'])
            ->leftJoin('a.slots', 's')
            ->leftJoin('s.dayOfWeek', 'd')
            ->leftJoin('a.students', 'a_s')
            ->orderBy('a.name', 'ASC');
        if ($activeOnly)
            $query->where('a.active = :yes')
                ->setParameter('yes', 'Y');
        return $query->getQuery()
            ->getResult();
    }
}

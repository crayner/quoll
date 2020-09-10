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
 * Time: 16:10
 */
namespace App\Modules\Enrolment\Repository;

use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\People\Entity\Person;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StudentEnrolmentRepository
 * @package App\Modules\Enrolment\Repository
 */
class StudentEnrolmentRepository extends ServiceEntityRepository
{
    /**
     * StudentEnrolmentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentEnrolment::class);
    }

    /**
     * @param Person $person
     * @return array
     */
    public function findStaffYearGroupsByRollGroup(Person $person): array
    {
        $x = $this->createQueryBuilder('se')
            ->select('DISTINCT yg.id AS yearGroupList')
            ->leftJoin('se.yearGroup', 'yg')
            ->leftJoin('se.rollGroup', 'rg')
            ->where('rg.tutor = :person OR rg.tutor2 = :person OR rg.tutor3 = :person')
            ->setParameter('person', $person)
            ->andWhere('se.academicYear = :academicYear')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
        $results = [];
        foreach($x as $list)
            $results = array_merge($results, [str_pad($list['yearGroupList'],3, '0', STR_PAD_LEFT)]);

        return array_unique($results);
    }

    /**
     * @param Person $person
     * @return array
     */
    public function findStudentYearGroup(Person $person): array
    {
        $x = $this->createQueryBuilder('se')
            ->select('DISTINCT yg.id AS yearGroupList')
            ->leftJoin('se.yearGroup', 'yg')
            ->where('se.academicYear = :academicYear')
            ->andWhere('se.person = :person')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();
        $results = [];
        foreach($x as $list)
            $results = array_merge($results, explode(',',$list['yearGroupList']));

        return array_unique($results);
    }

    /**
     * getStudentEnrolmentCount
     * @param integer|null $AcademicYearID
     * @return int
     */
    public function getStudentEnrolmentCount(?int $AcademicYearID = null): int
    {
        try {
            return intval($this->createQueryBuilder('se')
                ->select('COUNT(s.id)')
                ->join('se.student', 's')
                ->join('se.rollGroup', 'rg')
                ->join('rg.academicYear', 'ay')
                ->where('ay.id = :ay_id')
                ->setParameter('ay_id', intval($AcademicYearID))
                ->getQuery()
                ->getSingleScalarResult());
        } catch ( NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * countEnrolmentsByAcademicYear
     * @param AcademicYear $year
     * @return int
     */
    public function countEnrolmentsByAcademicYear(AcademicYear $year): int
    {
        try {
            return intval($this->createQueryBuilder('se')
                ->select('COUNT(se.id)')
                ->where('se.academicYear = :year')
                ->setParameter('year', $year)
                ->getQuery()
                ->getSingleScalarResult());
        } catch ( NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * countEnrolmentsByYearGroup
     * @param YearGroup $year
     * @return int
     */
    public function countEnrolmentsByYearGroup(YearGroup $year): int
    {
        try {
            return intval($this->createQueryBuilder('se')
                ->select('COUNT(se.id)')
                ->where('se.yearGroup = :year')
                ->setParameter('year', $year)
                ->getQuery()
                ->getSingleScalarResult());
        } catch ( NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}

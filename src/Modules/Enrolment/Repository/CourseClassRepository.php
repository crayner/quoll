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
namespace App\Modules\Enrolment\Repository;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CourseClassRepository
 * @package App\Modules\Enrolment\Repository
 */
class CourseClassRepository extends ServiceEntityRepository
{
    /**
     * CourseClassRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseClass::class);
    }

    /**
     * @param Person $person
     * @return array
     */
    public function findByPerson(Person $person): array
    {
        return $this->createQueryBuilder('cc')
            ->select('DISTINCT cc')
            ->leftJoin('cc.courseClassPeople', 'ccp')
            ->leftJoin('cc.course', 'c')
            ->where('ccp.person = :person')
            ->setParameter('person', $person)
            ->andWhere('c.academicYear = :academicYear')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }

    /**
     * findAccessibleClasses
     * @param AcademicYear $academicYear
     * @param string $classTitle
     * @return mixed
     */
    public function findAccessibleClasses(AcademicYear $academicYear, string $classTitle)
    {
        $result = $this->createQueryBuilder('cc')
            ->select([
                "CONCAT('Cla-', cc.id) As id",
                "CONCAT('" . $classTitle . "', c.nameShort, '.', cc.nameShort) AS text",
                'c.name AS search'
            ])
            ->join('cc.course', 'c')
            ->where('c.academicYear = :academicYear')
            ->setParameter('academicYear', $academicYear)
            ->orderBy('text')
            ->getQuery()
            ->getResult();
        return $result;
    }

    /**
     * findByPersonAcademicYear
     * @param AcademicYear $academicYear
     * @param Person $person
     * @return mixed
     */
    public function findByAcademicYearPerson(AcademicYear $academicYear, Person $person)
    {
        return $this->createQueryBuilder('cc')
            ->distinct()
            ->leftJoin('cc.course', 'c')
            ->leftjoin('cc.courseClassPeople', 'ccp', 'with', 'ccp.person = :person')
            ->where('c.academicYear = :academicYear')
            ->setParameter('academicYear',$academicYear)
            ->setParameter('person', $person)
            ->andWhere('ccp.role NOT LIKE :role')
            ->setParameter('role', '% - Left%')
            ->orderBy('c.abbreviation', 'ASC')
            ->addOrderBy('cc.abbreviation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findCourseClassEnrolmentPagination
     *
     * 3/09/2020 08:42
     * @return array
     */
    public function findCourseClassEnrolmentPagination(): array
    {
        return $this->createQueryBuilder('cc')
            ->select(
                [
                    "CONCAT(c.abbreviation,' (',c.name,')') AS courseName",
                    'cc.name',
                    'cc.abbreviation',
                    "'0' AS activeParticipants",
                    "'0' AS expectedParticipants",
                    "'0' As totalParticipants",
                    'cc.id',
                    'c.id AS course_id',
                    'yg.name AS yearGroup',
                ]
            )
            ->leftJoin('cc.course', 'c')
            ->leftJoin('c.yearGroups', 'yg')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('cc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

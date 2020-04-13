<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Enrolment\Repository;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CourseClassPersonRepository
 * @package App\Modules\Enrolment\Repository
 */
class CourseClassPersonRepository extends ServiceEntityRepository
{
    /**
     * CourseClassPersonRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseClassPerson::class);
    }

    /**
     * findAccessibleClasses
     * @param AcademicYear $year
     * @param Person $person
     * @return mixed
     */
    public function findAccessibleClasses(AcademicYear $year, Person $person, string $classTitle)
    {
        $result = $this->createQueryBuilder('ccp')
            ->select([
                "CONCAT('Cla-', cc.id) AS id",
                "CONCAT('" . $classTitle . "', c.nameShort, '.', cc.nameShort) AS text",
                'c.name AS search'
            ])
            ->join('ccp.courseClass', 'cc')
            ->join('cc.course', 'c')
            ->where('c.academicYear = :academicYear')
            ->andWhere('ccp.person = :person')
            ->orderBy('text')
            ->setParameters(['academicYear' => $year, 'person' => $person])
            ->getQuery()
            ->getResult();
        return $result;
    }

    /**
     * findStudentsInClass
     * @param CourseClass $class
     * @param AcademicYear $schoolYear
     * @param \DateTime $date
     * @return array
     */
    public function findStudentsInClass(CourseClass $class, AcademicYear $schoolYear, \DateTime $date): array
    {
        return $this->createQueryBuilder('ccp')
            ->select(['ccp.role','p.surname','p.preferredName','p.email','p.studentID','rg.nameShort AS rollGroup'])
            ->join('ccp.person', 'p')
            ->join('p.studentEnrolments', 'se')
            ->join('se.rollGroup', 'rg')
            ->where('ccp.courseClass = :courseClass')
            ->andWhere('p.status = :full')
            ->andWhere('(p.dateStart IS NULL OR p.dateStart <= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd >= :today)')
            ->andWhere('se.academicYear = :academicYear')
            ->setParameter('courseClass', $class)
            ->setParameter('full', 'Full')
            ->setParameter('academicYear', $schoolYear)
            ->setParameter('today', $date)
            ->orderBy('ccp.role')
            ->addOrderBy('p.surname')
            ->addOrderBy('p.preferredName')
            ->getQuery()
            ->getResult();
    }
}

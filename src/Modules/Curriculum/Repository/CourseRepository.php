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
namespace App\Modules\Curriculum\Repository;

use App\Modules\Curriculum\Entity\Course;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\Department\Entity\Department;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CourseRepository
 * @package App\Modules\Curriculum\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseRepository extends ServiceEntityRepository
{
    /**
     * CourseRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @return array
     */
    public function findByPerson(Person $person): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c')
            ->leftJoin('c.courseClasses', 'cc')
            ->leftJoin('cc.courseClassPeople', 'ccp')
            ->where('ccp.person = :person')
            ->setParameter('person', $person)
            ->andWhere('c.academicYear = :academicYear')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }

    /**
     * findByDepartment
     * @param Department $department
     * @return array
     */
    public function findByDepartment(Department $department, AcademicYear $schoolYear): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.courseClasses', 'cc')
            ->where('c.department = :department')
            ->setParameter('department', $department)
            ->andWhere('c.yearGroupList != :empty')
            ->setParameter('empty', '')
            ->andWhere('c.academicYear = :academicYear')
            ->setParameter('academicYear', $schoolYear)
            ->groupBy('c.id')
            ->orderBy('c.nameShort', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    /*
                    $sqlCourse = "SELECT gibbonCourse.* FROM gibbonCourse
                        JOIN gibbonCourseClass ON (gibbonCourseClass.gibbonCourseID=gibbonCourse.gibbonCourseID)
                        WHERE gibbonDepartmentID=:gibbonDepartmentID
                        AND gibbonYearGroupIDList <> ''
                        AND AcademicYearID=(SELECT AcademicYearID FROM gibbonAcademicYear WHERE status='Current')
                        GROUP BY gibbonCourse.gibbonCourseID
                        ORDER BY nameShort, name";
    */
    }
}

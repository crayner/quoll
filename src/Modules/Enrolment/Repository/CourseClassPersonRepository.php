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
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\People\Entity\Person;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\School\Entity\AcademicYear;
use App\Util\StringHelper;
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
     * findCourseClassParticipationNonStudent
     *
     * 3/09/2020 12:16
     * @param CourseClass $class
     * @return array
     */
    public function findCourseClassParticipationNonStudent(CourseClass $class): array
    {
        return $this->createQueryBuilder('ccp')
            ->select(
                [
                    'ccp.role',
                    "CONCAT(".PersonNameManager::formatNameQuery('p', 'Staff', 'Reversed').") AS name",
                    'c.email',
                    "CASE WHEN ccp.reportable = 1 THEN '".StringHelper::getYesNo(true)."' ELSE '".StringHelper::getYesNo(false)."' END AS reportable",
                    'ccp.id',
                    'cc.id AS course_class_id',
                    'course.id AS course_id'
                ]
            )
            ->where('ccp.role <> :student')
            ->setParameter('student', 'Student')
            ->andWhere('ccp.courseClass = :course_class')
            ->setParameter('course_class', $class)
            ->leftJoin('ccp.person', 'p')
            ->leftJoin('ccp.courseClass', 'cc')
            ->leftJoin('p.contact', 'c')
            ->leftJoin('cc.course', 'course')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName','ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findCourseClassParticipationStudent
     *
     * 3/09/2020 12:17
     * @param CourseClass $class
     * @return array
     */
    public function findCourseClassParticipationStudent(CourseClass $class): array
    {
        return $this->createQueryBuilder('ccp')
            ->select(
                [
                    'ccp.role',
                    "CONCAT(".PersonNameManager::formatNameQuery('p', 'Student', 'Reversed').") AS name",
                    'c.email',
                    "CASE WHEN ccp.reportable = 1 THEN '".StringHelper::getYesNo(true)."' ELSE '".StringHelper::getYesNo(false)."' END AS reportable",
                    'ccp.id',
                    'cc.id AS course_class_id',
                    'course.id AS course_id'
                ]
            )
            ->where('ccp.role = :student')
            ->setParameter('student', 'Student')
            ->andWhere('ccp.courseClass = :course_class')
            ->setParameter('course_class', $class)
            ->leftJoin('ccp.person', 'p')
            ->leftJoin('ccp.courseClass', 'cc')
            ->leftJoin('p.contact', 'c')
            ->leftJoin('cc.course', 'course')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName','ASC')
            ->getQuery()
            ->getResult();
    }
}

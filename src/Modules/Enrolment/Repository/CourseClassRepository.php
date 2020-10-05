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

use App\Modules\Department\Entity\Department;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\YearGroup;
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
     * findCourseClassEnrolmentPagination
     *
     * 7/09/2020 09:24
     * @return array
     */
    public function findCourseClassEnrolmentPagination(): array
    {
        return $this->createQueryBuilder('cc', 'cc.id')
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
            ->orderBy('yg.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->addOrderBy('cc.name', 'ASC')
            ->where('c.academicYear = :year')
            ->setParameter('year', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }

    /**
     * findByAcademicYear
     *
     * 7/09/2020 09:24
     * @param CourseClass $class
     * @return int|mixed|string
     */
    public function findByAcademicYearYearGroups(CourseClass $class)
    {
        $where = [];
        $params = [];
        foreach ($class->getCourse()->getYearGroups() as $q=>$yg) {
            $where[] = 'yg.id = :yg'.$q;
            $params['yg'.$q] = $yg->getId();
        }
        $params['year'] = AcademicYearHelper::getCurrentAcademicYear();
        return $this->createQueryBuilder('cc')
            ->select(['cc','c'])
            ->leftJoin('cc.course', 'c')
            ->leftJoin('c.yearGroups', 'yg')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('cc.name', 'ASC')
            ->where('c.academicYear = :year')
            ->andWhere('(' . implode(' OR ', $where) . ')')
            ->setParameters($params)
            ->getQuery()
            ->getResult();
    }

    /**
     * countStudentParticipants
     *
     * 20/09/2020 08:58
     * @param string $status
     * @return array
     */
    public function countStudentParticipants(string $status = '%'): array
    {
        return $this->createQueryBuilder('cc', 'cc.id')
            ->select(['COUNT(ccs.id) AS participants','cc.id'])
            ->leftJoin('cc.courseClassStudents', 'ccs')
            ->leftJoin('ccs.student', 's')
            ->leftJoin('s.person','p')
            ->where('p.status LIKE :full')
            ->setParameter('full', $status)
            ->groupBy('cc.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * findEnrolableClasses
     *
     * 11/09/2020 08:27
     * @param Person $person
     * @return array
     */
    public function findEnrolableClasses(Person $person): array
    {
        if ($person->isStudent()) {
            $se = $person->getStudent()->getCurrentEnrolment();
            $yg = $se ? $se->getYearGroup() : null;
            $query = $this->createQueryBuilder('cc', 'cc.id')
                ->select(['cc','c','ccs','s','t'])
                ->leftJoin('cc.course', 'c')
                ->leftJoin('cc.tutors', 't')
                ->leftJoin('cc.courseClassStudents', 'ccs')
                ->leftJoin('ccs.student', 's')
                ->leftJoin('c.yearGroups', 'yg')
                ->where('c.academicYear = :current')
                ->orderBy('yg.sortOrder', 'ASC')
                ->addOrderBy('c.name', 'ASC')
                ->addOrderBy('cc.name', 'ASC')
                ->addOrderBy('t.sortOrder', 'ASC')
                ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear());

            if ($yg instanceof YearGroup) {
                $yg->getId();
                $query
                    ->andWhere('yg.id = :yearGroup')
                    ->setParameter('yearGroup', $yg->getId());
            }
            return $query
                ->getQuery()
                ->getResult();
        } else {
            $yg = null;
        }
    }

    /**
     * findClassesByCurrentAcademicYear
     *
     * 17/09/2020 10:51
     * @return array
     */
    public function findClassesByCurrentAcademicYear(): array
    {
        return $this->createQueryBuilder('cc', 'cc.id')
            ->select(['cc','c','ccs','s','t'])
            ->leftJoin('cc.course', 'c')
            ->leftJoin('cc.courseClassStudents', 'ccs')
            ->leftJoin('ccs.student', 's')
            ->leftJoin('cc.tutors', 't')
            ->leftJoin('c.yearGroups', 'yg')
            ->where('c.academicYear = :current')
            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
            ->orderBy('yg.sortOrder', 'ASC')
            ->addOrderBy('c.abbreviation')
            ->addOrderBy('cc.abbreviation')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByAcademicYearPerson
     *
     * 3/10/2020 07:52
     * @param Person $person
     * @return array
     */
    public function findByAcademicYearPerson(Person $person): array
    {
        if ($person->isStudent()) {
            return $this->createQueryBuilder('cc')
                ->select(['cc','c','ccs'])
                ->leftJoin('cc.course','c')
                ->leftJoin('cc.courseClassStudents','ccs')
                ->where('c.academicYear = :current')
                ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                ->andWhere('ccs.student = :student')
                ->setParameter('student',$person->getStudent())
                ->orderBy('c.abbreviation','ASC')
                ->addOrderBy('cc.name','ASC')
                ->getQuery()
                ->getResult();
        }
        return $this->createQueryBuilder('cc')
            ->select(['cc','c'])
            ->leftJoin('cc.course','c')
            ->leftJoin('cc.tutors', 't')
            ->where('c.academicYear = :current')
            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
            ->andWhere('t.staff = :tutor')
            ->setParameter('tutor',$person->getStaff())
            ->orderBy('c.abbreviation','ASC')
            ->addOrderBy('cc.name','ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByDepartment
     *
     * 4/10/2020 17:24
     * @param Department $department
     * @return array
     */
    public function findByDepartment(Department $department): array
    {
        return $this->createQueryBuilder('cc')
            ->select(['cc','c'])
            ->leftJoin('cc.course', 'c')
            ->where('c.department = :department')
            ->andWhere('c.academicYear = :current')
            ->setParameters(['department' => $department, 'current' => AcademicYearHelper::getCurrentAcademicYear()])
            ->getQuery()
            ->getResult();
    }

    /**
     * findByYearGroup
     *
     * 4/10/2020 17:39
     * @param YearGroup $yearGroup
     * @return array
     */
    public function findByYearGroup(YearGroup $yearGroup): array
    {
        return $this->createQueryBuilder('cc')
            ->select(['cc','c'])
            ->leftJoin('cc.course', 'c')
            ->leftJoin('c.yearGroups', 'yg')
            ->where('yg.id = :year_group')
            ->andWhere('c.academicYear = :current')
            ->setParameters(['year_group' => $yearGroup->getId(), 'current' => AcademicYearHelper::getCurrentAcademicYear()])
            ->getQuery()
            ->getResult();
    }
}

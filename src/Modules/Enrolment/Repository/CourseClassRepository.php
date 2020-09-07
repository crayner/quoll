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
}

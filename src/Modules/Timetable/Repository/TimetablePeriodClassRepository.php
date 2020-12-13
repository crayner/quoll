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
 * Time: 17:00
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TimetablePeriodClassRepository
 *
 * 13/10/2020 15:59
 * @package App\Modules\Timetable\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetablePeriodClassRepository extends ServiceEntityRepository
{
    /**
     * TimetablePeriodClassRepository constructor.
     *
     * 13/10/2020 15:59
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetablePeriodClass::class);
    }

    /**
     * getPeriodClassesPagination
     *
     * 13/10/2020 15:40
     * @param TimetablePeriod $period
     * @return array
     */
    public function getPeriodClassesPagination(TimetablePeriod $period)
    {
        return $this->createQueryBuilder('c')
            ->select(['c','p','cc','co','f'])
            ->leftJoin('c.period', 'p')
            ->where('c.period = :period')
            ->setParameter('period', $period)
            ->leftJoin('c.courseClass', 'cc')
            ->leftJoin('cc.course', 'co')
            ->leftJoin('c.facility', 'f')
            ->orderBy('co.abbreviation', 'ASC')
            ->addOrderBy('cc.name','ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByPeriod
     *
     * 14/10/2020 09:28
     * @param TimetablePeriod $period
     * @param string|null $idOnly
     * @return array
     */
    public function findByPeriod(TimetablePeriod $period, ?string $idOnly = null): array
    {
        if ($idOnly !== null) {
            $select = [$idOnly];
        } else {
            $select = ['pc','cc','p','f','c'];
        }
        return $this->createQueryBuilder('pc')
            ->select($select)
            ->leftJoin('pc.courseClass', 'cc')
            ->leftJoin('cc.course', 'c')
            ->leftJoin('pc.period', 'p')
            ->leftJoin('pc.facility', 'f')
            ->where('pc.period = :period')
            ->setParameter('period', $period)
            ->orderBy('c.abbreviation', 'ASC')
            ->addOrderBy('cc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findCourseClassDate
     *
     * 12/11/2020 12:05
     * @param CourseClass|null $class
     * @param DateTimeImmutable|null $date
     * @return array
     */
    public function findCourseClassDate(?CourseClass $class, ?DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('tpc')
            ->leftJoin('tpc.period', 'tp')
            ->leftJoin('tp.timetableDay', 'tDay')
            ->leftJoin('tDay.timetableDates', 'tDate')
            ->where('tpc.courseClass = :courseClass')
            ->andWhere('tDate.date = :date')
            ->setParameters(['date' => $date,'courseClass' => $class])
            ->orderBy('tp.timeStart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * countCourseClassDate
     *
     * 12/11/2020 11:29
     * @param CourseClass $class
     * @param DateTimeImmutable $date
     * @return int
     */
    public function countCourseClassDate(CourseClass $class, DateTimeImmutable $date): int
    {
        try {
            return $this->createQueryBuilder('tpc')
                ->select('COUNT(tDate.id)')
                ->leftJoin('tpc.period', 'tp')
                ->leftJoin('tp.timetableDay','tDay')
                ->leftJoin('tDay.timetableDates', 'tDate')
                ->where('tpc.courseClass = :courseClass')
                ->andWhere('tDate.date = :date')
                ->setParameters(['date' => $date,'courseClass' => $class])
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}

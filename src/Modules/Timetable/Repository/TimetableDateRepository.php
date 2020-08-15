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
 * Time: 16:57
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetableDate;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTDayDateRepository
 * @package App\Modules\Timetable\Repository
 */
class TimetableDateRepository extends ServiceEntityRepository
{
    /**
     * TTDayDateRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetableDate::class);
    }

    /**
     * isSchoolOpen
     * @param DateTimeImmutable $date
     * @return bool
     * 9/08/2020 12:07
     */
    public function isSchoolOpen(DateTimeImmutable $date): bool
    {
        try {
            return intval($this->createQueryBuilder('tdd')
                    ->select('COUNT(tdd.id)')
                    ->where('tdd.date = :date')
                    ->setParameter('date', $date)
                    ->getQuery()
                    ->getSingleScalarResult()) > 0;
        } catch (NoResultException | NonUniqueResultException $e) {
            return false;
        }
    }

    /**
     * findAllLikeDate
     * @param string $date
     * @return mixed
     */
    public function findAllLikeDate(string $date)
    {
        return $this->createQueryBuilder('tdd')
            ->where('tdd.date LIKE :date')
            ->setParameter('date', $date.'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByTimetable
     * @param Timetable $timetable
     * @return array
     * 6/08/2020 13:46
     */
    public function findByTimetable(Timetable $timetable): array
    {
        return $this->createQueryBuilder('dd')
            ->select(['dd','d','t'])
            ->orderBy('dd.date','ASC')
            ->leftJoin('dd.timetableDay', 'd')
            ->leftJoin('d.timetable', 't')
            ->where('d.timetable = :timetable')
            ->setParameter('timetable', $timetable)
            ->getQuery()
            ->getResult();
    }

    /**
     * findIneByTimetableDate
     * @param Timetable $timetable
     * @param DateTimeImmutable $date
     * @return TimetableDate|null
     * 9/08/2020 12:07
     */
    public function findOneByTimetableDate(Timetable $timetable, DateTimeImmutable $date): ?TimetableDate
    {
        try {
            return $this->createQueryBuilder('dd')
                ->select(['dd', 'd', 't', 'w'])
                ->leftJoin('dd.timetableDay', 'd')
                ->leftJoin('d.timetable', 't')
                ->leftJoin('d.daysOfWeek', 'w')
                ->where('d.timetable = :timetable')
                ->setParameter('timetable', $timetable)
                ->andWhere('dd.date = :date')
                ->setParameter('date', $date)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @var array|null
     */
    private $dayDates;

    /**
     * createTimetableDate
     * @param TimetableDay $td
     * @param DateTimeImmutable $date
     * 8/08/2020 09:29
     */
    public function createTimetableDate(TimetableDay $td, DateTimeImmutable $date)
    {
        $t = $td->getTimetable();
        if ($this->dayDates === null || !key_exists($t->getName(), $this->dayDates)) {
            $tDates = [];
            foreach ($this->findByTimetable($t) as $dayDate) {
                $tDates[$dayDate->getDate()->format('Ymd')] = $dayDate;
            }
            $this->dayDates[$t->getName()] = $tDates;
        }
        if (!key_exists($date->format('Ymd'), $this->dayDates[$t->getName()])) {
            $this->dayDates[$t->getName()][$date->format('Ymd')] = new TimetableDate($td, $date);
        }
        return $this->dayDates[$t->getName()][$date->format('Ymd')];
    }

    /**
     * countByTimetableDay
     * @param TimetableDay $day
     * @return int
     * 13/08/2020 08:37
     */
    public function countByTimetableDay(TimetableDay $day): int
    {
        try {
            return intval($this->createQueryBuilder('date')
                ->select('COUNT(day.id)')
                ->where('date.timetableDay = :timetable')
                ->leftJoin('date.timetableDay', 'day')
                ->setParameter('timetable', $day)
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}

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
 * Time: 16:52
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTDayRepository
 * @package App\Modules\Timetable\Repository
 */
class TimetableDayRepository extends ServiceEntityRepository
{
    /**
     * TTDayRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetableDay::class);
    }

    /**
     * findByDateTT
     * @param \DateTime $date
     * @param Timetable $tt
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByDateTT(\DateTime $date, Timetable $tt)
    {
        return $this->createQueryBuilder('td')
            ->select('td,tdd,tc,tcr')
            ->join('td.timetableDayDates', 'tdd')
            ->join('td.TTColumn', 'tc')
            ->join('tc.timetableColumnPeriods', 'tcr')
            ->where('tdd.date = :date')
            ->setParameter('date', $date)
            ->andWhere('td.TT = :timetable')
            ->setParameter('timetable', $tt)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

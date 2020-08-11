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
 * Time: 16:35
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TimetableDayRepository
 * @package App\Modules\Timetable\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayRepository extends ServiceEntityRepository
{
    /**
     * TTColumnRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetableDay::class);
    }

    /**
     * findAll
     * @return array|int|mixed|string
     * 10/08/2020 11:15
     */
    public function findAll()
    {
        return $this->createQueryBuilder('c')
            ->select(['c','dow'])
            ->leftJoin('c.daysOfWeek', 'dow')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByDateTT
     * @param DateTimeImmutable $date
     * @param Timetable $tt
     * @return mixed
     */
    public function findByDateTT(DateTimeImmutable $date, Timetable $tt)
    {
        try {
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
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * nextRotateOrder
     * @param Timetable $timetable
     * @return int
     * 6/08/2020 13:01
     */
    public function nextRotateOrder(Timetable $timetable): int
    {
        try {
            return intval($this->createQueryBuilder('d')
                    ->select(['d.rotateOrder'])
                    ->where('d.timetable = :timetable')
                    ->setParameter('timetable', $timetable)
                    ->orderBy('d.rotateOrder', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleScalarResult()
                ) + 1;
        } catch (NoResultException | NonUniqueResultException $e) {
            return 1;
        }
    }
}

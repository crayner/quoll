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

use App\Modules\Timetable\Entity\TT;
use App\Modules\Timetable\Entity\TTDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTDayRepository
 * @package App\Modules\Timetable\Repository
 */
class TTDayRepository extends ServiceEntityRepository
{
    /**
     * TTDayRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TTDay::class);
    }

    /**
     * findByDateTT
     * @param \DateTime $date
     * @param TT $tt
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByDateTT(\DateTime $date, TT $tt)
    {
        return $this->createQueryBuilder('td')
            ->select('td,tdd,tc,tcr')
            ->join('td.timetableDayDates', 'tdd')
            ->join('td.TTColumn', 'tc')
            ->join('tc.timetableColumnRows', 'tcr')
            ->where('tdd.date = :date')
            ->setParameter('date', $date)
            ->andWhere('td.TT = :timetable')
            ->setParameter('timetable', $tt)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

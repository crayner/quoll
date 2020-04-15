<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:57
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\Timetable\Entity\TTDayDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTDayDateRepository
 * @package App\Modules\Timetable\Repository
 */
class TTDayDateRepository extends ServiceEntityRepository
{
    /**
     * TTDayDateRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TTDayDate::class);
    }

    /**
     * isSchoolOpen
     * @param \DateTime $date
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException|\Doctrine\ORM\NoResultException
     */
    public function isSchoolOpen(\DateTime $date): bool
    {
       return intval($this->createQueryBuilder('tdd')
                ->select('COUNT(tdd.id)')
                ->where('tdd.date LIKE :date')
                ->setParameter('date', $date->format('Y-m-d').'%')
                ->getQuery()
                ->getSingleScalarResult()) > 0;
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
}

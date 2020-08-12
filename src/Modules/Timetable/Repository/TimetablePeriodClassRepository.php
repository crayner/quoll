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

use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTDayRowClassRepository
 * @package App\Modules\Timetable\Repository
 */
class TimetablePeriodClassRepository extends ServiceEntityRepository
{
    /**
     * TTDayRowClassRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetablePeriodClass::class);
    }
}

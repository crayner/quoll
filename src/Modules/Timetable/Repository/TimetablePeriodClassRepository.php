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
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}

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
 * Time: 16:45
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\Timetable\Entity\TimetableColumnPeriod;
use App\Modules\Timetable\Entity\TimetableDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTColumnRowRepository
 * @package App\Modules\Timetable\Repository
 */
class TimetableColumnPeriodRepository extends ServiceEntityRepository
{
    /**
     * TTColumnRowRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetableColumnPeriod::class);
    }

    /**
     * findPersonPeriods
     * @param TimetableDay $day
     * @param Person $person
     * @param bool $asArray
     * @return array|int|mixed|string
     * 4/08/2020 12:06
     */
    public function findPersonPeriods(TimetableDay $day, Person $person, bool $asArray = false)
    {
        $query = $this->createQueryBuilder('tcr')
            ->select('c,cc,tdrc,tcr,s')
            ->join('tcr.timetableDayRowClasses', 'tdrc')
            ->join('tdrc.courseClass', 'cc')
            ->join('cc.course', 'c')
            ->join('cc.courseClassPeople', 'ccp')
            ->leftJoin('tdrc.space', 's')
            ->where('tdrc.timetableDay = :day')
            ->setParameter('day', $day)
            ->andWhere('ccp.person = :person')
            ->setParameter('person', $person)
            ->andWhere('ccp.role NOT LIKE :role')
            ->setParameter('role', '% - Left')
            ->orderBy('tcr.timeStart', 'ASC')
            ->addOrderBy('tcr.timeEnd', 'ASC')
            ->getQuery();

        if ($asArray)
            $result = $query->getArrayResult();
        else
            $result = $query->getResult();

        return $result;
    }
}

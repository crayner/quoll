<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:45
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\People\Entity\Person;
use App\Entity\TTColumnRow;
use App\Entity\TTDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTColumnRowRepository
 * @package App\Modules\Timetable\Repository
 */
class TTColumnRowRepository extends ServiceEntityRepository
{
    /**
     * TTColumnRowRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TTColumnRow::class);
    }

    /**
     * findPersonPeriods
     * @param TTDay $day
     * @param Person $person
     */
    public function findPersonPeriods(TTDay $day, Person $person, bool $asArray = false)
    {
        $query = $this->createQueryBuilder('tcr')
            ->select('c,cc,tdrc,tcr,s')
            ->join('tcr.TTDayRowClasses', 'tdrc')
            ->join('tdrc.courseClass', 'cc')
            ->join('cc.course', 'c')
            ->join('cc.courseClassPeople', 'ccp')
            ->leftJoin('tdrc.space', 's')
            ->where('tdrc.TTDay = :day')
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

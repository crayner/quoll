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
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\MarkBook\Repository;

use App\Modules\MarkBook\Entity\MarkBookEntry;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class MarkBookEntryRepository
 * @package App\Modules\MarkBook\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookEntryRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkBookEntry::class);
    }

    /**
     * findAttainmentOrEffortConcerns
     * @param Person $person
     * @param AcademicYear $schoolYear
     * @return int|mixed|string
     * @throws \Exception
     * 20/06/2020 13:02
     */
    public function findAttainmentOrEffortConcerns(Person $person, AcademicYear $schoolYear)
    {
        return $this->createQueryBuilder('me')
            ->join('me.markBookColumn', 'mc')
            ->join('mc.courseClass', 'cc')
            ->join('cc.course', 'c')
            ->where('me.student = :person')
            ->andWhere('(me.attainmentConcern = :yes OR me.effortConcern = :yes)')
            ->andWhere('mc.complete = :yes')
            ->andWhere('c.academicYear = :academicYear')
            ->andWhere('mc.completeDate <= :today')
            ->andWhere('mc.completeDate > :date')
            ->setParameter('person', $person)
            ->setParameter('yes', 'Y')
            ->setParameter('academicYear', $schoolYear)
            ->setParameter('today', new \DateTimeImmutable(date('Y-m-d'))) // today
            ->setParameter('date', new \DateTimeImmutable(date('Y-m-d', strtotime('-60 day')))) // 60 days ago
            ->getQuery()
            ->getResult();
    }

}

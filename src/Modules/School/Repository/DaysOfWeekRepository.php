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
namespace App\Modules\School\Repository;

use App\Modules\School\Entity\DaysOfWeek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class DaysOfWeekRepository
 * @package App\Modules\School\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DaysOfWeekRepository extends ServiceEntityRepository
{
    /**
     * DaysOfWeekRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DaysOfWeek::class);
    }

    /**
     * @var array
     */
    private $daysOfWeek;

    /**
     * @var array
     */
    private $daysOfWeekByName;

    /**
     * getDaysOfWeek
     * @return array
     */
    public function findAllAsArray(): array
    {
        if (null === $this->daysOfWeek) {
            $this->daysOfWeek = $this->createQueryBuilder('d', 'd.abbreviation')
                ->getQuery()
                ->getArrayResult();
        }
        return $this->daysOfWeek;
    }

    /**
     * findAllByName
     * @return array
     */
    public function findAllByName(): array
    {
        if ($this->daysOfWeekByName === null) {
            $this->daysOfWeekByName = $this->createQueryBuilder('d', 'd.name')
                ->orderBy('d.sequenceNumber')
                ->getQuery()
                ->getResult();
        }
        return $this->daysOfWeekByName;
    }
}

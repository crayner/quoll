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
}

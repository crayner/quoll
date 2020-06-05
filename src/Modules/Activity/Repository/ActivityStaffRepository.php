<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Activity\Repository;

use App\Modules\Activity\Entity\ActivityStaff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ActivityStaffRepository
 * @package Kookaburra\Activities\Repository
 */
class ActivityStaffRepository extends ServiceEntityRepository
{
    /**
     * ActivityStaffRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityStaff::class);
    }
}

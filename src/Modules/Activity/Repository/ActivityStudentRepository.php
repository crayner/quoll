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
namespace App\Modules\Activity\Repository;

use App\Modules\Activity\Entity\ActivityStudent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ActivityStudentRepository
 * @package App\Modules\Activity\Repository
 */
class ActivityStudentRepository extends ServiceEntityRepository
{
    /**
     * ActivityStudentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityStudent::class);
    }
}

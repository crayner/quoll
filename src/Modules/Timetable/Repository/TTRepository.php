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
 * Time: 16:35
 */
namespace App\Modules\Timetable\Repository;

use App\Modules\Timetable\Entity\TT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TTRepository
 * @package App\Modules\Timetable\Repository
 */
class TTRepository extends ServiceEntityRepository
{
    /**
     * TTRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TT::class);
    }
}

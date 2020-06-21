<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 21:46
 */
namespace App\Modules\Curriculum\Repository;

use App\Modules\Curriculum\Entity\Unit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UnitRepository
 * @package App\Modules\Curriculum\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class UnitRepository extends ServiceEntityRepository
{
    /**
     * UnitRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unit::class);
    }
}

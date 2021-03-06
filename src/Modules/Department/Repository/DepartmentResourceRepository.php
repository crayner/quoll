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
namespace App\Modules\Department\Repository;

use App\Modules\Department\Entity\DepartmentResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class DepartmentResourceRepository
 * @package App\Modules\School\Repository
 */
class DepartmentResourceRepository extends ServiceEntityRepository
{
    /**
     * DepartmentResourceRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepartmentResource::class);
    }
}

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
namespace App\Modules\System\Repository;

use App\Modules\Security\Entity\Role;
use App\Modules\System\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ModuleRepository
 * @package App\Modules\System\Repository
 */
class ModuleRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    /**
     * findWithActions
     * @return array
     */
    public function findWithActions(): array
    {
        return $this->createQueryBuilder('m')
            ->select(['m','a','a.name AS actionName'])
            ->join('m.actions','a')
            ->orderBy('m.category')
            ->addOrderBy('actionName')
            ->addOrderBy('a.name', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

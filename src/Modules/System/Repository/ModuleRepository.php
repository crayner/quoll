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
     * findModuleActionsByRole
     * @param Module $module
     * @param Role $role
     * @return mixed
     */
    public function findModuleActionsByRole(Module $module, Role $role)
    {
        return $this->createQueryBuilder('m')
            ->select(['a.category', 'm.name AS moduleName', 'a.name AS actionName', 'm.type', 'a.precedence', 'm.entryRoute AS moduleEntry', 'a.entryRoute', 'a.routeList', 'a.name AS name'])
            ->join('m.actions', 'a')
            ->join('a.roles', 'r')
            ->where('m.id = :module_id')
            ->setParameter('module_id', intval($module->getId()))
            ->andWhere('r.id = :role')
            ->setParameter('role', intval($role->getId()))
            ->andWhere('a.entryRoute != :empty')
            ->setParameter('empty', '')
            ->andWhere('a.menuShow = :yes')
            ->setParameter('yes', 'Y')
            ->groupBy('a.name')
            ->orderBy('a.category')
            ->addOrderBy('a.name')
            ->addOrderBy('a.precedence', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findWithActions
     * @return array
     */
    public function findWithActions(): array
    {
        return $this->createQueryBuilder('m')
            ->select(['m','a','SUBSTRING_INDEX(a.name, \'_\', 1) as actionName'])
            ->join('m.actions','a')
            ->orderBy('m.category')
            ->addOrderBy('actionName')
            ->addOrderBy('a.name', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

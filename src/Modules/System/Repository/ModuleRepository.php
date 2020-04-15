<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
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
     * findModulesByRole
     * @param Role $role
     * @return array|null
     */
    public function findByRole(Role $role)
    {
        return $this->createQueryBuilder('m')
            ->select(['m.category', 'm.name', 'm.type', 'm.entryURL', 'a.entryURL AS alternateEntryURL'])
            ->join('m.actions', 'a')
            ->join('a.roles', 'r')
            ->where('m.active = :active')
            ->andWhere('a.menuShow = :active')
            ->andWhere('r.id = :role_id')
            ->groupBy('m.name')
            ->orderBy('m.name')
            ->addOrderBy('a.name')
            ->setParameter('active', 'Y')
            ->setParameter('role_id', intval($role->getId()))
            ->getQuery()
            ->getResult();
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
            ->select(['a.category', 'm.name AS moduleName', 'a.name AS actionName', 'm.type', 'a.precedence', 'm.entryURL AS moduleEntry', 'a.entryURL', 'a.URLList', 'a.name AS name'])
            ->join('m.actions', 'a')
            ->join('a.roles', 'r')
            ->where('m.id = :module_id')
            ->setParameter('module_id', intval($module->getId()))
            ->andWhere('r.id = :role')
            ->setParameter('role', intval($role->getId()))
            ->andWhere('a.entryURL != :empty')
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
     * findFastFinderActions
     * @param Role $role
     * @param string $actionTitle
     * @return mixed
     */
    public function findFastFinderActions(Role $role, string $actionTitle)
    {
        return $this->createQueryBuilder('m')
            ->select([
                "CONCAT('Act-', m.name, '/', a.entryURL) AS id",
                "CONCAT('".$actionTitle."', SUBSTRING_INDEX(a.name, '_', 1)) AS text",
                'm.name as search'
            ])
            ->join('m.actions', 'a')
            ->join('a.roles', 'r')
            ->where('m.active = :yes')
            ->andWhere('a.menuShow = :yes')
            ->andWhere('r.id = :role')
            ->orderBy('text', 'ASC')
            ->setParameters(['yes' => 'Y', 'role' => intval($role->getId())])
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}

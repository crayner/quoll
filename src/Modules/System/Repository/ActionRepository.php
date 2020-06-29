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
namespace App\Modules\System\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\People\Util\UserHelper;

/**
 * Class ActionRepository
 * @package App\Modules\System\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActionRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Action::class);
    }

    /**
     * findOneByModuleContainsURL
     * @param Module $module
     * @param string $route
     * @return Action|null
     */
    public function findOneByModuleRoute(Module $module, string $route): ?Action
    {
        try {
            return $this->createQueryBuilder('a')
                ->where('a.module = :module')
                ->setParameter('module', $module)
                ->andWhere('a.routeList LIKE :route')
                ->setParameter('route', '%' . $route . '%')
                ->orderBy('a.precedence', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @var Action[]
     */
    private $actions;

    /**
     * findOneByNameModule
     * @param string $name
     * @param Module $module
     * @return Action|null
     * @throws NonUniqueResultException
     */
    public function findOneByNameModule(string $name, Module $module): ?Action
    {
        $this->actions = $this->actions ?: $this->findAll();

        foreach($this->actions as $action)
            if ($action->getName() === $name && $action->getModule()->isEqualTo($module))
                return $action;

        return $this->createQueryBuilder('a')
            ->where('a.name = :name')
            ->andWhere('a.module = :module')
            ->setParameters(['name' => $name, 'module' => $module])
            ->orderBy('a.precedence', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * findByRouteListModuleRole
     * @param array $criteria
     * @return mixed
     */
    public function findByRouteListModuleRole(array $criteria)
    {
        $result = $this->createQueryBuilder('a')
            ->leftJoin('a.roles', 'r')
            ->where('a.routeList LIKE :name')
            ->andWhere('a.module = :module')
            ->andWhere('r.id = :roleId')
            ->andWhere('a.name LIKE :sub')
            ->setParameters($criteria)
            ->getQuery()
            ->getArrayResult();
        return $result;
    }

    /**
     * findHighestGroupedAction
     * @param string $route
     * @param Module $module
     * @return bool
     */
    public function findHighestGroupedAction(string $route, Module $module)
    {
        if ($route === 'student_view.php') {
            dump($route);
            throw new \Exception('This is the end.');
        }

        $where = '(';
        $params = [];
        $roles = SecurityHelper::getHierarchy()->getReachableRoleNames(UserHelper::getCurrentUser()->getSecurityRoles() ?: []);
        foreach($roles as $q=>$role) {
            $where .= 'a.securityRoles LIKE :role' . $q . ' OR ';
            $params['role' . $q] = $role;
        }
        $where = rtrim($where, ' OR') . ')';
        $params['route'] = '%' . $route . '%';
        $params['module'] = $module;
        $params['module'] = $module;

        try {
            return $this->createQueryBuilder('a')
                ->where('a.routeList LIKE :route')
                ->andWhere('a.module = :module')
                ->andWhere($where)
                ->setParameters($params)
                ->orderBy('a.precedence', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException | PDOException | \PDOException $e) {
            return null;
        }
    }

    /**
     * findOneByRoute
     * @param string $route
     * @return Action|null
     */
    public function findOneByRoute(string $route): ?Action
    {
        try {
            return $this->createQueryBuilder('a')
                ->where('(a.routeList LIKE :route OR a.entryRoute LIKE :route)')
                ->setParameter('route', '%' . $route . '%')
                ->orderBy('a.precedence', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * findPermissionPagination
     * @return mixed
     */
    public function findPermissionPagination()
    {
        return $this->createQueryBuilder('a')
            ->select(['a.name AS route','r.id as role','m.name AS moduleName', 'a.id', 'a.categoryPermissionStaff', 'a.categoryPermissionStudent', 'a.categoryPermissionParent', 'a.categoryPermissionOther'])
            ->leftJoin('a.roles', 'r')
            ->leftJoin('a.module', 'm')
            ->orderBy('m.name', 'ASC')
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findModuleNameList
     * @return array
     */
    public function findModuleNameList(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.module', 'm')
            ->groupBy('m.id')
            ->select(['m.name'])
            ->orderBy('m.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * findAllWithRolesAndModules
     * @return array
     */
    public function findAllWithRolesAndModules()
    {
        return $this->createQueryBuilder('a')
            ->select(['a','m', 'r'])
            ->leftJoin('a.roles', 'r')
            ->join('a.module', 'm')
            ->getQuery()
            ->getResult();
    }

    /**
     * findFastFinderActions
     * @return array
     */
    public function findFastFinderActions(): array
    {
        return $this->createQueryBuilder('a')
            ->select(['a','m'])
            ->join('a.module', 'm')
            ->where('m.active = :yes')
            ->andWhere('a.menuShow = :yes')
            ->orderBy('a.name', 'ASC')
            ->setParameters(['yes' => 'Y'])
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * findByModule
     * @param Module $module
     * @return int|mixed|string
     */
    public function findByModule(Module $module)
    {
        return $this->createQueryBuilder('a')
            ->join('a.module', 'm')
            ->select(['a','m'])
            ->orderBy('a.category', 'ASC')
            ->addOrderBy('a.name', 'ASC')
            ->addOrderBy('a.precedence', 'DESC')
            ->where('a.module = :module')
            ->setParameter('module', $module)
            ->getQuery()
            ->getResult();
    }

    /**
     * countRoleUse
     * @param SecurityRole $role
     * @return int
     * 29/06/2020 10:38
     */
    public function countRoleUse(SecurityRole $role): int
    {
        try {
            return intval($this->createQueryBuilder('a')
                ->select("COUNT(a.id)")
                ->where('a.securityRoles LIKE :role')
                ->setParameter('role', '%' . $role->getRole() . '%')
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * findLikeRoute
     * @param string $route
     * @return Action|null
     * 29/06/2020 11:42
     */
    public function findOneByLikeRoute(string $route): ?Action
    {
        try {
            return $this->createQueryBuilder('a')
                ->where('a.routeList LIKE :route')
                ->setParameter('route', '%' . $route . '%')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}


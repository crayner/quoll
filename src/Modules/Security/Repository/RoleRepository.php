<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 09:40
 */
namespace App\Modules\Security\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\Security\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class RoleRepository
 * @package App\Modules\Security\Repository
 */
class RoleRepository extends ServiceEntityRepository
{
    /**
     * RoleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * findUserRoles
     * @param Person|null $person
     * @return array
     */
    public function findUserRoles(?Person $person): array
    {
        if (empty($person))
            return [];
        $roles = explode(',',$person->getAllRoles());
        $result = $this->createQueryBuilder('r')
            ->where('r.id IN (:roles)')
            ->setParameter('roles', $roles, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
        return $result ?: [];
    }

    /**
     * getRoleList
     * @param $roleList
     * @param $connection2
     * @return array
     */
    public function getRoleList($roleList): array
    {
        $roles = is_array($roleList) ? $roleList : explode(',',$roleList);
        return $this->createQueryBuilder('r')
            ->where('r.id IN (:roles)')
            ->setParameter('roles', $roles, Connection::PARAM_INT_ARRAY)
            ->select(['r.id', 'r.name'])
            ->getQuery()
            ->getResult();
    }

    /**
     * findByRoleIDList
     * @param array $list
     * @param string $key
     * @return array
     */
    public function findByRoleIDList(array $list, string $key): array
    {
        return $this->createQueryBuilder('r', 'r.'.$key)
            ->where('r.id in (:list)')
            ->select(['r.id','r.'.$key])
            ->setParameter('list', $list, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findByRoleList
     * @param $list
     * @param $key
     * @return array
     */
    public function findByRoleList($list, $key): array
    {
        return $this->createQueryBuilder('r', 'r.id')
            ->where('r.' . $key . ' in (:list)')
            ->select(['r.id','r.' . $key])
            ->setParameter('list', $list, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findByCategory
     * @return array
     */
    public function findAllCategories(): array
    {
        return $this->createQueryBuilder('r', 'r.category')
            ->distinct(true)
            ->select('r.category')
            ->orderBy('r.category')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByCategory
     * @return array
     */
    public function selectRoleList(): array
    {
        $roles = [];
        foreach($this->createQueryBuilder('r')
            ->select(['r.id','r.name'])
            ->orderBy('r.name')
            ->getQuery()
            ->getResult() as $role){
            $roles[$role['name']]  = $role['id'];
        }
        return $roles;
    }

    /**
     * findPermissionTitles
     * @return array
     */
    public function findPermissionTitles(): array
    {
        return $this->createQueryBuilder('r')
            ->select(['r.id', 'r.nameShort', 'r.name'])
            ->orderBy('r.category', 'DESC')
            ->addOrderBy('r.nameShort', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private $roles = [];

    /**
     * findOneByName
     * @param string $name
     * @return Role
     */
    public function findOneByName(string $name): Role
    {
        if (! key_exists($name, $this->roles))
            $this->roles[$name] = $this->findOneBy(['name' => $name]);
        if (null === $this->roles[$name])
            dd($name, $this->roles);
        return $this->roles[$name];
    }
}

<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 25/04/2020
 * Time: 14:23
 */
namespace App\Modules\Security\Manager;

use App\Modules\Security\Entity\SecurityRole;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class RoleHierarchy
 * @package App\Modules\Security\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RoleHierarchy implements RoleHierarchyInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Role\RoleHierarchy
     */
    private $roleHierarchy;

    /**
     * @var array
     */
    private $hierarchy;

    /**
     * @var SecurityRole[]|ArrayCollection
     */
    private static $roles;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * RoleHierarchy constructor.
     * @param RoleHierarchyInterface $roleHierarchy
     * @param SessionInterface $session
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy, SessionInterface $session)
    {
        $this->roleHierarchy = $roleHierarchy;

        $this->session = $session;

        $this->hierarchy = $this->buildHierarchyRoles();

        $this->roleHierarchy->__construct($this->hierarchy);
    }

    /**
     * getAssignableRoleNames
     * @param array|string[] $roles
     * @return array|string[]
     */
    public function getAssignableRoleNames(array $roles)
    {
        $roles = $this->getReachableRoleNames($roles);

        return $roles;
    }

    /**
     * getReachableRoleNames
     * @param array $roles
     * @return array
     * 30/06/2020 11:02
     */
    public function getReachableRoleNames(array $roles): array
    {
        return $this->roleHierarchy->getReachableRoleNames($roles);
    }

    /**
     * getStaffRoles
     * @return array
     * 1/07/2020 08:24
     */
    public function getStaffRoles(): array
    {
        $result = [];
        foreach(ProviderFactory::getRepository(SecurityRole::class)->findByCategoryAsStrings('Staff') as $role) {
            $result[] = $role['role'];
        }
        return $result;
    }

    /**
     * getRoleNamesThatReach
     *
     * Grab roles that are parents of the roles supplied.
     * @param array|string[] $roles
     * @return array|string[]
     * 23/06/2020 14:29
     */
    public function getRoleNamesThatReach(array $roles): array
    {
        $result = [];

        foreach($roles as $role) {
            foreach($this->hierarchy as $name => $w) {
                if (in_array($role, $this->getReachableRoleNames([$name]))) {
                    $result[] = $name;
                }
            }
        }
        return array_unique($result);
    }

    /**
     * buildHierarchyRoles
     * @return array
     * 29/06/2020 14:44
     */
    private function buildHierarchyRoles(): array
    {
        $result = [];
        if ($this->session->has('role_hierarchy')  && is_array($this->session->get('role_hierarchy')) && !empty($this->session->get('role_hierarchy'))) {
            return $this->session->get('role_hierarchy');
        }
        $roles = self::getRoles()->count() > 0 ? self::getRoles() : ProviderFactory::getRepository(SecurityRole::class)->findAll();

        foreach($roles as $role) {
            $result[$role->getRole()] = [];
            foreach($role->getChildRoles() as $child) {
                $result[$role->getRole()][] = $child->getRole();
            }
            self::addRole($role);
        }

        $this->session->set('role_hierarchy', $result);
        return $result;
    }

    /**
     * getRoles
     * @return ArrayCollection
     * 29/06/2020 14:40
     */
    private static function getRoles(): ArrayCollection
    {
        if (self::$roles === null) {
            self::$roles = new ArrayCollection();
        }
        return self::$roles;
    }

    /**
     * setRoles
     * @param array $roles
     * 29/06/2020 14:42
     */
    private static function setRoles(array $roles): void
    {
        self::$roles = $roles;
    }

    /**
     * addRole
     * @param $role
     * 29/06/2020 14:42
     */
    private static function addRole($role)
    {
        self::getRoles()->set($role->getRole(), $role);
    }

    /**
     * getCategory
     * @param $role
     * @return string
     * 29/06/2020 14:37
     */
    public static function getCategory($role): string
    {
        if (is_string($role)) {
            $role = self::getRoles()->get($role);
        }

        return $role->getCategory();
    }
}
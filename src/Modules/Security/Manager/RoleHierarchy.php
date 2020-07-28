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
use App\Util\ParameterBagHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Yaml\Yaml;

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
     * @var string[]
     */
    private static $categoryList = [
        'Staff',
        'Student',
        'Care Giver',
        'Contact',
        'System',
    ];

    /**
     * RoleHierarchy constructor.
     * @param RoleHierarchyInterface $roleHierarchy
     * @param array $hierarchy
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy, array $hierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;

        $this->hierarchy = $hierarchy;
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
     * getCategory
     * @param $role
     * @return string
     * 29/06/2020 14:37
     */
    public static function getCategory(string $role): string
    {
        if (is_string($role)) {
            $role = ProviderFactory::getRepository(SecurityRole::class)->findOneByRole($role);
        }

        return $role ? $role->getCategory() : 'Contact';
    }

    /**
     * @return string[]
     */
    public static function getCategoryList(): array
    {
        return self::$categoryList;
    }
}

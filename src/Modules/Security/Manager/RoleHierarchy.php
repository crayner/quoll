<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
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

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class RoleHierachy
 * @package App\Modules\Security\Manager
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
        if (($key = array_search('ROLE_CURRENT_USER', $roles)) !== false)
            unset($roles[$key]);
        if (($key = array_search('ROLE_FUTURE_YEARS', $roles)) !== false)
            unset($roles[$key]);
        if (($key = array_search('ROLE_PAST_YEARS', $roles)) !== false)
            unset($roles[$key]);
        if (($key = array_search('ROLE_ALLOWED_TO_SWITCH', $roles)) !== false)
            unset($roles[$key]);

        return $roles;
    }

    /**
     * getReachableRoleNames
     * @param array $roles
     * @return array
     * 11/06/2020 10:29
     */
    public function getReachableRoleNames(array $roles): array
    {
        return $this->roleHierarchy->getReachableRoleNames($roles);
    }

    /**
     * getStaffRoles
     * @return array
     * 11/06/2020 10:29
     */
    public function getStaffRoles(): array
    {
        $result = [];
        foreach($this->hierarchy as $name => $w)
            if (in_array('ROLE_STAFF', $this->getReachableRoleNames([$name])))
                $result[] = $name;
        array_unique($result);
        return $result;
    }
}
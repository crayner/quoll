<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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
    private $hierarchy;

    /**
     * RoleHierarchy constructor.
     * @param RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->hierarchy = $roleHierarchy;
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
     * @param array|string[] $roles
     * @return array|string[]
     */
    public function getReachableRoleNames(array $roles): array
    {
        return $this->hierarchy->getReachableRoleNames($roles);
    }
}
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
 * Date: 29/07/2020
 * Time: 08:46
 */
namespace App\Modules\Security\Manager;
use App\Modules\Security\Entity\SecurityRole;
use App\Util\ParameterBagHelper;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SecurityManager
 * @package App\Modules\Security\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityManager
{
    /**
     * @var string
     */
    private $hierarchyPath;

    /**
     * SecurityManager constructor.
     */
    public function __construct()
    {
        $this->hierarchyPath = realpath(__DIR__ . '/../../../../config/packages/role_hierarchy.yaml');
    }

    /**
     * updateSecurityRoleHierarchy
     * 29/07/2020 08:56
     * @param SecurityRole $role
     */
    public function updateSecurityRoleHierarchy(SecurityRole $role)
    {
        $roles = $this->readHierarchyFile();
        $roles[$role->getRole()] = [];
        foreach($role->getChildRoles() as $child)
        {
            $roles[$role->getRole()][] = $child->getRole();
        }
        $this->writeHierarchyFile($roles);
    }

    /**
     * readHierarchyFile
     * @return array
     * 29/07/2020 08:54
     */
    private function readHierarchyFile(): array
    {
        $result = Yaml::parse(file_get_contents($this->hierarchyPath));
        return $result['parameters']['security.hierarchy.roles'];
    }

    /**
     * writeHierarchyFile
     * @param array $hierarchy
     * 29/07/2020 08:57
     */
    private function writeHierarchyFile(array $hierarchy) 
    {
        $result['parameters']['security.hierarchy.roles'] = $hierarchy;
        file_put_contents($this->hierarchyPath, Yaml::dump($result, 8));
    }
}

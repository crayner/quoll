<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/07/2019
 * Time: 16:01
 */

namespace App\Modules\System\Provider;

use App\Provider\EntityProviderInterface;
use App\Manager\Traits\EntityTrait;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Role;

/**
 * Class ActionProvider
 * @package App\Modules\System\Provider
 */
class ActionProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Action::class;

    /**
     * findByURLListModuleRole
     * @param array $criteria
     * @return mixed
     */
    public function findByURLListModuleRole(array $criteria)
    {
        return $this->getRepository()->findByURLListModuleRole($criteria);
    }

    /**
     * findPaginationContent
     * @return array
     */
    public function findPaginationContent()
    {
        $result = [];
        $roles = [];
        foreach($this->getRepository(Role::class)->findBy([], ['category' => 'DESC', 'nameShort' => 'ASC']) as $role)
        {
            $w = [];
            $w['id'] = $role->getId();
            $w['category'] = $role->getCategory();
            $w['nameShort'] = $role->getNameShort();
            $w['readOnly'] = false;
            $w['checked'] = false;
            $roles[$w['id']] = $w;
        }

        foreach($this->getRepository()->findPermissionPagination() as $item)
        {
            $id = intval($item['id']);
            if (!key_exists($id, $result)) {
                $result[$id] = $item;
                $result[$id]['roles'] = $roles;
                foreach($result[$id]['roles'] as $q=>$w)
                {
                    $category = $w['category'];
                    $result[$id]['roles'][$q]['readOnly'] = $item['categoryPermission' . $category] === 'N';
                }
            }
            $roleId = intval($item['role']);
            if ($roleId > 0)
                $result[$id]['roles'][$roleId]['checked'] = true;
        }
        return $result;
    }
}
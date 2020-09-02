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

use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Modules\System\Entity\PageDefinition;
use App\Provider\AbstractProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ActionProvider
 * @package App\Modules\System\Provider
 */
class ActionProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Action::class;

    /**
     * findByrouteListModuleRole
     * @param array $criteria
     * @return mixed
     */
    public function findByRouteListModuleRole(array $criteria)
    {
        return $this->getRepository()->findByrouteListModuleRole($criteria);
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

    public function buildMenu()
    {
        return $this->getRepository()->findAllWithRolesAndModules();
    }

    /**
     * findFastFinderActions
     * @return array
     * 30/06/2020 11:11
     */
    public function findFastFinderActions(): array
    {
        $result = $this->getRepository()->findFastFinderActions();
        $answer = [];
        foreach($result as $action)
        {
            if (SecurityHelper::isGranted($action->getSecurityRoles())) {
                $act = [];
                $act['id'] = 'Act-' . $action->getName() . '/' . $action->getEntryRoute();
                $name = explode('_', $action->getName());
                $act['text'] = $name[0];
                $act['search'] = $action->getModule()->getName();
                $answer[] = $act;
            }
        }

        uasort($answer, function($a,$b) {
            if ($a['text'] === $b['text'])
                return 0;
            return $a['text'] < $b['text'] ? -1 : 1;
        });

        return $answer;
    }

    /**
     * moduleMenuItems
     *
     * 2/09/2020 17:10
     * @param PageDefinition $definition
     * @return array
     */
    public function moduleMenuItems(PageDefinition $definition): array
    {
        $result = $definition->getModule()->getActions(true);
        $categories = [];
        $precedence = [];
        foreach($result as $action)
        {
            if ($action->isEntrySidebar() && (SecurityHelper::isGranted($action->getSecurityRoles()) || [] === $action->getSecurityRoles())) {
                if ((key_exists($action->getDisplayName(), $precedence)
                        && $action->getPrecedence() > $precedence[$action->getDisplayName()])
                        || !key_exists($action->getDisplayName(), $precedence)) {
                    $categories[$action->getCategory()][$action->getDisplayName()] = $this->toActionArray($definition->getModule(), $action);
                    $precedence[$action->getDisplayName()] = $action->getPrecedence();
                }
            }
        }
        return $categories;
    }

    /**
     * toActionArray
     *
     * 3/09/2020 08:02
     * @param Module $module
     * @param Action $action
     * @return array
     */
    private function toActionArray(Module $module, Action $action)
    {
        return [
            'category' => $action->getCategory(),
            'moduleName' => $module->getName(),
            'actionName' => $action->getFullName(),
            'type' => $module->getType(),
            'precedence' => $action->getPrecedence(),
            'moduleEntry' => $module->getEntryRoute(),
            'entryRoute' => $action->getentryRoute(),
            'routeList' => $action->getRouteList(),
            'name' => $action->getDisplayName(),
        ];

    }
}

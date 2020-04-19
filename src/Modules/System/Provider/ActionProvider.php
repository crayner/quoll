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

use App\Manager\Traits\EntityTrait;
use App\Modules\Security\Entity\Role;
use App\Modules\System\Entity\Action;
use App\Provider\EntityProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * findByrouteListModuleRole
     * @param array $criteria
     * @return mixed
     */
    public function findByrouteListModuleRole(array $criteria)
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
     * @param AuthorizationCheckerInterface $checker
     * @return mixed
     */
    public function findFastFinderActions(AuthorizationCheckerInterface $checker): array
    {
        $result = $this->getRepository()->findFastFinderActions();
        $answer = [];
        foreach($result as $action)
        {
            if ($checker->isGranted($action->getRole())) {
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

         /*   ->select([
                "CONCAT('Act-', m.name, '/', a.entryRoute) AS id",
                "CONCAT('" . $actionTitle . "', SUBSTRING_INDEX(a.name, '_', 1)) AS text",
                'm.name as search'
            ])
            ->join('a.module', 'm')
//           ->leftJoin('a.roles', 'r')
            ->where('m.active = :yes')
            ->andWhere('a.menuShow = :yes')
//            ->andWhere('r.id = :role')
            ->orderBy('text', 'ASC')
            ->setParameter('yes', 'Y')
//            ->setParameters(['yes' => 'Y', 'role' => intval($role->getId())])
            ->distinct()
            ->getQuery()
            ->getResult(); */
    }
}
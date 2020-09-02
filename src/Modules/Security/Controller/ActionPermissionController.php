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
 * Date: 30/06/2020
 * Time: 08:43
 */
namespace App\Modules\Security\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Form\ActionPermissionType;
use App\Modules\Security\Pagination\ActionPermissionPagination;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ActionPermissionController
 * @package App\Modules\Security\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActionPermissionController extends AbstractPageController
{
    /**
     * list
     *
     * 19/08/2020 10:47
     * @param ActionPermissionPagination $pagination
     * @Route("/action/permissions/list/",name="action_permission_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(ActionPermissionPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(Action::class)->findBy([],['name' => 'ASC']), 'actionPermissions');

        return $this->getPageManager()
            ->createBreadcrumbs('Action Permissions')
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                ]
            );
    }

    /**
     * edit
     *
     * 19/08/2020 10:44
     * @param Action $item
     * @Route("/action/{item}/permission/edit/", name="action_permission_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(Action $item)
    {
        $form = $this->createForm(ActionPermissionType::class, $item, ['action' => $this->generateUrl('action_permission_edit', ['item' => $item->getId()])]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(Action::class)->persistFlush($item);
                $form = $this->createForm(ActionPermissionType::class, $item, ['action' => $this->generateUrl('action_permission_edit', ['item' => $item->getId()])]);
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            return $this->generateJsonResponse(['form' => $this->getContainerManager()
                ->singlePanel($form->createView())
                ->getFormFromContainer()]);
        }

        return $this->getPageManager()
            ->createBreadcrumbs('Edit Action Permission',
                [
                    [
                        'uri' => 'action_permission_list',
                        'name' => 'Action Permissions'
                    ]
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('action_permission_list'))
                        ->setTranslationDomain('Security')
                        ->singlePanel($form->createView())
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * writeSecurityLinks
     *
     * 19/08/2020 10:47
     * @return string
     */
    public static function writeSecurityLinks(): string
    {
           
        $links = [];
        $data = [];
        foreach(ProviderFactory::getRepository(SecurityRole::class)->findAll() as $securityRole) {
            $entity = $securityRole->toArray('buildContent');
            $data[] = $entity;

            foreach($securityRole->getChildRoles() as $role) {
                $item = [];
                $item['findBy']['role'] = $securityRole->getRole();
                $item['source']['table'] = SecurityRole::class;
                $item['source']['findBy']['role'] = $role->getRole();
                $item['target'] = 'childRole';
            }
            $links[] = $item;
        }

        file_put_contents(__DIR__ . '/../../Security/Entity/SecurityRoleCoreData.yaml', Yaml::dump($data, 5));
        file_put_contents(__DIR__ . '/../../Security/Entity/SecurityRoleCoreLinks.yaml', Yaml::dump($links, 5));

        $data = [];
        foreach(ProviderFactory::getRepository(Action::class)->findAll() as $action) {
            $entity = $action->toArray('buildContent');
            $data[] = $entity;
        }

        file_put_contents(__DIR__ . '/../../System/Entity/ActionCoreData.yaml', Yaml::dump($data, 5));

        $data = [];
        $links = [];
        foreach(ProviderFactory::getRepository(Module::class)->findAll() as $module) {
            $entity = $module->toArray('buildContent');
            $data[] = $entity;
            foreach ($module->getActions() as $action) {
                $item = [];
                $item['findBy']['entryRoute'] = $module->getEntryRoute();
                $item['source']['table'] = Action::class;
                $item['source']['findBy']['entryRoute'] = $action->getEntryRoute();
                $item['source']['findBy']['precedence'] = $action->getPrecedence();
                $item['target'] = 'action';
                $links[] = $item;
            }
        }

        file_put_contents(__DIR__ . '/../../System/Entity/ModuleCoreData.yaml', Yaml::dump($data, 5));
        file_put_contents(__DIR__ . '/../../System/Entity/ModuleCoreLinks.yaml', Yaml::dump($links, 5));

        return '<li>Action, Modules and Security Role Data and Links</li>';
    }
}

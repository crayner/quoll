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
     * actionPermisionList
     * @param ActionPermissionPagination $pagination
     * @return JsonResponse
     * @Route("/action/permissions/list/",name="action_permission_list")
     * @IsGranted("ROLE_ROUTE")
     * 30/06/2020 08:47
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
     * @param Action $item
     * @param ContainerManager $manager
     * @Route("/action/{item}/permission/edit/", name="action_permission_edit")
     * @IsGranted("ROLE_ROUTE")
     * 30/06/2020 09:45
     */
    public function edit(Action $item, ContainerManager $manager)
    {
        $form = $this->createForm(ActionPermissionType::class, $item, ['action' => $this->generateUrl('action_permission_edit', ['item' => $item->getId()])]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $data = ProviderFactory::create(Action::class)->persistFlush($item, []);
                $form = $this->createForm(ActionPermissionType::class, $item, ['action' => $this->generateUrl('action_permission_edit', ['item' => $item->getId()])]);
            } else {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $manager
            ->setReturnRoute($this->generateUrl('action_permission_list'))
            ->setTranslationDomain('Security')
            ->singlePanel($form->createView());

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
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }

    /**
     * writeSecurityLinks
     * 1/07/2020 09:59
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
                $item['source']['findBy']['name'] = $role;
                $item['target'] = 'childRole';
            }
            $links[] = $item;
        }

        file_put_contents(__DIR__ . '/../../Security/Entity/SecurityRoleCoreData.yaml', Yaml::dump($data, 3));
        file_put_contents(__DIR__ . '/../../Security/Entity/SecurityRoleCoreLinks.yaml', Yaml::dump($links, 3));

        $links = [];
        $data = [];
        foreach(ProviderFactory::getRepository(Action::class)->findAll() as $action) {
            $entity = $action->toArray('buildContent');
            $data[] = $entity;
            $item = [];
            $item['findBy']['entryRoute'] = $action->getentryRoute();
            $item['source']['table'] = Module::class;
            $item['source']['findBy']['name'] = $action->getModule()->getName();
            $item['target'] = 'module';
            $links[] = $item;
        }

        file_put_contents(__DIR__ . '/../../System/Entity/ActionCoreData.yaml', Yaml::dump($data, 3));
        file_put_contents(__DIR__ . '/../../System/Entity/ActionCoreLinks.yaml', Yaml::dump($links, 3));

        return '<li>Action and Security Role Data and Links</li>';
    }
}

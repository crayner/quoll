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
 * Date: 29/06/2020
 * Time: 10:10
 */
namespace App\Modules\Security\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Form\SecurityRoleType;
use App\Modules\Security\Pagination\SecurityRolePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityRoleController
 * @package App\Modules\Security\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityRoleController extends AbstractPageController
{
    /**
     * list
     * @param SecurityRolePagination $pagination
     * @return JsonResponse
     * @Route("/security/roles/list/",name="security_role_list")
     * @Route("/security/role/{role}/delete/",name="security_role_delete")
     * @IsGranted("ROLE_ROUTE")
     * 29/06/2020 10:14
     */
    public function list(SecurityRolePagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(SecurityRole::class)->findBy([],['category' => 'ASC', 'role' => 'ASC']))
            ->setAddElementRoute($this->generateUrl('security_role_add'));

        return $this->getPageManager()
            ->createBreadcrumbs('Security Roles')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param SecurityRole|null $role
     * @return JsonResponse
     * @Route("/security/role/add/",name="security_role_add")
     * @Route("/security/role/{role}/edit/",name="security_role_edit")
     * @IsGranted("ROLE_ROUTE")
     * 29/06/2020 12:21
     */
    public function edit(ContainerManager $manager, ?SecurityRole $role = null)
    {
        if ($role === null) {
            $role = new SecurityRole();
            $action = $this->generateUrl('security_role_add');
        } else {
            $action = $this->generateUrl('security_role_edit', ['role' => $role->getId()]);
        }

        $form = $this->createForm(SecurityRoleType::class, $role, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $data = ProviderFactory::create(SecurityRole::class)->persistFlush($role, []);
                if ($data['status'] === 'success' && $this->getRequest()->get('_route') === 'security_role_add') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('security_role_edit', ['role' => $role->getId()]);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                } elseif ($data['status'] === 'success') {
                    $form = $this->createForm(SecurityRoleType::class, $role, ['action' => $action]);
                } else {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $manager->setReturnRoute($this->generateUrl('security_role_list'))
            ->singlePanel($form->createView());

        if ($role->getId()) {
            $manager->setAddElementRoute($this->generateUrl('security_role_add'));
        }

        return $this->getPageManager()
            ->createBreadcrumbs($role->getId() ? 'Edit Security Role' : 'Add Security Role')
            ->render(['containers' => $manager->getBuiltContainers()]);

    }
}
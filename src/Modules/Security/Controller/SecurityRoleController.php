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

use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Form\SecurityRoleType;
use App\Modules\Security\Manager\SecurityManager;
use App\Modules\Security\Pagination\SecurityRolePagination;
use App\Provider\ProviderFactory;
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
     *
     * 19/08/2020 09:36
     * @param SecurityRolePagination $pagination
     * @Route("/security/roles/list/",name="security_role_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(SecurityRolePagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(SecurityRole::class)->findBy([],['category' => 'ASC', 'role' => 'ASC']))
            ->setAddElementRoute($this->generateUrl('security_role_add'));

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('security_role_list'))
            ->createBreadcrumbs('Security Roles')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 19/08/2020 09:37
     * @param SecurityManager $security
     * @param SecurityRole|null $role
     * @Route("/security/role/add/",name="security_role_add")
     * @Route("/security/role/{role}/edit/",name="security_role_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(SecurityManager $security, ?SecurityRole $role = null)
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
                ProviderFactory::create(SecurityRole::class)->persistFlush($role);
                if ($this->isStatusSuccess() && $this->getRequest()->get('_route') === 'security_role_add') {
                    $security->updateSecurityRoleHierarchy($role);
                    $this->getStatusManager()
                        ->setReDirect($this->generateUrl('security_role_edit', ['role' => $role->getId()]))
                        ->convertToFlash();
                } elseif ($this->isStatusSuccess()) {
                    $security->updateSecurityRoleHierarchy($role);
                    $form = $this->createForm(SecurityRoleType::class, $role, ['action' => $action]);
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getFormFromContainer()
                ]
            );
        }

        if ($role->getId()) {
            $this->getContainerManager()
                ->setAddElementRoute($this->generateUrl('security_role_add'));
        }

        return $this->getPageManager()
            ->createBreadcrumbs($role->getId() ? 'Edit Security Role' : 'Add Security Role')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('security_role_list'))
                        ->singlePanel($form->createView())
                        ->getBuiltContainers()
                ]
            );
    }

    /**
     * delete
     *
     * 19/08/2020 09:48
     * @param SecurityManager $security
     * @param SecurityRole $role
     * @param SecurityRolePagination $pagination
     * @return JsonResponse
     * @Route("/security/role/{role}/delete/",name="security_role_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function delete(SecurityManager $security, SecurityRole $role, SecurityRolePagination $pagination)
    {
        ProviderFactory::create(SecurityRole::class)->delete($role);
        if ($this->isStatusSuccess()) $security->removeRole($role);

        return $this->list($pagination);
    }
}
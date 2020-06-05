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
 * Date: 4/06/2020
 * Time: 13:15
 */
namespace App\Modules\Department\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\Department\Entity\Department;
use App\Modules\Department\Form\DepartmentSettingType;
use App\Modules\Department\Form\DepartmentStaffType;
use App\Modules\Department\Form\DepartmentType;
use App\Modules\Department\Pagination\DepartmentPagination;
use App\Modules\Department\Pagination\DepartmentStaffPagination;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Staff\Entity\DepartmentStaff;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use App\Twig\SidebarContent;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ManageController
 * @package App\Modules\Department\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ManageController extends AbstractPageController
{
    /**
     * manage
     * @param ContainerManager $manager
     * @param DepartmentPagination $pagination
     * @param SidebarContent $sidebar
     * @param array $messages
     * @param string $tabName
     * @return JsonResponse|Response
     * @Route("/department/list/{tabName}",name="department_list")
     * @Route("/department/list/{tabName}",name="department_view")
     * @Route("/department/list/{tabName}",name="department_manage")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 13:19
     */
    public function list(ContainerManager $manager, DepartmentPagination $pagination, SidebarContent $sidebar, array $messages = [], string $tabName = 'Settings')
    {
        $form = $this->createForm(DepartmentSettingType::class, null, ['action' => $this->generateUrl('department_list')]);
        ProviderFactory::create(CourseClass::class)->getMyClasses($this->getUser(), $sidebar);

        if ($this->getRequest()->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data['errors'] = ProviderFactory::create(Setting::class)->handleSettingsForm($form, $this->getRequest());
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }

            if ($data['status'] === 'success')
                $form = $this->createForm(DepartmentSettingType::class, null, ['action' => $this->generateUrl('department_list')]);

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $container = new Container();
        $panel = new Panel('Settings', 'Department');
        $container->addForm('Settings', $form->createView())->addPanel($panel);
        $panel = new Panel('List', 'Department');
        $content = ProviderFactory::getRepository(Department::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)->setAddElementRoute($this->generateUrl('department_add'));
        $panel->setPagination($pagination);
        $container->addPanel($panel)->setSelectedPanel($tabName);
        $manager->addContainer($container);

        return $this->getPageManager()
            ->setMessages(isset($messages['errors']) ? $messages['errors'] : [])
            ->createBreadcrumbs('Departments')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Department|null $department
     * @param string|null $tabName
     * @return JsonResponse|Response
     * @Route("/department/{department}/edit/{tabName}", name="department_edit")
     * @Route("/department/add/{tabName}", name="department_add")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 15:00
     */
    public function edit(ContainerManager $manager, ?Department $department = null, ?string $tabName = 'General')
    {
        if (!$department instanceof Department) {
            $department = new Department();
            $action = $this->generateUrl('department_add', ['tabName' => $tabName]);
        } else {
            $action = $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => $tabName]);
        }


        $form = $this->createForm(DepartmentType::class, $department, ['action' => $action]);

        $staffForm = $this->createForm(DepartmentStaffType::class, $department, ['action' => $action]);

        $container = new Container();
        $container->setTarget('formContent')->setSelectedPanel($tabName);
        TranslationHelper::setDomain('Department');

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $data = [];
            $data['status'] = 'success';
            if ($content['formName'] === 'General Form') {
                $form->submit($content);
                if ($form->isValid()) {
                    $id = $department->getId();
                    $provider = ProviderFactory::create(Department::class);
                    $data = $provider->persistFlush($department, $data);
                    if ($data['status'] === 'success') {
                        $form = $this->createForm(DepartmentType::class, $department,
                            ['action' => $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => 'General'])]
                        );
                        if ($id !== $department->getId()) {
                            ErrorMessageHelper::convertToFlash($data, $this->getRequest()->getSession()->getBag('flashes'));
                            $data['status'] = 'redirect';
                            $data['redirect'] = $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => 'General']);
                        }
                    }
                } else {
                    $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
                }

                $manager->singlePanel($form->createView());
            } elseif ($content['formName'] === 'Staff Form') {
                $manager->singlePanel($staffForm->createView());
                $data = ProviderFactory::create(DepartmentStaff::class)->writeDepartmentStaff($department, $content['newStaff'], $content['role'], $data);
                if ($data['status'] === 'success')
                {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => 'Staff']);
                }
                $manager->singlePanel($staffForm->createView());

            }
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $panel = new Panel('General', 'Department');
        $container->addForm('General', $form->createView())->addPanel($panel);

        if ($department->getId() > 0) {
            $panel = new Panel('Staff', 'Department');
            $panel->setPreContent(['currentStaffHeader', 'staffPaginationContent']);
            $container->addForm('Staff', $staffForm->createView())->addPanel($panel);
            $container->addPanel($panel)->setContentLoader([
                [
                    'route' => $this->generateUrl('department_content_loader', ['department' => $department->getId()]),
                    'target' => 'staffPaginationContent',
                    'type' => 'pagination',
                ],
                [
                    'route' => $this->generateUrl('department_current_staff_header'),
                    'target' => 'currentStaffHeader',
                    'type' => 'text',
                ],
            ]);
        }

        $manager->addContainer($container)->setReturnRoute($this->generateUrl('department_list', ['tabName' => 'List']));
        $pageHeader = new PageHeader(TranslationHelper::translate($department->getId() === null ? 'Add Department' : 'Edit Department'));
        if ($department->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('department_add'));
        }

        return $this->getPageManager()->setPageHeader($pageHeader)
            ->createBreadcrumbs($department->getId() === null ? 'Add Department' : 'Edit Department')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @param ContainerManager $manager
     * @param Department $department
     * @param DepartmentPagination $pagination
     * @param SidebarContent $sidebarContent
     * @return JsonResponse|Response
     * @Route("/department/{department}/delete/",name="department_delete")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 15:59
     */
    public function delete(ContainerManager $manager, Department $department, DepartmentPagination $pagination, SidebarContent $sidebarContent)
    {
        $provider = ProviderFactory::create(Department::class);

        $provider->delete($department);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($manager, $pagination, $sidebarContent, $data, 'List');
    }

    /**
     * manageContent
     * @param DepartmentStaffPagination $pagination
     * @param Department|null $department
     * @return JsonResponse
     * @Route("/department/{department}/content/loader/", name="department_content_loader")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 16:20
     */
    public function manageContent(DepartmentStaffPagination $pagination, ?Department $department)
    {
        try {
            $content = ProviderFactory::getRepository(DepartmentStaff::class)->findStaffByDepartment($department);
            $pagination->setContent($content);
            return new JsonResponse(['content' => $pagination->toArray(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * currentStaffHeader
     * @return JsonResponse
     * @Route("/department/current/staff/header/", name="department_current_staff_header")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 16:20
     */
    public function currentStaffHeader()
    {
        try {
            return new JsonResponse(['content' => $this->renderView('department/current_staff_header.html.twig'), 'status' => 'success']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
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
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use App\Twig\SidebarContent;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function list(
        ContainerManager $manager,
        DepartmentPagination $pagination,
        SidebarContent $sidebar,
        array $messages = [],
        string $tabName = 'Settings')
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
        $pagination->setContent($content)->setAddElementRoute($this->generateUrl('department_add'), 'Add Department');
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
     * @param DepartmentStaffPagination $pagination
     * @param Department|null $department
     * @param string|null $tabName
     * @return JsonResponse|Response
     * @Route("/department/{department}/edit/{tabName}", name="department_edit")
     * @Route("/department/add/{tabName}", name="department_add")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 15:00
     */
    public function edit(ContainerManager $manager, DepartmentStaffPagination $pagination, ?Department $department = null, ?string $tabName = 'General', array $data = [])
    {
        if (!$department instanceof Department) {
            $department = new Department();
            $action = $this->generateUrl('department_add', ['tabName' => $tabName]);
        } else {
            $action = $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => $tabName]);
        }


        $form = $this->createForm(DepartmentType::class, $department, ['action' => $action]);

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
            }
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $panel = new Panel('General', 'Department');
        $container->addForm('General', $form->createView())->addPanel($panel);

        if ($department->getId() !== null) {
            $panel = new Panel('Staff', 'Department');
            $content = ProviderFactory::getRepository(DepartmentStaff::class)->findStaffByDepartment($department);
            $pagination->setContent($content)
                ->setPreContent($this->renderView('department/current_staff_header.html.twig'))
                ->setRefreshRoute($this->generateUrl('department_edit', ['tabName' => 'Staff', 'department' => $department->getId()]),'Refresh Department Staff')
                ->setAddElementRoute(['url' => $this->generateUrl('department_staff_add_popup', ['department' => $department->getId()]), 'target' => 'Department_Staff', 'options' => 'width=600,height=350'], 'Add Department Staff');
            $panel->setPagination($pagination);
            $container->addPanel($panel);
        }

        $manager->addContainer($container)
            ->setReturnRoute($this->generateUrl('department_list', ['tabName' => 'List']), 'Return to Departments');
        $pageHeader = new PageHeader(TranslationHelper::translate($department->getId() === null ? 'Add Department' : 'Edit Department'));
        if ($department->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('department_add'), 'Add Department');
        }

        return $this->getPageManager()
            ->setMessages(isset($data['errors']) ? $data['errors'] : [])
            ->setPageHeader($pageHeader)
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
     * deleteStaff
     * @param DepartmentStaff $staff
     * @param ContainerManager $manager
     * @param DepartmentStaffPagination $pagination
     * @return JsonResponse|Response
     * @Route("/department/staff/{staff}/delete/", name="department_staff_delete")
     * @IsGranted("ROLE_ROUTE")
     * 6/06/2020 08:22
     */
    public function deleteStaff(DepartmentStaff $staff, ContainerManager $manager, DepartmentStaffPagination $pagination)
    {
        $provider = ProviderFactory::create(DepartmentStaff::class);

        $provider->delete($staff);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->edit($manager,$pagination,$staff->getDepartment(),'Staff', $data);
    }

    /**
     * addDepartmentStaff
     * @param ContainerManager $manager
     * @param Department $department
     * @param DepartmentStaff|null $staff
     * @return JsonResponse
     * @Route("/department/{department}/staff/{staff}/edit/",name="department_staff_edit_popup")
     * @Route("/department/{department}/staff/add/",name="department_staff_add_popup")
     * @IsGranted("ROLE_ROUTE")
     * 6/06/2020 08:12
     */
    public function addDepartmentStaff(ContainerManager $manager, Department $department, ?DepartmentStaff $staff = null)
    {
        dump($staff, $this->getRequest()->get('_route'));
        if (null === $staff || $this->getRequest()->get('_route') === 'department_staff_add_popup') {
            $staff = new DepartmentStaff();
            $action = $this->generateUrl('department_staff_add_popup', ['department' => $department->getId()]);
        } else {
            $action = $this->generateUrl('department_staff_edit_popup', ['department' => $department->getId(), 'staff' => $staff->getId()]);
        }
dump($staff);
        $form = $this->createForm(DepartmentStaffType::class, $staff, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            if ($this->isCsrfTokenValid($form->getName(), $content['_token'])) {
                $data = ProviderFactory::create(DepartmentStaff::class)->writeDepartmentStaff($department, $content['person'], $content['role'], [], $form);
            } else {
                $data = ErrorMessageHelper::getInvalidTokenMessage([],true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}
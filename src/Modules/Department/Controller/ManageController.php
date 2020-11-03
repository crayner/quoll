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
 * Date: 4/06/2020
 * Time: 13:15
 */
namespace App\Modules\Department\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Department\Entity\Department;
use App\Modules\Department\Form\DepartmentSettingType;
use App\Modules\Department\Form\DepartmentStaffType;
use App\Modules\Department\Form\DepartmentType;
use App\Modules\Department\Pagination\DepartmentPagination;
use App\Modules\Department\Pagination\DepartmentStaffPagination;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use App\Twig\SidebarContent;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ManageController
 * @package App\Modules\Department\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ManageController extends AbstractPageController
{
    /**
     * list
     *
     * 17/08/2020 14:27
     * @param DepartmentPagination $pagination
     * @param SidebarContent $sidebar
     * @param string $tabName
     * @Route("/department/list/{tabName}",name="department_list")
     * @Route("/department/list/{tabName}",name="department_view")
     * @Route("/department/list/{tabName}",name="department_manage")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(
        DepartmentPagination $pagination,
        SidebarContent $sidebar,
        string $tabName = 'List')
    {
        $form = $this->createForm(DepartmentSettingType::class, null, ['action' => $this->generateUrl('department_list')]);
        ProviderFactory::create(CourseClass::class)->getMyClasses($this->getUser(), $sidebar);
        $manager = $this->getContainerManager();

        if ($this->isPostContent()) {
            try {
                SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            } catch (\Exception $e) {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(DepartmentSettingType::class, null, ['action' => $this->generateUrl('department_list')]);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $container = new Container($tabName);
        $panel = new Panel('Settings', 'Department', new Section('form','Settings'));
        $container->addForm('Settings', $form->createView())
            ->addPanel($panel);
        $content = ProviderFactory::getRepository(Department::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('department_add'), 'Add Department');
        $panel = new Panel('List', 'Department', new Section('pagination', $pagination));
        $container->addPanel($panel);
        $manager->addContainer($container);

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->createBreadcrumbs('Departments')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * edit
     *
     * 17/08/2020 14:24
     * @param DepartmentStaffPagination $pagination
     * @param Department|null $department
     * @param string|null $tabName
     * @Route("/department/{department}/edit/{tabName}", name="department_edit")
     * @Route("/department/add/{tabName}", name="department_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(
        DepartmentStaffPagination $pagination,
        ?Department $department = null,
        ?string $tabName = 'General'
    ) {
        if (!$department instanceof Department) {
            $department = new Department();
            $action = $this->generateUrl('department_add', ['tabName' => $tabName]);
        } else {
            $action = $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => $tabName]);
        }

        $manager = $this->getContainerManager();
        $form = $this->createForm(DepartmentType::class, $department, ['action' => $action]);

        $container = new Container();
        $container->setTarget('formContent')->setSelectedPanel($tabName);
        TranslationHelper::setDomain('Department');

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            if ($content['formName'] === 'General Form') {
                $form->submit($content);
                if ($form->isValid()) {
                    $id = $department->getId();
                    ProviderFactory::create(Department::class)
                        ->persistFlush($department);
                    if ($this->getStatusManager()->isStatusSuccess()) {
                        $form = $this->createForm(DepartmentType::class, $department,
                            ['action' => $this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => 'General'])]
                        );
                        if ($id !== $department->getId()) {
                            $this->getStatusManager()
                                ->setReDirect($this->generateUrl('department_edit', ['department' => $department->getId(), 'tabName' => 'General']))
                                ->convertToFlash();
                        }
                    }
                } else {
                    $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
                }

                $manager->singlePanel($form->createView());
            }

            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer() ]);
        }

        $panel = new Panel('General', 'Department', new Section('form', 'General'));
        $container->addForm('General', $form->createView())->addPanel($panel);

        if ($department->getId() !== null) {
            $content = ProviderFactory::getRepository(DepartmentStaff::class)->findStaffByDepartment($department);
            $pagination->setContent($content)
                ->setPreContent($this->renderView('department/current_staff_header.html.twig'))
                ->setRefreshRoute($this->generateUrl('department_edit', ['tabName' => 'Staff', 'department' => $department->getId()]), 'Refresh Department Staff')
                ->setAddElementRoute(['url' => $this->generateUrl('department_staff_add_popup', ['department' => $department->getId()]), 'target' => 'Department_Staff', 'options' => 'width=650,height=350'], 'Add Department Staff');
            $panel = new Panel('Staff', 'Department', new Section('pagination', $pagination));
            $container->addPanel($panel);
        }

        $manager->addContainer($container)
            ->setReturnRoute($this->generateUrl('department_list', ['tabName' => 'List']), 'Return to Departments');
        $pageHeader = new PageHeader($department->getId() === null ? TranslationHelper::translate('Add Department') : TranslationHelper::translate('Edit Department: {name}', ['{name}' => $department->getName()]));
        if ($department->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('department_add'), 'Add Department');
        }

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setPageHeader($pageHeader)
            ->createBreadcrumbs($department->getId() === null ? 'Add Department' : ['Edit Department: {name}', ['{name}' => $department->getName()]])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     *
     * 17/08/2020 15:00
     * @param Department $department
     * @param DepartmentPagination $pagination
     * @param SidebarContent $sidebarContent
     * @return JsonResponse
     * @Route("/department/{department}/delete/",name="department_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function delete(Department $department, DepartmentPagination $pagination, SidebarContent $sidebarContent)
    {
        ProviderFactory::create(Department::class)
            ->delete($department);

        return $this->list($pagination, $sidebarContent,  'List');
    }

    /**
     * deleteStaff
     *
     * 17/08/2020 14:45
     * @param DepartmentStaff $staff
     * @param DepartmentStaffPagination $pagination
     * @Route("/department/staff/{staff}/delete/", name="department_staff_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function deleteStaff(DepartmentStaff $staff, DepartmentStaffPagination $pagination)
    {
        ProviderFactory::create(DepartmentStaff::class)
            ->delete($staff);

        return $this->edit($pagination,$staff->getDepartment(),'Staff');
    }

    /**
     * editDepartmentStaff
     *
     * 17/08/2020 14:57
     * @param Department $department
     * @param DepartmentStaff|null $staff
     * @Route("/department/{department}/staff/{staff}/edit/",name="department_staff_edit_popup")
     * @Route("/department/{department}/staff/add/",name="department_staff_add_popup")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editDepartmentStaff(Department $department, ?DepartmentStaff $staff = null)
    {
        if (null === $staff || $this->getRequest()->get('_route') === 'department_staff_add_popup') {
            $staff = new DepartmentStaff();
            $action = $this->generateUrl('department_staff_add_popup', ['department' => $department->getId()]);
        } else {
            $action = $this->generateUrl('department_staff_edit_popup', ['department' => $department->getId(), 'staff' => $staff->getId()]);
        }

        $form = $this->createForm(DepartmentStaffType::class, $staff, ['action' => $action]);
        $manager = $this->getContainerManager();

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $staff->getId();
                ProviderFactory::create(DepartmentStaff::class)->persistFlush($staff);
                if ($this->getStatusManager()->isStatusSuccess() && $id !== $staff->getId()) {
                    $form = $this->createForm(DepartmentStaffType::class, $staff, ['action' => $this->generateUrl('department_staff_edit_popup', ['department' => $department->getId(), 'staff' => $staff->getId()])]);
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()
            ->render(
                [
                    'containers' => $manager->getBuiltContainers()
                ]
            );
    }
}
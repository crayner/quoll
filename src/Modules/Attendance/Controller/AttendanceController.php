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
 * Date: 12/06/2020
 * Time: 13:39
 */
namespace App\Modules\Attendance\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Form\AttendanceCLIType;
use App\Modules\Attendance\Form\AttendanceCodeType;
use App\Modules\Attendance\Form\AttendanceContextType;
use App\Modules\Attendance\Form\AttendanceReasonType;
use App\Modules\Attendance\Form\AttendanceRegistrationType;
use App\Modules\Attendance\Pagination\AttendanceCodePagination;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AttendanceController
 * @package App\Modules\Attendance\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceController extends AbstractPageController
{
    /**
     * manage
     * @param ContainerManager $manager
     * @param AttendanceCodePagination $pagination
     * @param string $tabName
     * @return mixed
     * @Route("/attendance/code/list/{tabName}",name="attendance_code_list")
     * @Route("/attendance/code/list/{tabName}",name="attendance_code_configure")
     * @Route("/attendance/code/{code}/delete/", name="attendance_code_delete")
     * @IsGranted("ROLE_ROUTE")
     * 12/06/2020 13:44
     */
    public function list(ContainerManager $manager, AttendanceCodePagination $pagination, string $tabName = 'Code')
    {
        SettingFactory::getSettingManager()->getSettingsByScope('Attendance');

        if ($this->getRequest()->getMethod() === 'POST' && $this->getRequest()->getContent() !== '') {
            return $this->saveSettings($tabName, $manager);
        }

        $container = new Container($tabName);
        $content = ProviderFactory::getRepository(AttendanceCode::class)->findBy([], ['sortOrder' => 'ASC']);
        $pagination->setContent($content)
            ->setDraggableRoute('attendance_code_sort')
            ->setPreContent($this->renderView('attendance/code_manage.html.twig'))
            ->setAddElementRoute($this->generateUrl('attendance_code_add'));
        $panel = new Panel('Code', 'Attendance', new Section('pagination', $pagination));

        $container->addPanel($panel);

        $form = $this->getForm('Reasons');
        $panel = new Panel('Reasons', 'Attendance', new Section('form', 'Reasons'));
        $container->addForm('Reasons', $form->createView())->addPanel($panel);

        $form = $this->getForm('Context');
        $panel = new Panel('Context', 'Attendance', new Section('form', 'Context'));
        $container->addForm('Context', $form->createView())
            ->addPanel($panel);

        $form = $this->getForm('Registration');
        $panel = new Panel('Registration', 'Attendance', new Section('form', 'Registration'));
        $container->addForm('Registration', $form->createView())
            ->addPanel($panel);

        $form = $this->getForm('CLI');
        $panel = new Panel('CLI', 'Attendance', new Section('form', 'CLI'));
        $container->addForm('CLI', $form->createView())
            ->addPanel($panel);

        $manager->addContainer($container->setSelectedPanel($tabName));
        return $this->getPageManager()->createBreadcrumbs('Alert Levels', [])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * saveSettings
     * @param string $tabName
     * @param ContainerManager $manager
     * @return JsonResponse
     * 13/06/2020 08:25
     */
    private function saveSettings(string $tabName, ContainerManager $manager)
    {
        $form = $this->getForm($tabName);

        SettingFactory::getSettingManager()->handleSettingsForm($form,$this->getRequest());
        $data['status'] = SettingFactory::getSettingManager()->getStatus();
        $data['errors'] = SettingFactory::getSettingManager()->getErrors();
        if ($data['status'] === 'success') {
            $form = $this->getForm($tabName);
        }

        $manager->singlePanel($form->createView());
        $data['form'] = $manager->getFormFromContainer();

        return new JsonResponse($data);
    }

    /**
     * getForm
     * @param string $tabName
     * @return FormInterface
     * 13/06/2020 08:33
     */
    private function getForm(string $tabName): FormInterface
    {
        switch ($tabName) {
            case 'Reasons':
                $form = $this->createForm(AttendanceReasonType::class, null, ['action' => $this->generateUrl('attendance_code_list', ['tabName' => 'Reasons'])]);
                break;
            case 'Context':
                $form = $this->createForm(AttendanceContextType::class, null, ['action' => $this->generateUrl('attendance_code_list', ['tabName' => 'Context'])]);
                break;
            case 'Registration':
                $form = $this->createForm(AttendanceRegistrationType::class, null,
                    [
                        'action' => $this->generateUrl('attendance_code_list', ['tabName' => 'Registration']),
                        'ip' => $this->getPageManager()->getIPAddress($this->getRequest()),
                    ]
                );
                break;
            case 'CLI':
                $form = $this->createForm(AttendanceCLIType::class, null, ['action' => $this->generateUrl('attendance_code_list', ['tabName' => 'CLI'])]);
                break;
        }

        return $form;
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param SecurityHelper $helper
     * @param AttendanceCode|null $code
     * @return JsonResponse
     * @Route("/attendance/code/{code}/edit/", name="attendance_code_edit")
     * @Route("/attendance/code/add/", name="attendance_code_add")
     * @IsGranted("ROLE_ROUTE")
     * 13/06/2020 08:40
     */
    public function edit(ContainerManager $manager, SecurityHelper $helper, ?AttendanceCode $code = null)
    {

        if (!$code instanceof AttendanceCode) {
            $code = new AttendanceCode();
            $action = $this->generateUrl('attendance_code_add');
        } else {
            $action = $this->generateUrl('attendance_code_edit', ['code' =>$code->getId()]);
        }

        $form = $this->createForm(AttendanceCodeType::class,$code, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $id = $code->getId();
                $provider = ProviderFactory::create(AttendanceCode::class);
                $data = $provider->persistFlush($code, $data);
                if ($data['status'] === 'success' && $id === $code->getId()) {
                    $form = $this->createForm(AttendanceCodeType::class, $code,
                        ['action' => $this->generateUrl('attendance_code_edit', ['code' => $code->getId()])]
                    );
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }
        $manager->setReturnRoute($this->generateUrl('attendance_code_list', ['tabName' => 'Code']))
            ->setAddElementRoute($this->generateUrl('attendance_code_add'))
            ->singlePanel($form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs($code->getId() > 0 ? 'Edit Attendance Code' : 'Add Attendance Code', [['uri' => 'attendance_code_list', 'name' => 'Attendance  Code Settings']])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * sort
     * @param AttendanceCode $target
     * @param AttendanceCode $source
     * @param AttendanceCodePagination $pagination
     * @return JsonResponse
     * @Route("/attendance/{source}/code/{target}/sort/",name="attendance_code_sort")
     * @IsGranted("ROLE_ROUTE")
     * 13/06/2020 10:49
     */
    public function sort(AttendanceCode $target, AttendanceCode $source, AttendanceCodePagination $pagination)
    {
        $manager = new EntitySortManager();
        $manager->setIndexName('sort_order')
            ->setSortField('sortOrder')
            ->execute($source, $target, $pagination);

        return new JsonResponse($manager->getDetails());
    }
}

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
 * Date: 28/05/2020
 * Time: 14:50
 */
namespace App\Modules\Staff\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Modules\Staff\Form\StaffAbsenceSettingsType;
use App\Modules\Staff\Form\StaffFieldValueSettingsType;
use App\Modules\Staff\Pagination\StaffAbsenceTypePagination;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Staff\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * staffSettings
     *
     * 18/08/2020 16:03
     * @param StaffAbsenceTypePagination $pagination
     * @param string $tabName
     * @Route("/staff/settings/{tabName}",name="staff_settings",methods={"GET"})
     * @Route("/staff/settings/{tabName}",name="staff_settings_people",methods={"GET"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function staffSettings(StaffAbsenceTypePagination $pagination, string $tabName = 'List')
    {
        $container = new Container($tabName);
        $content =  ProviderFactory::getRepository(StaffAbsenceType::class)->findBy([], ['sequenceNumber' => 'ASC']);
        $pagination->setContent($content)->setDraggableRoute('staff_absence_type_sort')
            ->setAddElementRoute($this->generateUrl('staff_absence_type_add'))
        ;
        $section = new Section('html','<h3>'.TranslationHelper::translate('Staff Absence Types').'</h3>');

        $panel = new Panel('List', 'Staff', $section);
        $section = new Section('pagination', $pagination);
        $panel->addSection($section);
        $container->addPanel($panel);
        $form = $this->createForm(StaffAbsenceSettingsType::class, null, ['action' => $this->generateUrl('staff_settings_absence')]);
        $panel = new Panel('Absence Settings', 'Staff', new Section('form','Absence Settings'));
        $container->addForm('Absence Settings', $form->createView())
            ->addPanel($panel);
        $form = $this->createForm(StaffFieldValueSettingsType::class, null, ['action' => $this->generateUrl('staff_settings_field_values')]);
        $panel = new Panel('Field Values', 'Staff', new Section('form', 'Field Values'));
        $container->addPanel($panel)
            ->addForm('Field Values', $form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs('Staff Settings')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
        ;
    }

    /**
     * staffAbsenceSettings
     *
     * 18/08/2020 15:58
     * @Route("/staff/absence/settings/",name="staff_settings_absence",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function staffAbsenceSettings()
    {
        $form = $this->createForm(StaffAbsenceSettingsType::class, null, ['action' => $this->generateUrl('staff_settings_absence')]);
        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(StaffAbsenceSettingsType::class, null, ['action' => $this->generateUrl('staff_settings_absence')]);
            }
        } catch (Exception $e) {
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

    /**
     * staffFieldValueSettings
     *
     * 18/08/2020 15:51
     * @Route("/staff/field/value/settings/",name="staff_settings_field_values",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function staffFieldValueSettings()
    {
        $form = $this->createForm(StaffFieldValueSettingsType::class, null, ['action' => $this->generateUrl('staff_settings_field_values')]);
        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(StaffFieldValueSettingsType::class, null, ['action' => $this->generateUrl('staff_settings_field_values')]);
            }
        } catch (Exception $e) {
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
}

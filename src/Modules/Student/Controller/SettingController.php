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
 * Date: 3/05/2020
 * Time: 14:06
 */
namespace App\Modules\Student\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Student\Entity\StudentNoteCategory;
use App\Modules\Student\Form\StudentAlertSettingsType;
use App\Modules\Student\Form\StudentMiscellaneousSettingsType;
use App\Modules\Student\Form\StudentNoteSettingsType;
use App\Modules\Student\Pagination\StudentNoteCategoryPagination;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Student\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * studentSettings
     *
     * 18/08/2020 15:19
     * @param StudentNoteCategoryPagination $pagination
     * @param string $tabName
     * @Route("/student/settings/{tabName}",name="student_settings",methods={"GET"})
     * @Route("/student/settings/{tabName}",name="student_settings_people",methods={"GET"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function studentSettings(StudentNoteCategoryPagination $pagination, string $tabName = 'Categories')
    {
        $container = new Container($tabName);

        $content = ProviderFactory::getRepository(StudentNoteCategory::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('student_note_category_add'));

        $panel = new Panel('Categories', 'Student', new Section('html', '<h3>'.TranslationHelper::translate('Student Note Categories', [], 'Student').'</h3>'));
        $panel->addSection(new Section('pagination', $pagination));
        $container->addPanel($panel);

        $form = $this->createForm(StudentNoteSettingsType::class, null, ['action' => $this->generateUrl('student_settings_notes')]);
        $panel = new Panel('Notes', 'Student', new Section('form', 'Notes'));
        $container->addPanel($panel)
            ->addForm('Notes', $form->createView());

        $form = $this->createForm(StudentAlertSettingsType::class, null, ['action' => $this->generateUrl('student_settings_alert')]);
        $panel = new Panel('Alerts', 'Student', new Section('form', 'Alerts'));
        $container->addPanel($panel)
            ->addForm('Alerts', $form->createView());

        $form = $this->createForm(StudentMiscellaneousSettingsType::class, null, ['action' => $this->generateUrl('student_settings_miscellaneous')]);
        $panel = new Panel('Miscellaneous', 'Student', new Section('form', 'Miscellaneous'));
        $container->addPanel($panel)
            ->addForm('Miscellaneous', $form->createView());


        return $this->getPageManager()
            ->createBreadcrumbs('Student Settings')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers()
                ]
            );
    }

    /**
     * saveNoteSettings
     *
     * 18/08/2020 14:36
     * @Route("/student/notes/settings/",name="student_settings_notes",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveNoteSettings()
    {
        $form = $this->createForm(StudentNoteSettingsType::class, null, ['action' => $this->generateUrl('student_settings_notes')]);

        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess())
                $form = $this->createForm(StudentNoteSettingsType::class, null, ['action' => $this->generateUrl('student_settings_notes')]);
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }

    /**
     * saveAlertSettings
     *
     * 18/08/2020 14:40
     * @Route("/student/alert/settings/",name="student_settings_alert",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveAlertSettings()
    {
        $form = $this->createForm(StudentAlertSettingsType::class, null, ['action' => $this->generateUrl('student_settings_alert')]);

        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess())
                $form = $this->createForm(StudentAlertSettingsType::class, null, ['action' => $this->generateUrl('student_settings_alert')]);
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }

    /**
     * saveMiscellaneousSettings
     *
     * 18/08/2020 14:41
     * @Route("/student/miscellaneous/settings/",name="student_settings_miscellaneous",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveMiscellaneousSettings()
    {
        $form = $this->createForm(StudentMiscellaneousSettingsType::class, null, ['action' => $this->generateUrl('student_settings_miscellaneous')]);

        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess())
                $form = $this->createForm(StudentMiscellaneousSettingsType::class, null, ['action' => $this->generateUrl('student_settings_miscellaneous')]);
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }
}

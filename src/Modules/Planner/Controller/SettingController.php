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
 * Time: 10:29
 */
namespace App\Modules\Planner\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Planner\Form\PlannerAccessSettingsType;
use App\Modules\Planner\Form\PlannerMiscellaneousSettingType;
use App\Modules\Planner\Form\PlannerTemplateSettingType;
use App\Modules\System\Manager\SettingFactory;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Planner\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * settings
     *
     * 17/08/2020 16:11
     * @param string $tabName
     * @Route("/planner/settings/{tabName}",name="planner_settings",methods={"GET"})
     * @Route("/planner/settings/{tabName}",name="planner_configure",methods={"GET"})
     * @Route("/planner/settings/{tabName}",name="planner_view",methods={"GET"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings(string $tabName = 'Templates')
    {
        $settingManager = SettingFactory::getSettingManager();
        $settingManager->getSettingsByScope('Planner');

        $form = $this->createForm(PlannerTemplateSettingType::class, null, ['action' => $this->generateUrl('planner_save_template_settings')]);

        $container = new Container($tabName);
        $panel = new Panel('Templates', 'Planner', new Section('form', 'Templates'));
        $container->setTranslationDomain('School')
            ->addForm('Templates', $form->createView())
            ->addPanel($panel)
        ;
        $panel = new Panel('Access', 'Planner', new Section('form', 'Access'));
        $form = $this->createForm(PlannerAccessSettingsType::class, null, ['action' => $this->generateUrl('planner_save_access_settings')]);
        $container->addPanel($panel)
            ->addForm('Access', $form->createView());
        $panel = new Panel('Miscellaneous', 'Planner', new Section('form', 'Miscellaneous'));
        $form = $this->createForm(PlannerMiscellaneousSettingType::class, null, ['action' => $this->generateUrl('planner_save_miscellaneous_settings')]);
        $container->addPanel($panel)
            ->addForm('Miscellaneous', $form->createView());

        $this->getContainerManager()->addContainer($container);
        return $this->getPageManager()->createBreadcrumbs('Planner Settings', [])
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()]);
    }

    /**
     * saveTemplateSettings
     *
     * 18/08/2020 08:00
     * @Route("/planner/save/templates/settings/",name="planner_save_template_settings",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveTemplateSettings()
    {
        $settingManager = SettingFactory::getSettingManager();
        $settingManager->getSettingsByScope('Planner');

        $form = $this->createForm(PlannerTemplateSettingType::class, null, ['action' => $this->generateUrl('planner_save_template_settings')]);

        try {
            $settingManager->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(PlannerTemplateSettingType::class, null, ['action' => $this->generateUrl('planner_save_template_settings')]);
            }
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }

    /**
     * saveAccessSettings
     *
     * 18/08/2020 08:00
     * @Route("/planner/save/access/settings/",name="planner_save_access_settings",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveAccessSettings()
    {
        $settingManager = SettingFactory::getSettingManager();
        $settingManager->getSettingsByScope('Planner');

        $form = $this->createForm(PlannerAccessSettingsType::class, null, ['action' => $this->generateUrl('planner_save_access_settings')]);

        try {
            $settingManager->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(PlannerAccessSettingsType::class, null, ['action' => $this->generateUrl('planner_save_access_settings')]);
            }
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }

    /**
     * saveMiscellaneousSettings
     *
     * 18/08/2020 08:00
     * @Route("/planner/save/miscellaneous/settings/",name="planner_save_miscellaneous_settings",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveMiscellaneousSettings()
    {
        $settingManager = SettingFactory::getSettingManager();
        $settingManager->getSettingsByScope('Planner');

        $form = $this->createForm(PlannerMiscellaneousSettingType::class, null, ['action' => $this->generateUrl('planner_save_miscellaneous_settings')]);

        try {
            $settingManager->handleSettingsForm($form, $this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(PlannerMiscellaneousSettingType::class, null, ['action' => $this->generateUrl('planner_save_miscellaneous_settings')]);
            }
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }
}

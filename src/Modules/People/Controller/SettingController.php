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
 * Date: 2/05/2020
 * Time: 12:21
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\People\Form\PeopleDayTypeSettingsType;
use App\Modules\People\Form\PeopleFieldValueSettingsType;
use App\Modules\People\Form\PeoplePrivacyDataSettingsType;
use App\Modules\System\Manager\SettingFactory;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * peopleSettings
     *
     * 19/08/2020 09:20
     * @param string|null $tabName
     * @Route("/people/settings/{tabName}",name="people_settings")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function peopleSettings(?string $tabName = 'Field Values')
    {
        $form = $this->createForm(PeopleFieldValueSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_field_value')]);
        $container = new Container($tabName);
        $panel = new Panel('Field Values', 'People', new Section('form', 'Field Values'));
        $container->addForm('Field Values', $form->createView())
            ->addPanel($panel);
        $form = $this->createForm(PeoplePrivacyDataSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_privacy_data')]);
        $panel = new Panel('Privacy / Data Options', 'People', new Section('form', 'Privacy / Data Options'));
        $container->addForm('Privacy / Data Options', $form->createView())
            ->addPanel($panel);
        $form = $this->createForm(PeopleDayTypeSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_day_type')]);
        $panel = new Panel('Day Type Options', 'People', new Section('form', 'Day Type Options'));
        $container->addForm('Day Type Options', $form->createView())
            ->addPanel($panel);

        return $this->getPageManager()->createBreadcrumbs('People Settings')
            ->render(['containers' => $this->getContainerManager()->addContainer($container)->getBuiltContainers()]);
    }

    /**
     * saveFieldValueSettings
     *
     * 19/08/2020 09:16
     * @Route("/people/field/value/settings/",name="people_settings_save_field_value")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveFieldValueSettings()
    {
        $form = $this->createForm(PeopleFieldValueSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_field_value')]);
        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->isStatusSuccess())
                $form = $this->createForm(PeopleFieldValueSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_field_value')]);
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->singlePanel($form->createView())->getFormFromContainer()]);
    }

    /**
     * savePrivacyDataSettings
     *
     * 19/08/2020 09:17
     * @Route("/people/privacy/data/settings/",name="people_settings_save_privacy_data")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function savePrivacyDataSettings()
    {
        $form = $this->createForm(PeoplePrivacyDataSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_privacy_data')]);
        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->isStatusSuccess())
                $form = $this->createForm(PeoplePrivacyDataSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_privacy_data')]);
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->singlePanel($form->createView())->getFormFromContainer()]);
    }

    /**
     * saveDayTypeSettings
     *
     * 19/08/2020 09:18
     * @Route("/people/day/type/settings/",name="people_settings_save_day_type")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveDayTypeSettings()
    {
        $form = $this->createForm(PeopleDayTypeSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_day_type')]);
        try {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            if ($this->isStatusSuccess())
                $form = $this->createForm(PeopleDayTypeSettingsType::class, null, ['action' => $this->generateUrl('people_settings_save_day_type')]);
        } catch (Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->singlePanel($form->createView())->getFormFromContainer()]);
    }
}
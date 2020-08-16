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
 * Date: 19/04/2020
 * Time: 14:08
 */

namespace App\Modules\System\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\MessageStatusManager;
use App\Modules\System\Entity\Action;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Form\DisplaySettingsType;
use App\Modules\System\Form\LocalisationSettingsType;
use App\Modules\System\Form\MiscellaneousSettingsType;
use App\Modules\System\Form\OrganisationSettingsType;
use App\Modules\System\Form\SecuritySettingsType;
use App\Modules\System\Form\SystemSettingsType;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use App\Util\ErrorMessageHelper;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\System\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * systemSettings
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/system/settings/{tabName}",name="system_settings")
     * @IsGranted("ROLE_ROUTE")
     */
    public function systemSettings(ContainerManager $manager, string $tabName = 'System')
    {
        $pageManager = $this->getPageManager();
        $request = $pageManager->getRequest();

        $settingProvider = SettingFactory::getSettingManager();
        $settingProvider->getSettingsByScope('System');
        $container = new Container();
        $manager->setTranslationDomain('System');
        TranslationHelper::setDomain('System');

        // System Settings
        $form = $this->createSystemForm();

        if ($tabName === 'System' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createSystemForm();
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());

            return new JsonResponse($settingProvider->getMessages()->toArray($manager->getFormFromContainer()), 200);
        }

        $panel = new Panel('System', 'System', new Section('form', 'System'));
        $container->addForm('System', $form->createView())->addPanel($panel);

        // Organisation Settings
        $form = $this->createOrganisationForm();

        if ($tabName === 'Organisation' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createOrganisationForm();
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getMessageStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Organisation', 'System', new Section('form', 'Organisation'));
        $container->addForm('Organisation', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Security Settings
        $form = $this->createSecurityForm();

        if ($tabName === 'Security' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createSecurityForm();
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getMessageStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Security', 'System', new Section('form', 'Security'));
        $container->addForm('Security', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Localisation
        $form = $this->createLocalisationForm();

        if ($tabName === 'Localisation' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createLocalisationForm();
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getMessageStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Localisation', 'System', new Section('form', 'Localisation'));
        $container->addForm('Localisation', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Miscellaneous
        $form = $this->createMiscellaneousForm();

        if ($tabName === 'Miscellaneous' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createMiscellaneousForm();
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getMessageStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Miscellaneous', 'System', new Section('form', 'Miscellaneous'));
        $container->addForm('Miscellaneous', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Finally Finished
        $manager->addContainer($container)->buildContainers();

        return $pageManager->createBreadcrumbs('System Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * systemSettings
     * @param ContainerManager $manager
     * @return JsonResponse|Response
     * @Route("/display/settings/", name="display_settings")
     * @IsGranted("ROLE_ROUTE")
     */
    public function displaySettings(ContainerManager $manager)
    {
        $request = $this->getPageManager()->getRequest();

        $settingProvider = SettingFactory::getSettingManager();

        // System Settings
        $form = $this->createDisplayForm();

        if ($request->getContent() !== '') {
            try {
                $settingProvider->handleSettingsForm($form, $request);
                if ($this->getMessageStatusManager()->getStatus() === 'success') {
                    $form = $this->createDisplayForm();
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getMessageStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());

        $pageHeader = new PageHeader('Display Settings');
        $pageHeader->setContent(TranslationHelper::translate('The settings used here are cached and changes will not be reflected in the display configuration immediately.', [], 'System'));

        return $this->getPageManager()
            ->setPageHeader($pageHeader)
            ->createBreadcrumbs('Display Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * removeSettingImage
     * 22/07/2020 10:13
     * @param string $scope
     * @param string $name
     * @param string $route
     * @param string $url
     * @Route("/setting/{scope}/image/{name}/{route}/{url}/removal/",name="setting_image_removal")
     * @return JsonResponse
     * @throws Exception
     */
    public function removeSettingImage(string $scope, string $name, string $route, string $url)
    {
        $actions = ProviderFactory::create(Action::class)->findLike(['routeList' => $route]);
        $doIt = false;

        foreach($actions as $action) {
            if ($doIt) break;
            foreach($action->getSecurityRoles() as $role) {
                if ($this->isGranted($role)) {
                    $doIt = true;
                    break;
                }
            }
        }

        if ($doIt) {
            $sm = SettingFactory::getSettingManager();
            $imageFile = $sm->get($scope, $name);
            if (!empty($imageFile) && strpos($imageFile, 'build') === false && strpos($imageFile, 'static') === false) {
                ImageHelper::deleteImage($imageFile);
                $sm->set($scope,$name,null);
                $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
            } else {
                $this->addFlash('info', ErrorMessageHelper::onlyNothingToDoMessage());
            }
        } else {
            $this->addFlash('error', ErrorMessageHelper::onlyNoAccessMessage());
        }
        $data['status'] = 'redirect';
        $data['redirect'] = urldecode($url);
        return new JsonResponse($data);
    }

    /**
     * createDisplayForm
     *
     * 16/08/2020 09:07
     * @return FormInterface
     */
    private function createDisplayForm(): FormInterface
    {
        return $this->createForm(DisplaySettingsType::class, null,
            [
                'action' => $this->generateUrl('display_settings'),
                'remove_organisation_background' => $this->generateUrl('setting_image_removal',
                    [
                        'scope' => 'System',
                        'name' => 'organisationBackground',
                        'route' => 'system_settings',
                        'url' => urlencode($this->generateUrl('display_settings'))
                    ]
                ),
                'remove_organisation_logo' => $this->generateUrl('setting_image_removal',
                    [
                        'scope' => 'System',
                        'name' => 'organisationLogo',
                        'route' => 'system_settings',
                        'url' => urlencode($this->generateUrl('display_settings'))
                    ]
                ),

            ]
        );
    }

    /**
     * createSystemForm
     *
     * 16/08/2020 09:10
     * @return FormInterface
     */
    private function createSystemForm(): FormInterface
    {
        return $this->createForm(SystemSettingsType::class, null, ['action' => $this->generateUrl('system_settings', ['tabName' => 'System'])]);

    }

    /**
     * createOrganisationForm
     *
     * 16/08/2020 09:15
     * @return FormInterface
     */
    private function createOrganisationForm(): FormInterface
    {
        return $this->createForm(OrganisationSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Organisation']),
                'attr' => [
                    'encType' => 'multipart/form-data',
                ],
                'remove_organisation_background' => $this->generateUrl('setting_image_removal',
                    [
                        'scope' => 'System',
                        'name' => 'organisationBackground',
                        'route' => 'system_settings',
                        'url' => urlencode($this->generateUrl('system_settings', ['tabName' => 'Organisation']))
                    ]
                ),
                'remove_organisation_logo' => $this->generateUrl('setting_image_removal',
                    [
                        'scope' => 'System',
                        'name' => 'organisationLogo',
                        'route' => 'system_settings',
                        'url' => urlencode($this->generateUrl('system_settings', ['tabName' => 'Organisation']))
                    ]
                ),
            ]
        );
    }

    /**
     * createSecurityForm
     *
     * 16/08/2020 09:18
     * @return FormInterface
     */
    private function createSecurityForm(): FormInterface
    {
        return $this->createForm(SecuritySettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Security']),
            ]
        );
    }

    /**
     * createLocalisationForm
     *
     * 16/08/2020 09:23
     * @return FormInterface
     */
    private function createLocalisationForm(): FormInterface
    {
        return $this->createForm(LocalisationSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Localisation']),
            ]
        );
    }

    /**
     * createMiscellaneousForm
     *
     * 16/08/2020 09:25
     * @return FormInterface
     */
    private function createMiscellaneousForm(): FormInterface
    {
        return $this->createForm(MiscellaneousSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Miscellaneous']),
            ]
        );
    }
}

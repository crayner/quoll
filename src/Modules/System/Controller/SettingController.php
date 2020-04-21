<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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
use App\Controller\AbstractPageController;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Form\LocalisationSettingsType;
use App\Modules\System\Form\MiscellaneousSettingsType;
use App\Modules\System\Form\OrganisationSettingsType;
use App\Modules\System\Form\SecuritySettingsType;
use App\Modules\System\Form\SystemSettingsType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\System\Controller
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

        $settingProvider = ProviderFactory::create(Setting::class);
        $settingProvider->getSettingsByScope('System');
        $container = new Container();
        $manager->setTranslationDomain('System');
        TranslationHelper::setDomain('System');
        // System Settings
        $form = $this->createForm(SystemSettingsType::class, null, ['action' => $this->generateUrl('system_settings', ['tabName' => 'System'])]);

        if ($tabName === 'System' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => TranslationHelper::translate('return.error.2', [], 'messages')];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('System');
        $container->addForm('System', $form->createView())->addPanel($panel);

        // Organisation Settings
        $form = $this->createForm(OrganisationSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Organisation']),
                'attr' => [
                    'encType' => 'multipart/form-data',
                ],
            ]
        );

        if ($tabName === 'Organisation' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => TranslationHelper::translate('return.error.2', [], 'messages') . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            $data['status'] = 'redirect';
            $data['redirect'] = $this->generateUrl('system_settings', ['tabName' => 'Organisation']);
            $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Organisation');
        $container->addForm('Organisation', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Security Settings
        $form = $this->createForm(SecuritySettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Security']),
            ]
        );

        if ($tabName === 'Security' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true) . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Security');
        $container->addForm('Security', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Localisation
        $form = $this->createForm(LocalisationSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Localisation']),
            ]
        );

        if ($tabName === 'Localisation' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true) . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Localisation');
        $container->addForm('Localisation', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Miscellaneous
        $form = $this->createForm(MiscellaneousSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_settings', ['tabName' => 'Miscellaneous']),
            ]
        );

        if ($tabName === 'Miscellaneous' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true) . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Miscellaneous');
        $container->addForm('Miscellaneous', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Finally Finished
        $manager->addContainer($container)->buildContainers();

        return $pageManager->createBreadcrumbs('System Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}
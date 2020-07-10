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
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

        $panel = new Panel('System', 'System', new Section('form', 'System'));
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

        $panel = new Panel('Organisation', 'System', new Section('form', 'Organisation'));
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

        $panel = new Panel('Security', 'System', new Section('form', 'Security'));
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

        $panel = new Panel('Localisation', 'System', new Section('form', 'Localisation'));
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
        $form = $this->createForm(DisplaySettingsType::class, null, ['action' => $this->generateUrl('display_settings')]);

        if ($request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                dump($e);
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $manager->singlePanel($form->createView());

        $pageHeader = new PageHeader('Display Settings');
        $pageHeader->setContent(TranslationHelper::translate('The settings used here are cached and changes will not be reflected in the display configuration immediately.', [], 'System'));

        return $this->getPageManager()
            ->setPageHeader($pageHeader)
            ->createBreadcrumbs('Display Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}
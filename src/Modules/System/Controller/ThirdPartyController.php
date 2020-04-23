<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/03/2020
 * Time: 13:31
 */

namespace App\Modules\System\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use App\Modules\System\Form\EmailSettingsType;
use App\Modules\System\Form\GoogleIntegrationType;
use App\Modules\System\Form\PaypalSettingsType;
use App\Modules\System\Form\SMSSettingsType;
use App\Modules\System\Manager\GoogleSettingManager;
use App\Modules\System\Manager\MailerSettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ThirdPartyController
 * @package App\Modules\System\Controller
 */
class ThirdPartyController extends AbstractPageController
{
    /**
     * thirdParty
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/third/party/settings/{tabName}/", name="third_party_settings")
     * @IsGranted("ROLE_ROUTE"))
     */
    public function thirdParty(ContainerManager $manager, string $tabName = 'Google')
    {
        $pageManager = $this->getPageManager();
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();
        TranslationHelper::setDomain('System');
        $manager->setTranslationDomain('System');

        $settingProvider = ProviderFactory::create(Setting::class);
        $container = new Container();
        $container->setSelectedPanel($tabName);

        // Google
        $form = $this->createForm(GoogleIntegrationType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'Google'])]);

        if ($tabName === 'Google' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
                $gm = new GoogleSettingManager();
                $data['errors'][] = $gm->handleGoogleSecretsFile($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true)];
            }

            $form = $this->createForm(GoogleIntegrationType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'Google'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Google');
        $container->addForm('Google', $form->createView())->addPanel($panel);

        // PayPal
        $form = $this->createForm(PaypalSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'PayPal'])]);

        if ($tabName === 'PayPal' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true)];
            }

            $form = $this->createForm(PaypalSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'PayPal'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('PayPal');
        $container->addForm('PayPal', $form->createView())->addPanel($panel);

        // SMS
        $form = $this->createForm(SMSSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'SMS'])]);

        if ($tabName === 'SMS' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true)];
            }

            $form = $this->createForm(SMSSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'SMS'])]);
            $manager->singlePanel($form->createView());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('SMS');
        $container->addForm('SMS', $form->createView())->addPanel($panel);

        // EMail
        $form = $this->createForm(EmailSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'EMail'])]);

        if ($tabName === 'EMail' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
                $msm = new MailerSettingsManager();
                $msm->handleMailerDsn($request);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data,true);
                $data['errors'][] = ['class' => 'error', 'message' => $e->getMessage()];
            }

            $form = $this->createForm(EmailSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'EMail'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('EMail');
        $container->addForm('EMail', $form->createView())->addPanel($panel);

        // Finally Finished
        $manager->addContainer($container)->buildContainers();

        return $pageManager->createBreadcrumbs('Third Party Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}
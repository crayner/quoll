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
 * Date: 26/03/2020
 * Time: 13:31
 */
namespace App\Modules\System\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\System\Manager\SettingFactory;
use App\Twig\DefaultContextEmail;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use App\Modules\System\Form\EmailSettingsType;
use App\Modules\System\Form\GoogleIntegrationType;
use App\Modules\System\Form\PaypalSettingsType;
use App\Modules\System\Form\SMSSettingsType;
use App\Modules\System\Manager\GoogleSettingManager;
use App\Modules\System\Manager\MailerSettingsManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ThirdPartyController
 * @package App\Modules\System\Controller
 */
class ThirdPartyController extends AbstractPageController
{
    /**
     * thirdParty
     *
     * 16/08/2020 10:19
     * @param string $tabName
     * @Route("/third/party/settings/{tabName}/", name="third_party_settings")
     * @IsGranted("ROLE_ROUTE"))
     * @return JsonResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function thirdParty(string $tabName = 'Google')
    {
        $manager = $this->getContainerManager();
        $pageManager = $this->getPageManager();
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();
        TranslationHelper::setDomain('System');
        $manager->setTranslationDomain('System');

        $settingProvider = SettingFactory::getSettingManager();
        $container = new Container();
        $container->setSelectedPanel($tabName);

        // Google
        $form = $this->createGoogleForm();

        if ($tabName === 'Google' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $gm = new GoogleSettingManager($this->getStatusManager());
                    if ($gm->handleGoogleSecretsFile($form, $request)) {
                        $form = $this->createGoogleForm();
                    }
                    $this->getStatusManager()->convertToFlash();
                }
            } catch (Exception $e) {
                $this->getStatusManager()->error(StatusManager::DATABASE_ERROR);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Google', 'System', new Section('form', 'Google'));
        $container->addForm('Google', $form->createView())->addPanel($panel);

        // PayPal
        $form = $this->createPaypalForm();

        if ($tabName === 'PayPal' && $request->getMethod() === 'POST') {
            $data = [];
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createPaypalForm();
                }
            } catch (Exception $e) {
                $this->getStatusManager()->error(StatusManager::DATABASE_ERROR);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('PayPal', 'System', new Section('form', 'PayPal'));
        $container->addForm('PayPal', $form->createView())->addPanel($panel);

        // SMS
        $form = $this->createSMSForm();

        if ($tabName === 'SMS' && $request->getMethod() === 'POST') {
            try {
                if ($settingProvider->handleSettingsForm($form, $request)) {
                    $form = $this->createSMSForm();
                }
            } catch (Exception $e) {
                $this->getStatusManager()->error(StatusManager::DATABASE_ERROR);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('SMS', 'System', new Section('form', 'SMS'));
        $container->addForm('SMS', $form->createView())->addPanel($panel);

        // EMail
        $msm = new MailerSettingsManager();
        $msm->parseFromDsn(ParameterBagHelper::get('mailer_dsn'));
        $form = $this->createEmailForm($msm);

        if ($tabName === 'EMail' && $request->getMethod() === 'POST') {
            if ($msm->setMessages($this->getStatusManager())->handleMailerDsn($form,$request)) {
                $form = $this->createEmailForm($msm);
            }

            $manager->singlePanel($form->createView());
            $data = $this->getStatusManager()->toArray($manager->getFormFromContainer());

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('EMail', 'System', new Section('form', 'EMail'));
        $panel->addSection(new Section('html', $this->renderView('system/test_email_button.html.twig')));
        $container->addForm('EMail', $form->createView())->addPanel($panel);

        // Finally Finished
        $manager->addContainer($container)->buildContainers();

        return $pageManager->createBreadcrumbs('Third Party Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * testEmail
     * @param MailerInterface $mailer
     * @Route("/email/test/",name="test_email")
     * @IsGranted("ROLE_ROUTE")
     */
    public function testEmail(MailerInterface $mailer)
    {
        $result = $this->getParameter('mailer_dsn');
        // Test Mailer Settings
        $dsn = Transport\Dsn::fromString($result);
        if ($dsn instanceof Transport\Dsn) {
            if ($this->getUser()->getPerson()->getEmail() === null || $this->getUser()->getPerson()->getEmail() === '' ) {
                $this->addFlash('warning', TranslationHelper::translate('The email setting where not tested as you do not have an email address recorded in your personal record.', [], 'System'));
            } else {
                $email = (new DefaultContextEmail())
                    ->from(new Address(SettingFactory::getSettingManager()->get('System', 'organisationEmail', 'kookaburra@localhost.org.au'),SettingFactory::getSettingManager()->get('System', 'organisationName', 'Kookaburra')))
                    ->to(new Address($this->getUser()->getPerson()->getEmail(),$this->getUser()->getPerson()->formatName('Standard')))
                    ->subject(TranslationHelper::translate('Test EMail Settings on {address}', ['{address}' => ParameterBagHelper::get('absoluteURL')], 'System'))
                    ->htmlTemplate('email/security/email_settings_test_message.html.twig')
                    ->getEmail()
                ;
                try {
                    $mailer->send($email);
                    $this->addFlash('info', TranslationHelper::translate('Please check your email (address) for a successful test message.', ['{address}' => $this->getUser()->getPerson()->getEmail()], 'System'));
                } catch (TransportException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }
        return $this->thirdParty('EMail');
    }

    /**
     * createGoogleForm
     *
     * 16/08/2020 09:30
     * @return FormInterface
     */
    private function createGoogleForm(): FormInterface
    {
        return $this->createForm(GoogleIntegrationType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'Google'])]);
    }

    /**
     * createPaypalForm
     *
     * 16/08/2020 09:34
     * @return FormInterface
     */
    private function createPaypalForm(): FormInterface
    {
        return $this->createForm(PaypalSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'PayPal'])]);
    }

    /**
     * createSMSForm
     *
     * 16/08/2020 09:36
     * @return FormInterface
     */
    private function createSMSForm(): FormInterface
    {
        return $this->createForm(SMSSettingsType::class, null, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'SMS'])]);
    }

    /**
     * createEmailSetting
     *
     * 16/08/2020 09:38
     * @param MailerSettingsManager $msm
     * @return FormInterface
     */
    private function createEmailForm(MailerSettingsManager $msm): FormInterface
    {
        return $this->createForm(EmailSettingsType::class, $msm, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'EMail'])]);
    }
}
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
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Messenger\SendEmailNowMessage;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Twig\DefaultContextEmail;
use App\Util\ErrorMessageHelper;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use App\Modules\System\Form\EmailSettingsType;
use App\Modules\System\Form\GoogleIntegrationType;
use App\Modules\System\Form\PaypalSettingsType;
use App\Modules\System\Form\SMSSettingsType;
use App\Modules\System\Manager\GoogleSettingManager;
use App\Modules\System\Manager\MailerSettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Messenger\MessageHandler;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
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
     * @return JsonResponse|Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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

        $settingProvider = SettingFactory::getSettingManager();
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

        $panel = new Panel('Google', 'System', new Section('form', 'Google'));
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

        $panel = new Panel('PayPal', 'System', new Section('form', 'PayPal'));
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

        $panel = new Panel('SMS', 'System', new Section('form', 'SMS'));
        $container->addForm('SMS', $form->createView())->addPanel($panel);

        // EMail
        $msm = new MailerSettingsManager();
        $msm->parseFromDsn(ParameterBagHelper::get('mailer_dsn'));
        $form = $this->createForm(EmailSettingsType::class, $msm, ['action' => $this->generateUrl('third_party_settings', ['tabName' => 'EMail'])]);

        if ($tabName === 'EMail' && $request->getMethod() === 'POST') {
            $msm->handleMailerDsn($form,$request,$this->getUser());
            $manager->singlePanel($form->createView());
            $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
            $data['form'] = $manager->getFormFromContainer();
            $data['redirect'] = $this->generateUrl('third_party_settings', ['tabName' => 'EMail']);
            $data['status'] = 'redirect';
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
                    ->from(new Address(SettingFactory::getSettingManager()->getSettingByScopeAsString('System', 'organisationEMail', 'quoll@localhost.org.au'),SettingFactory::getSettingManager()->getSettingByScopeAsString('System', 'organisationName', 'Quoll')))
                    ->to(new Address($this->getUser()->getPerson()->getEmail(),$this->getUser()->getPerson()->formatName([])))
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
        return $this->redirectToRoute('third_party_settings', ['tabName' => 'EMail']);
    }
}
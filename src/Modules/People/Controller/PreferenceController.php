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
 * Date: 17/04/2020
 * Time: 11:03
 */

namespace App\Modules\People\Controller;


use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\People\Form\PreferenceSettingsType;
use App\Modules\Security\Form\Entity\ResetPassword;
use App\Modules\Security\Form\ResetPasswordType;
use App\Modules\Security\Manager\PasswordManager;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PreferenceController extends AbstractPageController
{
    /**
     * preference
     * @param ContainerManager $manager
     * @param PasswordManager $passwordManager
     * @param TranslatorInterface $translator
     * @param string $tabName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/preferences/{tabName}", name="preferences")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function preferences(ContainerManager $manager, PasswordManager $passwordManager, TranslatorInterface $translator, string $tabName = 'Settings')
    {
        $pageManager = $this->getPageManager();
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();

        $rp = new ResetPassword();
        $passwordForm = $this->createForm(ResetPasswordType::class, $rp,
            [
                'action' => $this->generateUrl('user_admin__preferences', ['tabName' => 'Reset Password']),
                'policy' => $this->renderView('@KookaburraUserAdmin/components/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
            ]
        );

        if ($request->getContent() !== '' && $tabName === 'Reset Password')
        {
            $passwordForm->submit(json_decode($request->getContent(), true));
            if ($passwordForm->isValid()) {
                $data = $passwordManager->changePassword($rp, $this->getUser());
                $passwordForm = $this->createForm(ResetPasswordType::class, $rp,
                    [
                        'action' => $this->generateUrl('user_admin__preferences', ['tabName' => 'Reset Password']),
                        'policy' => $this->renderView('@KookaburraUserAdmin/components/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
                    ]
                );
                $manager->singlePanel($passwordForm->createView());
                $data['form'] = $manager->getFormFromContainer('formContent', 'single');
                return new JsonResponse($data, 200);
            } else {
                $manager->singlePanel($passwordForm->createView());
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
                $data['form'] = $manager->getFormFromContainer('formContent', 'single');
                return new JsonResponse($data, 200);
            }
        }


        $manager->setTranslationDomain('UserAdmin');
        $container = new Container();
        $container->setSelectedPanel($tabName);
        $passwordPanel = new Panel('Reset Password');
        $container->addForm('Reset Password', $passwordForm->createView());

        $person = $this->getUser()->getPerson();
        $settingsForm = $this->createForm(PreferenceSettingsType::class, $person, ['action' => $this->generateUrl('user_admin__preferences', ['tabName' => 'Settings'])]);

        if ($request->getContent() !== '' && $tabName === 'Settings') {
            $settingsForm->submit(json_decode($request->getContent(), true));
            $data = [];
            if ($settingsForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($person);
                $em->flush();
                $em->refresh($person);
                $data = ErrorMessageHelper::getSuccessMessage($data, true);
                $settingsForm = $this->createForm(PreferenceSettingsType::class, $person, ['action' => $this->generateUrl('user_admin__preferences', ['tabName' => 'Settings'])]);
                $manager->singlePanel($settingsForm->createView());
                $data['form'] = $manager->getFormFromContainer('formContent', 'single');
                return new JsonResponse($data, 200);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
                $manager->singlePanel($settingsForm->createView());
                $data['form'] = $manager->getFormFromContainer('formContent', 'single');
                return new JsonResponse($data, 200);
            }
        }

        $settingsPanel = new Panel();
        $settingsPanel->setName('Settings');
        $container->addForm('Settings', $settingsForm->createView());
        $container->addPanel($passwordPanel)->addPanel($settingsPanel)->setTarget('preferences');

        $manager->addContainer($container)->buildContainers();

        return $pageManager->createBreadcrumbs('Preferences')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}
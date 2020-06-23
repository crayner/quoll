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
 * Date: 17/04/2020
 * Time: 11:03
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\People\Form\PreferenceSettingsType;
use App\Modules\Security\Form\Entity\ResetPassword;
use App\Modules\Security\Form\ResetPasswordType;
use App\Modules\Security\Manager\PasswordManager;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PreferenceController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PreferenceController extends AbstractPageController
{
    /**
     * preference
     * @param ContainerManager $manager
     * @param PasswordManager $passwordManager
     * @param string $tabName
     * @return JsonResponse|Response
     * @Route("/personal/preferences/{tabName}", name="preferences")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function preferences(ContainerManager $manager, PasswordManager $passwordManager, string $tabName = 'Settings')
    {
        $request = $this->getRequest();

        $rp = new ResetPassword();
        $passwordForm = $this->createForm(ResetPasswordType::class, $rp,
            [
                'action' => $this->generateUrl('preferences', ['tabName' => 'Reset Password']),
                'policy' => $this->renderView('security/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
            ]
        );

        if ($request->getContent() !== '' && $tabName === 'Reset Password')
        {
            $passwordForm->submit(json_decode($request->getContent(), true));
            if ($passwordForm->isValid()) {
                $data = $passwordManager->changePassword($rp, $this->getUser());
                $passwordForm = $this->createForm(ResetPasswordType::class, $rp,
                    [
                        'action' => $this->generateUrl('preferences', ['tabName' => 'Reset Password']),
                        'policy' => $this->renderView('security/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
                    ]
                );
                $manager->singlePanel($passwordForm->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data, 200);
            } else {
                $manager->singlePanel($passwordForm->createView());
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data, 200);
            }
        }


        $manager->setTranslationDomain('People');
        $container = new Container();
        $container->setSelectedPanel($tabName);
        $passwordPanel = new Panel('Reset Password', 'Security', new Section('form', 'Reset Password'));
        $container->addForm('Reset Password', $passwordForm->createView());

        $person = $this->getUser()->getPerson();

        $settingsForm = $this->createForm(PreferenceSettingsType::class, $person, ['action' => $this->generateUrl('preferences', ['tabName' => 'Settings'])]);

        if ($request->getContent() !== '' && $tabName === 'Settings') {
            $settingsForm->submit(json_decode($request->getContent(), true));
            $data = [];
            if ($settingsForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($person);
                $em->flush();
                $em->refresh($person);
                $data = ErrorMessageHelper::getSuccessMessage($data, true);
                $settingsForm = $this->createForm(PreferenceSettingsType::class, $person, ['action' => $this->generateUrl('preferences', ['tabName' => 'Settings'])]);
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

        $settingsPanel = new Panel('Settings', 'People', new Section('form','Settings'));
        $container->addForm('Settings', $settingsForm->createView());
        $container->addPanel($passwordPanel)->addPanel($settingsPanel)->setTarget('preferences');

        $manager->addContainer($container)->buildContainers();

        return $this->getPageManager()->createBreadcrumbs('Personal Preferences')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}
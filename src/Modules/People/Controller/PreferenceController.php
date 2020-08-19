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
use App\Manager\StatusManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\PreferenceType;
use App\Modules\Security\Form\Entity\ResetPassword;
use App\Modules\Security\Form\ResetPasswordType;
use App\Modules\Security\Manager\PasswordManager;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
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
     * @param PasswordManager $passwordManager
     * @param string $tabName
     * @return JsonResponse|Response
     * @Route("/personal/preferences/{tabName}", name="preferences")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function preferences(PasswordManager $passwordManager, string $tabName = 'Preferences')
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
                $passwordManager->changePassword($rp, $this->getUser());
                $passwordForm = $this->createForm(ResetPasswordType::class, $rp,
                    [
                        'action' => $this->generateUrl('preferences', ['tabName' => 'Reset Password']),
                        'policy' => $this->renderView('security/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
                    ]
                );
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }
            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($passwordForm->createView())
                        ->getFormFromContainer(),
                ]
            );
        }


        $container = new Container($tabName);
        $passwordPanel = new Panel('Reset Password', 'Security', new Section('form', 'Reset Password'));
        $container->addForm('Reset Password', $passwordForm->createView());

        $person = $this->getUser()->getPerson();

        $settingsForm = $this->createForm(PreferenceType::class, $person,
            [
                'action' => $this->generateUrl('preferences', ['tabName' => 'Settings']),
                'remove_background_image' => $this->generateUrl('remove_personal_image', ['documentation' => $person->getPersonalDocumentation()->getId()])
            ]
        );

        if ($request->getContent() !== '' && $tabName === 'Settings') {
            $settingsForm->submit(json_decode($request->getContent(), true));
            if ($settingsForm->isValid()) {
                ProviderFactory::create(Person::class)->persistFlush($person);
                $settingsForm = $this->createForm(PreferenceType::class, $person,
                    [
                        'action' => $this->generateUrl('preferences', ['tabName' => 'Settings']),
                        'remove_background_image' => $this->generateUrl('remove_personal_image', ['documentation' => $person->getPersonalDocumentation()->getId()])
                    ]
                );
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }
            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($settingsForm->createView())
                        ->getFormFromContainer(),
                ]
            );
        }

        $settingsPanel = new Panel('Preferences', 'People', new Section('form','Preferences'));
        $container->addForm('Preferences', $settingsForm->createView());
        $container->addPanel($passwordPanel)
            ->addPanel($settingsPanel);

        return $this->getPageManager()->createBreadcrumbs('Personal Preferences')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            );
    }

}
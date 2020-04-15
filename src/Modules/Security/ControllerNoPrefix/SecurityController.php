<?php

namespace App\Modules\Security\ControllerNoPrefix;

use App\Modules\People\Entity\Person;
use App\Modules\Security\Manager\SecurityUser;
use App\Provider\ProviderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Modules\UserAdmin\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * login
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/login/", name="login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $this->addFlash('warning', 'return.error.0');
        $provider = ProviderFactory::create(Person::class);
        if ($this->getUser() instanceof UserInterface && !$this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('home');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $provider->getRepository()->loadUserByUsernameOrEmail($lastUsername) ?: new Person();
        $user->setUsername($lastUsername);
        new SecurityUser($user);

        return $this->redirectToRoute('home');
    }

    /**
     * logout
     * @Route("/logout/", name="logout")
     */
    public function logout()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}

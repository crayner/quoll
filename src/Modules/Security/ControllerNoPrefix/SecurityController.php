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
 * Date: 2/05/2020
 * Time: 10:14
 */
namespace App\Modules\Security\ControllerNoPrefix;

use App\Modules\People\Entity\Person;
use App\Modules\Security\Entity\SecurityUser;
use App\Provider\ProviderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Modules\Security\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * login
     * @param AuthenticationUtils $authenticationUtils
     * @return RedirectResponse|Response
     * @Route("/login/", name="login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $this->addFlash('error', 'return.error.0');
        $provider = ProviderFactory::create(SecurityUser::class);
        if ($this->getUser() instanceof UserInterface && !$this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('home');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $provider->getRepository()->loadUserByUsernameOrEmail($lastUsername) ?: new SecurityUser();
        $user->setUsername($lastUsername);

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

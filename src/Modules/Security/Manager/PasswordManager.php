<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/07/2019
 * Time: 14:28
 */

namespace App\Modules\Security\Manager;

use App\Modules\Security\Util\SecurityHelper;
use App\Util\TranslationHelper;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Provider\ProviderFactory;
use App\Modules\Security\Form\Entity\ResetPassword;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PasswordManager
 * @package App\Modules\Security\Manager
 */
class PasswordManager
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RouterInterface
     */
    private $stack;

    /**
     * PasswordManager constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param RequestStack $stack
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenStorageInterface $tokenStorage, RouterInterface $router, RequestStack $stack)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->stack = $stack;
    }

    /**
     * changePassword
     * @param ResetPassword $rp
     * @param UserInterface $user
     * @return array
     */
    public function changePassword(ResetPassword $rp, UserInterface $user): array
    {
        $session = $this->getSession();
        $person = $user->getPerson();
        $data = [];
        $data['status'] = 'success';

        //Check to see if academic year id variables are set, if not set them
        if (!$session->has('academicYear')) {
            ProviderFactory::create(AcademicYear::class)->setCurrentAcademicYear($session);
        }

        //Check password address is not blank
        $password = $rp->getRaw();
        $forceReset = $user->isPasswordForceReset();

        $ok = SecurityHelper::encodeAndSetPassword($user, $password);
        TranslationHelper::setDomain('Security');
        if ($ok && $forceReset) {
            $data['errors'][] = ['class' => 'success', 'message' => TranslationHelper::translate('return.success.a')];
            // Set Session
            $token = $this->tokenStorage->getToken();
            $session->set('_security_main', serialize($token));
            return $data;
        }

        if ($ok) {
            $data['errors'][] = ['class' => 'success', 'message' => TranslationHelper::translate('return.success.0', [], 'messages')];
            // Set Session
            $token = $this->tokenStorage->getToken();
            $session->set('_security_main', serialize($token));
            return $data;
        }

        // Failed to change password.
        $data['status'] = 'error';
        if ($forceReset)
            $data['errors'][] = ['class' => 'error', 'message' => TranslationHelper::translate('return.error.a')];
        else
            $data['errors'][] = ['class' => 'error', 'message' => TranslationHelper::translate('return.error.2', [], 'messages')];

        return $data;
    }

    /**
     * getSession
     * @return SessionInterface
     */
    private function getSession(): SessionInterface
    {
        return $this->stack->getCurrentRequest()->getSession();
    }
}
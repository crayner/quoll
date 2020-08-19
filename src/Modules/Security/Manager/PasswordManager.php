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

use App\Manager\StatusManager;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Util\TranslationHelper;
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
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var RequestStack
     */
    private RequestStack $stack;

    /**
     * @var StatusManager
     */
    private StatusManager $statusManager;

    /**
     * PasswordManager constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param RequestStack $stack
     * @param StatusManager $statusManager
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        RequestStack $stack,
        StatusManager $statusManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->stack = $stack;
        $this->statusManager = $statusManager;
    }

    /**
     * changePassword
     *
     * 19/08/2020 11:49
     * @param ResetPassword $rp
     * @param UserInterface $user
     */
    public function changePassword(ResetPassword $rp, UserInterface $user)
    {
        $session = $this->getSession();
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
            $this->getStatusManager()->success();
            $token = $this->tokenStorage->getToken();
            $session->set('_security_main', serialize($token));
            return;
        }

        if ($ok) {
            $this->getStatusManager()->success();
            $token = $this->tokenStorage->getToken();
            $session->set('_security_main', serialize($token));
            return;
        }

        // Failed to change password.
        $data['status'] = 'error';
        if ($forceReset) {
            $this->getStatusManager()->error('Your account status could not be updated, and so you cannot continue to use the system. Please contact {email} if you have any questions.', ['{email}' => "<a href='mailto:".SettingFactory::getSettingManager()->get('System', 'organisationAdministratorEmail')."'>".SettingFactory::getSettingManager()->get('System', 'organisationAdministratorName').'</a>'], 'Security');
        } else {
            $this->getStatusManager()->error(StatusManager::DATABASE_ERROR);
        }
    }

    /**
     * getSession
     * @return SessionInterface
     */
    private function getSession(): SessionInterface
    {
        return $this->stack->getCurrentRequest()->getSession();
    }

    /**
     * getStatusManager
     *
     * 19/08/2020 11:44
     * @return StatusManager
     */
    private function getStatusManager(): StatusManager
    {
        return $this->statusManager;
    }
}
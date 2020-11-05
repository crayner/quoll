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
 * Date: 5/11/2020
 * Time: 10:19
 */
namespace App\Modules\Security\Listener;

use App\Manager\StatusManager;
use App\Modules\Security\Entity\SecurityUser;
use App\Provider\ProviderFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Class OnSwitchListener
 *
 * 5/11/2020 10:20
 * @package App\Modules\Security\Listener
 * @author Craig Rayner <craig@craigrayner.com>
 */
class OnSwitchListener implements EventSubscriberInterface
{
    /**
     * @var StatusManager
     */
    private StatusManager $status;

    /**
     * OnSwitchListener constructor.
     *
     * @param StatusManager $status
     */
    public function __construct(StatusManager $status)
    {
        $this->status = $status;
    }

    /**
     * onSwitchUser
     *
     * 5/11/2020 10:20
     * @param SwitchUserEvent $event
     */
    public function onSwitchUser(SwitchUserEvent $event)
    {
        $request = $event->getRequest();

        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->remove('_locale');
            $session->remove('person');
            $session->remove('security_user');
            $session->remove('mainMenuItems');
            $session->remove('mainMenuItems_cacheTime');
            $session->remove('academicYear');
            $session->remove('fastFinderActions');
            $session->remove('fastFinderActions_cacheTime');
            $session->remove('fastFinderClasses');
            $session->remove('fastFinderClasses_cacheTime');
            $session->remove('fastFinderStudents');
            $session->remove('fastFinderStudents_cacheTime');

            if ($event->getToken()->getUser()->isEqualTo($event->getTargetUser())
                && $event->getToken() instanceof SwitchUserToken
                && $request->query->has('_switch_user')) {
                $user = ProviderFactory::getRepository(SecurityUser::class)->loadUserByUsernameOrEmail($request->query->get('_switch_user'));
                if ($user instanceof SecurityUser) {
                    $token = new SwitchUserToken($user, $user->getPassword(), 'main', $user->getRoles(), $event->getToken()->getOriginalToken());
                    $event->setToken($token);
                }
            }
        }
    }

    /**
     * getSubscribedEvents
     *
     * 5/11/2020 10:20
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }

    /**
     * Security
     *
     * @return Security
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }
}

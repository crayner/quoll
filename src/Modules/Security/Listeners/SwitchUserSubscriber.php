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
 * Date: 28/06/2020
 * Time: 09:15
 */
namespace App\Modules\Security\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Class SwitchUserSubscriber
 * @package App\Modules\Security\Listeners
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SwitchUserSubscriber implements EventSubscriberInterface
{
    /**
     * getSubscribedEvents
     * @return array|string[]
     * 28/06/2020 09:17
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }

    /**
     * onSwitchUser
     * @param SwitchUserEvent $event
     * 28/06/2020 09:18
     */
    public function onSwitchUser(SwitchUserEvent $event)
    {
        if ($event->getRequest()->hasSession()) {
            $session = $event->getRequest()->getSession();
            $user = $event->getTargetUser();
            $session->set('person', $user->getPerson());
            $event->getRequest()->attributes->set('_switch_user',true);
        }
    }
}
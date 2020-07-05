<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 25/10/2019
 * Time: 16:02
 */

namespace App\Modules\Comms\Listener;

use App\Modules\System\Manager\NotificationSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class NotificationEventListener
 * @package App\Modules\Comms\Listener
 * @author Craig Rayner <craig@craigrayner.com>
 */
class NotificationEventListener implements EventSubscriberInterface
{

    /**
     * @var NotificationSender
     */
    private $notificationSender;

    /**
     * NotificationListener constructor.
     * @param NotificationSender $notificationSender
     */
    public function __construct(NotificationSender $notificationSender)
    {
        $this->notificationSender = $notificationSender;
    }

    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [ 'sendNotifications' ]
        ];
    }

    /**
     * sendNotifications
     */
    public function sendNotifications()
    {
        if ($this->notificationSender->hasEvents())
        {
            $this->notificationSender->renderEvents();
        }
    }
}
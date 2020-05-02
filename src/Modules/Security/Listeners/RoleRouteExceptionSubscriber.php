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
 * Date: 2/05/2020
 * Time: 09:39
 */

namespace App\Modules\Security\Listeners;

use App\Modules\Security\Exception\RoleRouteException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RoleRouteExceptionSubscriber
 * @package App\Modules\Security\Listeners
 */
class RoleRouteExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * getSubscribedEvents
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'roleRouteException'
        ];
    }

    /**
     * roleRouteException
     * @param ExceptionEvent $event
     */
    public function roleRouteException(ExceptionEvent $event)
    {
        if ($event->getThrowable() instanceof RoleRouteException) {
            $route = $event->getRequest()->get('_route');
            $data = [];
            $data['status'] = 'redirect';
            $data['check'] = 'Exception';
            $data['redirect'] = sprintf('/route/%s/error/', $route);

            $event->setResponse(new JsonResponse($data, 200));
        }
    }
}
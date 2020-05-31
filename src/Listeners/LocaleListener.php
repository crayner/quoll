<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 17/04/2020
 * Time: 10:56
 */

namespace App\Listeners;


use App\Modules\People\Entity\Person;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class LocaleListener
 * @package App\Listeners
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * getSubscribedEvents
     * @return array|array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 24],
        ];
    }

    /**
     * onRequest
     * @param RequestEvent $event
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->hasSession()) {
            $person = $request->getSession()->get('person');
            if ($person instanceof Person && $person->getI18nPersonal() !== null)
                $request->setLocale($person->getI18nPersonal()->getCode());
        }
    }
}
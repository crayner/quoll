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
 * Date: 24/04/2020
 * Time: 16:04
 */

namespace App\Modules\System\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use App\Modules\System\Form\NotificationEventType;
use App\Modules\System\Listener\NotificationEventListener;
use App\Modules\System\Pagination\NotificationEventPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\ReactFormHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Modules\System\Controller
 * @todo Test this when events that allows subscribers are available
 */
class NotificationController extends AbstractPageController
{
    /**
     * notificationEvents
     * @Route("/notification/events/", name="notification_events")
     * @IsGranted("ROLE_ROUTE")
     * @param NotificationEventPagination $pagination
     * @return JsonResponse
     */
    public function notificationEvents(NotificationEventPagination $pagination)
    {
        $notificationProvider = ProviderFactory::create(NotificationEvent::class);

        $pagination->setContent($notificationProvider->selectAllNotificationEvents());

        return $this->getPageManager()->createBreadcrumbs('Notification Events')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * notificationEventEdit
     * @param NotificationEvent $event
     * @param ContainerManager $manager
     * @param ReactFormHelper $helper
     * @Route("/notification/{event}/edit/", name="notification_event_edit")
     * @Route("/notification/{event}/edit/", name="notification_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function notificationEventEdit(NotificationEvent $event, ContainerManager $manager, ReactFormHelper $helper)
    {
        $request = $this->getPageManager()->getRequest();

        $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('notification_event_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);

        if ($request->getContent() !== '') {
            $handler = new NotificationEventHandler();
            $data = $handler->handleRequest($request, $form, $event);
            if ($data['status'] === 'success')
                $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('notification_event_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Edit Notification Event',
            [
                ['uri' => 'notification_events', 'name' => 'Notification Events'],
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * notificationListenerDelete
     * @param NotificationEvent $event
     * @param NotificationListener $listener
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/notification/{event}/listener/{listener}/delete/", name="notification_listener_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function notificationListenerDelete(NotificationEvent $event, NotificationListener $listener, ContainerManager $manager)
    {
        $data = [];
        $data['errors'] = [];
        $data['form'] = [];
        $em = $this->getDoctrine()->getManager();
        if (!$event->getListeners()->contains($listener)) {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            return JsonResponse::create($data, 200);
        }

        try {
            $event->removeListener($listener);
            $em->remove($listener);
            $em->flush();
        } catch (PDOException $e) {
            $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            return JsonResponse::create($data, 200);
        }
        $em->refresh($event);
        $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('notification_event_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);

        $manager->singlePanel($form->createView());
        $data['form'] = $manager->getFormFromContainer();
        if ($data['errors'] === []) {
            $data = ErrorMessageHelper::getSuccessMessage($data, true);
        }

        //JSON Response required.
        return JsonResponse::create($data, 200);
    }
}
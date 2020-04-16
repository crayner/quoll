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
 * Date: 16/04/2020
 * Time: 16:35
 */

namespace App\Modules\Comms\Controller;

use App\Controller\AbstractPageController;
use App\Modules\Comms\Entity\Notification;
use App\Modules\Comms\Manager\NotificationTrayManager;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Modules\Comms\Controller
 */
class NotificationController extends AbstractPageController
{
    /**
     * index
     * @param NotificationTrayManager $notification
     * @return JsonResponse
     * @Route("/api/notification/refresh/", name="notification_refresh")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function refresh(NotificationTrayManager $notification)
    {
        return new JsonResponse($notification->execute($this->getUser()), 200);
    }

    /**
     * manage
     * @Route("/notifications/manage/", name="notifications_manage")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function manage()
    {
        $notifications = ProviderFactory::getRepository(Notification::class)->findByPersonStatus($this->getUser()->getPerson());
        $archived = ProviderFactory::getRepository(Notification::class)->findByPersonStatus($this->getUser()->getPerson(), 'Archived');

        return $this->getPageManager()->render(
            [
                'content' => $this->renderView('notifications/notification_manage.html.twig',
                    [
                        'new' => $notifications,
                        'archived' => $archived,
                    ]
                )
            ]
        );
    }

}
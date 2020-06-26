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
 * Date: 16/04/2020
 * Time: 16:35
 */
namespace App\Modules\Comms\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\Comms\Entity\Notification;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use App\Modules\Comms\Form\NotificationEventType;
use App\Modules\Comms\Manager\NotificationTrayManager;
use App\Modules\Comms\Pagination\NotificationEventPagination;
use App\Modules\System\Manager\Hidden\NotificationEventHandler;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\ReactFormHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Modules\Comms\Controller
 * @author Craig Rayner <craig@craigrayner.com>
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
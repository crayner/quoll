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
 * Time: 16:52
 */

namespace App\Modules\Comms\Controller;

use App\Controller\AbstractPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessengerController
 * @package App\Modules\Comms\Controller
 */
class MessengerController extends AbstractPageController
{
    /**
     * index
     * @return JsonResponse
     * @Route("/api/messenger/refresh/", name="messenger_refresh")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function refresh()
    {
        return new JsonResponse(['count' => 0], 200);
    }

}
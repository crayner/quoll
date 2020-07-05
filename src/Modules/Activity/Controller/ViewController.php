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
 * Date: 4/06/2020
 * Time: 10:50
 */
namespace App\Modules\Activity\Controller;

use App\Controller\AbstractPageController;
use App\Modules\Activity\Entity\Activity;
use App\Modules\Activity\Pagination\ActivityPagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ViewController
 * @package App\Modules\Activity\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ViewController extends AbstractPageController
{
    /**
     * list
     * @param ActivityPagination $pagination
     * @param array $data
     * @return Response
     * @Route("/activity/list/",name="activity_list")
     * @Route("/activity/list/",name="activity_details")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 10:59
     */
    public function list(ActivityPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(Activity::class)->findForPagination();

        $pagination->setContent($content);

        return $this->getPageManager()
            ->setMessages(isset($data['errors']) ? $data['errors'] : [])
            ->createBreadcrumbs('Activities')
            ->render(['pagination' => $pagination->toArray()]);
    }

}
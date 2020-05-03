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
 * Date: 3/05/2020
 * Time: 14:44
 */
namespace App\Modules\Staff\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StaffController
 * @package App\Modules\Staff\Controller
 */
class StaffController extends AbstractPageController
{
    /**
     * view
     * @Route("/staff/view/", name="staff_view")
     * @param ContainerManager $manager
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function view(ContainerManager $manager)
    {
        $manager->setContent('<h3>View Staff</h3>');
        return $this->getPageManager()->createBreadcrumbs('View Staff')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}

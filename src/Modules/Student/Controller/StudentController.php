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
 * Date: 3/05/2020
 * Time: 14:47
 */
namespace App\Modules\Student\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentController
 * @package App\Modules\Student\Controller
 */
class StudentController extends AbstractPageController
{
    /**
     * view
     * @Route("/student/view/", name="student_view")
     * @param ContainerManager $manager
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function view(ContainerManager $manager)
    {
        $manager->setContent('<h3>View Students</h3>');
        return $this->getPageManager()->createBreadcrumbs('View Students')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}

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
 * Date: 3/09/2020
 * Time: 08:14
 */
namespace App\Modules\Enrolment\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Pagination\CourseClassEnrolmentPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ClassEnrolmentController
 * @package App\Modules\Enrolment\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ClassEnrolmentController extends AbstractPageController
{
    /**
     * list
     *
     * 3/09/2020 08:15
     * @Route("/course/enrolment/by/class/list/",name="course_enrolment_by_class_list")
     * @IsGranted("ROLE_ROUTE")
     * @param CourseClassEnrolmentPagination $pagination
     * @return JsonResponse
     */
    public function list(CourseClassEnrolmentPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(CourseClass::class)->findCourseClassEnrolmentPagination(),'CourseClassEnrolmentPagination')
            ->setPageMax(50);

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Course Enrolment by Class')
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }
}

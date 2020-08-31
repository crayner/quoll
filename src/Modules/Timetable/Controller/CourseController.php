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
 * Date: 31/08/2020
 * Time: 09:44
 */
namespace App\Modules\Timetable\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Timetable\Pagination\CoursePagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CourseController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseController extends AbstractPageController
{
    /**
     * list
     *
     * 31/08/2020 09:49
     * @param CoursePagination $pagination
     * @Route("/course/list/",name="course_list")
     * @return JsonResponse
     */
    public function list(CoursePagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(Course::class)->findCoursePagination(),'CoursePagination');

        $container = new Container();
        $panel = new Panel('null', 'Curriculum', new Section('pagination', $pagination));
        $panel->addSection(new Section('html', $this->renderView('school/academic_year_warning.html.twig')));
        $container->addPanel($panel);

        return $this->getPageManager()
            ->createBreadcrumbs('Courses and Classes')
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * edit
     *
     * 31/08/2020 12:35
     * @param Course|null $course
     * @param string $tabName
     * @return JsonResponse
     * @Route("/course/{course}/edit/{tabName}",name="course_edit")
     * @Route("/course/{course}/delete/",name="course_delete")
     * @Route("/course/add/",name="course_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(?Course $course = null, string $tabName = 'Details')
    {
        if ($course === null) {
            $course = new Course();
            $action = $this->generateUrl('course_add');
        } else {
            $action = $this->generateUrl('course_edit', ['course' => $course->getId(), 'tabName' => $tabName]);
        }




        $container = new Container($tabName);

        return $this->getPageManager()
            ->createBreadcrumbs($course->getId() === null ? 'Add Course' : 'Edit Course',
                [
                    ['name' => 'Courses and Classes', 'uri' => 'course_list']
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
       ;
    }
}

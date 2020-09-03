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
namespace App\Modules\Curriculum\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Curriculum\Form\CourseBlurbType;
use App\Modules\Curriculum\Form\CourseType;
use App\Modules\Curriculum\Pagination\CoursePagination;
use App\Modules\Enrolment\Pagination\CourseClassPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CourseController
 * @package App\Modules\Curriculum\Controller
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
     * @Route("/course/list/",name="enrolment_course_list")
     * @return JsonResponse
     */
    public function list(CoursePagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(Course::class)->findCoursePagination(),'CoursePagination')
            ->setAddElementRoute($this->generateUrl('course_add'));

        $container = new Container();
        $panel = new Panel('null', 'Curriculum', new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Courses and Classes')
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('course_list'))
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
     * @param CourseClassPagination $pagination
     * @param Course|null $course
     * @param string $tabName
     * @Route("/course/{course}/edit/{tabName}",name="course_edit")
     * @Route("/course/add/",name="course_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(CourseClassPagination $pagination, ?Course $course = null, string $tabName = 'Details')
    {
        if ($course === null) {
            $course = new Course();
            $action = $this->generateUrl('course_add');
        } else {
            $action = $this->generateUrl('course_edit', ['course' => $course->getId(), 'tabName' => $tabName]);
        }

        if ($this->getRequest()->getContent() !== '') {
            if ($tabName === 'Blurb') {
                $form = $this->getCourseBlurbForm($course);
            } else {
                $form = $this->getCourseForm($course, $action);
            }
            $content = json_decode($this->getRequest()->getContent(), true);
            $id = $course->getId();
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(Course::class)->persistFlush($course);
                if ($id !== $course->getId() && $this->isStatusSuccess()) {
                    $action = $this->generateUrl('course_edit', ['course' => $course->getId(), 'tabName' => 'Details']);
                    $this->getStatusManager()->setReDirect($action, true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        $container = new Container($tabName);
        $panel = new Panel('Details', 'Curriculum', new Section('form', 'Details'));
        $container->addForm('Details', $this->getCourseForm($course, $action)->createView())
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        if ($course->getId() !== null) {
            $panel = new Panel('Blurb', 'Curriculum', new Section('form', 'Blurb'));
            $container->addForm('Blurb', $this->getCourseBlurbForm($course)->createView())
                ->addPanel(AcademicYearHelper::academicYearWarning($panel));

            $pagination->setContent($course->getCourseClasses()->toArray(), 'CourseClassPagination')
                ->setAddElementRoute($this->generateUrl('course_class_add', ['course' => $course->getId()]));
            $panel = new Panel('Classes', 'Enrolment', new Section('pagination', $pagination));
            $container->addPanel(AcademicYearHelper::academicYearWarning($panel));
            $this->getContainerManager()->setAddElementRoute($this->generateUrl('course_add'));
        }

        return $this->getPageManager()
            ->createBreadcrumbs($course->getId() === null ? 'Add Course' : ['Edit Course {name}', ['{name}' => $course->getName()]],
                [
                    ['name' => 'Courses and Classes', 'uri' => 'course_list']
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('course_list'))
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
       ;
    }

    /**
     * getCourseBlurbForm
     *
     * 1/09/2020 10:26
     * @param Course $course
     * @return FormInterface
     */
    private function getCourseBlurbForm(Course $course): FormInterface
    {
        return $this->createForm(CourseBlurbType::class, $course, ['action' => $this->generateUrl('course_edit', ['tabName' => 'Blurb', 'course' => $course->getId()])]);
    }

    /**
     * getCourseForm
     *
     * 1/09/2020 10:28
     * @param Course $course
     * @param string $action
     * @return FormInterface
     */
    private function getCourseForm(Course $course, string $action): FormInterface
    {
        return $this->createForm(CourseType::class, $course, ['action' => $action]);
    }

    /**
     * delete
     *
     * 1/09/2020 10:57
     * @param Course $course
     * @param CoursePagination $pagination
     * @Route("/course/{course}/delete/",name="course_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(Course $course, CoursePagination $pagination)
    {
        ProviderFactory::create(Course::class)->delete($course);

        return $this->list($pagination);
    }
}

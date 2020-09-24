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
 * Time: 16:30
 */
namespace App\Modules\Enrolment\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Form\Type\EnumType;
use App\Manager\EntitySortManager;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\Enrolment\Form\CourseClassTutorType;
use App\Modules\Enrolment\Form\CourseClassType;
use App\Modules\Enrolment\Pagination\CourseClassTutorPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Util\CacheHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CourseClassController
 * @package App\Modules\Curriculum\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassController extends AbstractPageController
{
    /**
     * edit
     *
     * 1/09/2020 09:16
     * @param Course $course
     * @param CourseClassTutorPagination $pagination
     * @param CourseClass|null $class
     * @param string $tabName
     * @return JsonResponse
     * @Route("/course/{course}/class/{class}/edit/{tabName}",name="course_class_edit")
     * @Route("/course/{course}/class/{class}/delete/",name="course_class_delete")
     * @Route("/course/{course}/class/add/",name="course_class_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(Course $course, CourseClassTutorPagination $pagination, ?CourseClass $class = null, string $tabName = 'Details')
    {
        if ($class === null || $this->isRoute('course_class_add')) {
            $class = new CourseClass($course);
            $action = $this->generateUrl('course_class_add', ['course' => $course->getId(), 'tabName' => $tabName]);
        } else {
            $action = $this->generateUrl('course_class_edit', ['course' => $course->getId(), 'class' => $class->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(CourseClassType::class, $class, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $id = $class->getId();
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(CourseClass::class)->persistFlush($class);
                if ($id !== $class->getId() && $this->isStatusSuccess()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('course_class_edit', ['course' => $course->getId(), 'class' => $class->getId(), 'tabName' => $tabName]), true);
                }
                CacheHelper::clearCacheValue('course_class_choices');
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        $container = new Container($tabName);
        $panel = new Panel('Details', 'Enrolment', new Section('form','single'));
        $container->addForm('single', $form->createView())
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));
        if ($class->getId() !== null) {
            $this->getContainerManager()
                ->setAddElementRoute($this->generateUrl('course_class_add', ['course' => $course->getId()]));
            $pagination
                ->setContent($class->getTutors()->toArray());
            $panel = new Panel('Tutors', 'Enrolment', new Section('pagination', $pagination));
            $container->addPanel(AcademicYearHelper::academicYearWarning($panel));
        }

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->toArray())
            ->setUrl($class->getId() === null ? $this->generateUrl('course_class_add', ['course' => $course->getId()]) : $this->generateUrl('course_class_edit', ['course' => $course->getId(), 'class' => $class->getId(), 'tabName' => $tabName]))
            ->createBreadcrumbs($class->getId() ? ['Edit Class {name}', ['{name}' => $class->getName()], 'Enrolment'] : 'Add Class',
                [
                    ['name' => 'Courses and Classes', 'uri' => 'course_list'],
                    ['name' => 'Edit Course {name}', 'uri' => 'course_edit', 'uri_params' => ['course' => $course->getId(), 'tabName' => 'Classes'], 'trans_params' => ['{name}' => $course->getName()]],
                ]
            )
            ->render(
                [
                    'containers' =>$this->getContainerManager()
                        ->setHideSingleFormWarning()
                        ->addContainer($container)
                        ->setReturnRoute($this->generateUrl('course_edit', ['course' => $course->getId(), 'tabName' => 'Classes']))
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * classTutorEdit
     *
     * 21/09/2020 10:11
     * @param CourseClass $class
     * @param CourseClassTutor|null $tutor
     * @Route("/course/class/{class}/tutor/{tutor}/edit/",name="course_class_tutor_edit")
     * @Route("/course/class/{class}/tutor/add/",name="course_class_tutor_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function classTutorEdit(CourseClass $class, ?CourseClassTutor $tutor = null)
    {
        if ($tutor === null) {
            $tutor =new CourseClassTutor($class);
            $action = $this->generateUrl('course_class_tutor_add', ['class' => $class->getId()]);
        } else {
            $action = $this->generateUrl('course_class_tutor_edit', ['class' => $class->getId(), 'tutor' => $tutor->getId()]);
        }

        $form = $this->createForm(CourseClassTutorType::class, $tutor, ['action' => $action]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                $id = $tutor->getId();
                ProviderFactory::create(CourseClassTutor::class)->persistFlush($tutor);
                if ($id !== $tutor->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('course_class_tutor_edit', ['class' => $class->getId(), 'tutor' => $tutor->getId()]), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('single', 'Enrolment', new Section('form', 'single'));
        $container->addForm('single', $form->createView())
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        if ($tutor->getId() !== null) $this->getContainerManager()->setAddElementRoute($this->generateUrl('course_class_tutor_add', ['class' => $class->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs($tutor->getId() === null ? 'Add Tutor' : 'Edit Tutor of Class',
                [
                    ['name' => 'Courses and Classes', 'uri' => 'course_list'],
                    ['name' => 'Edit Course {name}', 'uri' => 'course_edit', 'uri_params' => ['course' => $class->getCourse()->getId(), 'tabName' => 'Classes'], 'trans_params' => ['{name}' => $class->getCourse()->getName()]],
                    ['name' => 'Edit Class {name}', 'uri' => 'course_class_edit', 'trans_params' => ['{name}' => $class->getName()], 'uri_params' => ['class' => $class->getId(),'course' => $class->getCourse()->getId(), 'tabName' => 'Tutors']],
                ]
            )->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->setReturnRoute($this->generateUrl('course_class_edit', ['class' => $class->getId(),'course' => $class->getCourse()->getId(), 'tabName' => 'Tutors']))
                        ->getBuiltContainers(),
                ]
            )
        ;
    }

    /**
     * classTutorRemove
     *
     * 21/09/2020 10:11
     * @param CourseClass $class
     * @param CourseClassTutorPagination $pagination
     * @param CourseClassTutor|null $tutor
     * @return JsonResponse
     * @Route("/course/class/{class}/tutor/{tutor}/remove/",name="course_class_tutor_remove")
     * @IsGranted("ROLE_ROUTE")
     */
    public function classTutorRemove(CourseClass $class, CourseClassTutorPagination $pagination, CourseClassTutor $tutor)
    {
        ProviderFactory::create(CourseClassTutor::class)->delete($tutor);

        return $this->edit($class->getCourse(), $pagination, $class, 'Tutors');
    }

    /**
     * classTutorSort
     *
     * 22/09/2020 08:57
     * @param CourseClassTutor $source
     * @param CourseClassTutor $target
     * @param CourseClassTutorPagination $pagination
     * @param EntitySortManager $manager
     * @Route("/course/class/tutor/{source}/{target}/sort/",name="course_class_tutor_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function classTutorSort(CourseClassTutor $source, CourseClassTutor $target, CourseClassTutorPagination $pagination, EntitySortManager $manager)
    {
        $manager->setSortField('sortOrder')
            ->setIndexName('course_class_sort_order')
            ->setFindBy(['courseClass' => $source->getCourseClass()])
            ->setIndexColumns(['sortOrder','courseClass'])
            ->execute($source, $target, $pagination);

        return $this->generateJsonResponse(['content' => $manager->getPaginationContent()]);
    }
}

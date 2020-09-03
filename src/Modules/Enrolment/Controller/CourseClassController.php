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
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Form\CourseClassType;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @param CourseClass|null $class
     * @Route("/course/{course}/class/{class}/edit/",name="course_class_edit")
     * @Route("/course/{course}/class/{class}/delete/",name="course_class_delete")
     * @Route("/course/{course}/class/add/",name="course_class_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(Course $course, ?CourseClass $class = null)
    {
        if ($class === null) {
            $class = new CourseClass($course);
            $action = $this->generateUrl('course_class_add', ['course' => $course->getId()]);
        } else {
            $action = $this->generateUrl('course_class_edit', ['course' => $course->getId(), 'class' => $class->getId()]);
        }

        $form = $this->createForm(CourseClassType::class, $class, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $id = $class->getId();
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(CourseClass::class)->persistFlush($class);
                if ($id !== $class->getId() && $this->isStatusSuccess()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('course_class_edit', ['course' => $course->getId(), 'class' => $class->getId()]), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('single', 'Enrolment', new Section('form','single'));
        $container->addForm('single', $form->createView())
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));
        if ($class->getId() !== null) $this->getContainerManager()->setAddElementRoute($this->generateUrl('course_class_add', ['course' => $course->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs($class->getId() ? ['Edit Class {name}', ['{name}' => $class->getName()], 'Enrolment'] : 'Add Class',
                [
                    ['name' => 'Courses and Classes', 'uri' => 'course_list'],
                    ['name' => 'Edit Course {name}', 'uri' => 'course_edit', 'uri_params' => ['course' => $course->getId(), 'tabName' => 'Classes'], 'trans_params' => ['{name}' => $course->getName()]],
                ]
            )
            ->render(
                [
                    'containers' =>$this->getContainerManager()
                        ->addContainer($container)
                        ->setReturnRoute($this->generateUrl('course_edit', ['course' => $course->getId(), 'tabName' => 'Classes']))
                        ->getBuiltContainers(),
                ]
            );
    }
}
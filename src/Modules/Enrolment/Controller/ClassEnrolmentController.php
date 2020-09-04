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
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\Enrolment\Form\CourseClassPersonType;
use App\Modules\Enrolment\Pagination\CourseClassEnrolmentPagination;
use App\Modules\Enrolment\Pagination\CourseClassParticipantPagination;
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

    /**
     * edit
     *
     * 3/09/2020 11:53
     * @param CourseClass $class
     * @param CourseClassParticipantPagination $pagination
     * @return JsonResponse
     * @Route("/course/class/{class}/enrolment/manage/",name="course_class_enrolment_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(CourseClass $class, CourseClassParticipantPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::create(CourseClassPerson::class)->findCourseClassParticipationPagination($class),'CourseClassParticipationPagination')
            ->setAddElementRoute($this->generateUrl('course_class_enrolment_add', ['class' => $class->getId()]))
            ->setPageMax(50);

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs(['Manage {name} Enrolment', ['{name}' => $class->getAbbreviatedName()], 'Enrolment'],
                [
                    ['name' => 'Course Enrolment by Class', 'uri' => 'course_enrolment_by_class_list']
                ]
            )
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]))
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * remove
     *
     * 4/09/2020 09:41
     * @param CourseClass $class
     * @param CourseClassPerson $person
     * @param CourseClassParticipantPagination $pagination
     * @Route("/course/class/{class}/enrolment/{person}/delete/",name="course_class_enrolment_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function remove(CourseClass $class, CourseClassPerson $person, CourseClassParticipantPagination $pagination)
    {
        ProviderFactory::create(CourseClassPerson::class)->delete($person);

        return $this->manage($class, $pagination);
    }

    /**
     * edit
     *
     * 3/09/2020 11:53
     * @param CourseClass $class
     * @param CourseClassPerson|null $person
     * @Route("/course/class/{class}/enrolment/{person}/edit/",name="course_class_enrolment_edit")
     * @Route("/course/class/{class}/enrolment/add/",name="course_class_enrolment_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(CourseClass $class, ?CourseClassPerson $person = null)
    {
        if (null === $person) {
            $action = $this->generateUrl('course_class_enrolment_add', ['class' => $class->getId()]);
            $person = new CourseClassPerson($class);
        } else {
            $action = $this->generateUrl('course_class_enrolment_edit', ['class' => $class->getId(), 'person' => $person->getId()]);
        }

        $form = $this->createForm(CourseClassPersonType::class, $person, ['action' => $action]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                $id = $person->getId();
                ProviderFactory::create(CourseClassPerson::class)->persistFlush($person);
                if ($id !== $person->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('course_class_enrolment_edit', ['class' => $class->getId(), 'person' => $person->getId()]), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('form','single'));
        $container->addForm('single', $form->createView())
            ->addPanel($panel);

        if ($person->getId() !== null) $this->getContainerManager()
            ->setAddElementRoute($this->generateUrl('course_class_enrolment_add', ['class' => $class->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs('Edit Enrolment',
                [
                    ['name' => 'Course Enrolment by Class', 'uri' => 'course_enrolment_by_class_list'],
                    ['name' => 'Manage {name} Enrolment', 'uri' => 'course_class_enrolment_manage', 'uri_params' => ['class' => $class->getId()], 'trans_params' => ['{name}' => $class->getAbbreviatedName()]],
                ]
            )->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]))
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            );
    }
}

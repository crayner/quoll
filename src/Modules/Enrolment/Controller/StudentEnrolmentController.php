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
 * Date: 8/09/2020
 * Time: 09:17
 */
namespace App\Modules\Enrolment\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Enrolment\Entity\StudentRollGroup;
use App\Modules\Enrolment\Form\StudentEnrolmentType;
use App\Modules\Enrolment\Pagination\StudentEnrolmentPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentEnrolmentController
 * @package App\Modules\Enrolment\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentEnrolmentController extends AbstractPageController
{
    /**
     * enrolment
     *
     * 8/09/2020 09:57
     * @param StudentEnrolmentPagination $pagination
     * @Route("/student/enrolment/list/",name="student_enrolment_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(StudentEnrolmentPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(Student::class)->getStudentEnrolmentPaginationContent())
            ->setPageMax(50);

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('html', $this->renderView('enrolment/student_search_warning.html.twig')));
        $panel->addSection(new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Student Enrolment')
            ->setUrl($this->generateUrl('student_enrolment_list'))
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * edit
     *
     * 10/09/2020 09:27
     * @param Student $student
     * @Route("/student/enrolment/{student}/edit/",name="student_enrolment_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(Student $student)
    {
        $se = ProviderFactory::getRepository(StudentRollGroup::class)->findOneByStudent($student) ?: new StudentRollGroup($student);
        $se->setStudent($student);

        $form = $this->createForm(StudentEnrolmentType::class, $se, ['action' => $this->generateUrl('student_enrolment_edit', ['student' => $student->getId()])]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(StudentRollGroup::class)->persistFlush($se);
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('form', 'single'));
        $container->addForm('single', $form->createView())
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Edit Student Enrolment',
                [
                    ['uri' => 'student_enrolment_list', 'name' => 'Student Enrolment']
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('student_enrolment_list'))
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
        ;
    }

    /**
     * delete
     *
     * 10/09/2020 09:33
     * @param StudentRollGroup $enrolment
     * @param StudentEnrolmentPagination $pagination
     * @Route("/student/enrolment/{enrolment}/delete/",name="student_enrolment_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(StudentRollGroup $enrolment, StudentEnrolmentPagination $pagination)
    {
        ProviderFactory::create(StudentRollGroup::class)->delete($enrolment);

        return $this->list($pagination);
    }
}

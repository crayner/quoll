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
 * Date: 20/07/2020
 * Time: 08:49
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\SchoolStudentType;
use App\Modules\Student\Entity\Student;
use App\Modules\Student\Form\StudentType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentController extends PeopleController
{
    /**
     * edit
     * @param ContainerManager $manager
     * @param Student $student
     * @return Response
     * @Route("/student/{student}/edit/",name="student_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 09:21
     */
    public function editStudent(ContainerManager $manager, Student $student)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createStudentForm($student);

            return $this->saveStudentContent($form, $manager, $student, 'Student');
        } else {
            $form = $this->createStudentForm($student);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Person $person
     * @return Response
     * @Route("/student/{student}/school/edit/",name="student_school_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 09:21
     */
    public function editSchoolStudent(ContainerManager $manager, Student $student)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createSchoolStudentForm($student);

            return $this->saveStudentContent($form, $manager, $student, 'School');
        } else {
            $form = $this->createSchoolStudentForm($student);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * studentDeletePersonalBackground
     * @param Student $student
     * @return JsonResponse
     * @Route("/student/{student}/personal/background/remove/",name="student_personal_background_remove")
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 10:23
     */
    public function studentDeletePersonalBackground(Student $student)
    {
        $student->removePersonalBackground();

        $data = ProviderFactory::create(Student::class)->persistFlush($student, []);

        return new JsonResponse($data);
    }

    /**
     * addToStudent
     * @param Person $person
     * @Route("/student/{person}/add/",name="student_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 19/07/2020 12:13
     */
    public function addToStudent(Person $person)
    {
        if (null === $person->getStudent()) {
            $student = new Student($person);
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($student);
                $student->setPerson($person);
                $person->getSecurityUser()->addSecurityRole('ROLE_STUDENT');
                $em->persist($person);
                $em->flush();
                $data = [];
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlySuccessMessage(true)];
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Student']);
            } catch (PDOException | \PDOException | \Exception $e) {
                $data = [];
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true)];
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Student']);

            }
        } else {
            $data['status'] = 'redirect';
            $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Student']);
            $this->addFlash('warning', ErrorMessageHelper::onlyNothingToDoMessage());
        }
        return new JsonResponse($data);
    }

    /**
     * saveStudentContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param Student $student
     * @param string $tabName
     * @return JsonResponse
     * 19/07/2020 16:29
     */
    private function saveStudentContent(FormInterface $form, ContainerManager $manager, Student $student, string $tabName)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(Student::class)->persistFlush($student,$data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                if ($tabName === 'School') {
                    $form = $this->createSchoolStudentForm($student);
                } else {
                    $form = $this->createStudentForm($student);
                }
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        }
        return new JsonResponse($data);
    }

    /**
     * createStudentForm
     * @param Student $student
     * @return FormInterface
     * 20/07/2020 11:03
     */
    private function createStudentForm(Student $student): FormInterface
    {
        return $this->createForm(StudentType::class, $student,
            [
                'action' => $this->generateUrl('student_edit', ['student' => $student->getId()]),
            ]
        );
    }

    /**
     * createSchoolStudentForm
     * @param Student $student
     * @return FormInterface
     * 20/07/2020 11:04
     */
    private function createSchoolStudentForm(Student $student): FormInterface
    {
        return $this->createForm(SchoolStudentType::class, $student,
            [
                'action' => $this->generateUrl('student_school_edit', ['student' => $student->getId()]),
                'remove_personal_background' => $this->generateUrl('student_personal_background_remove', ['student' => $student->getId()]),
            ]
        );
    }
}

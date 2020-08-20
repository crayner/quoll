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

use App\Manager\StatusManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\SchoolStudentType;
use App\Modules\Student\Entity\Student;
use App\Modules\Student\Form\StudentType;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentController extends PeopleController
{
    /**
     * editStudent
     *
     * 20/08/2020 14:07
     * @param Student $student
     * @Route("/student/{student}/edit/",name="student_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editStudent(Student $student)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createStudentForm($student);

            return $this->saveStudentContent($form, $student, 'Student');
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            $form = $this->createStudentForm($student);
            return $this->singleForm($form);
        }
    }

    /**
     * editSchoolStudent
     *
     * 20/08/2020 14:08
     * @param Student $student
     * @Route("/student/{student}/school/edit/",name="student_school_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editSchoolStudent(Student $student)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createSchoolStudentForm($student);

            return $this->saveStudentContent($form, $student, 'School');
        } else {
            $form = $this->createSchoolStudentForm($student);
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            return $this->singleForm($form);
        }
    }

    /**
     * studentDeletePersonalBackground
     *
     * 20/08/2020 14:09
     * @param Student $student
     * @Route("/student/{student}/personal/background/remove/",name="student_personal_background_remove")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function studentDeletePersonalBackground(Student $student)
    {
        $student->removePersonalBackground();

        ProviderFactory::create(Student::class)->persistFlush($student);

        return $this->getStatusManager()->toJsonResponse();
    }

    /**
     * addToStudent
     *
     * 20/08/2020 14:15
     * @param Person $person
     * @Route("/student/{person}/add/",name="student_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function addToStudent(Person $person)
    {
        if (null === $person->getStudent()) {
            $student = new Student($person);
            $student->setPerson($person);
            $person->getSecurityUser()->addSecurityRole('ROLE_STUDENT');
            ProviderFactory::create(Student::class)->persistFlush($student, false);
            ProviderFactory::create(Person::class)->persistFlush($person);
        } else {
            $this->getStatusManager()->warning(StatusManager::NOTHING_TO_DO);
        }
        return $this->getStatusManager()->toJsonResponse();
    }

    /**
     * saveStudentContent
     *
     * 20/08/2020 14:18
     * @param FormInterface $form
     * @param Student $student
     * @param string $tabName
     * @return JsonResponse
     */
    private function saveStudentContent(FormInterface $form, Student $student, string $tabName)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        if ($form->isValid()) {
            ProviderFactory::create(Student::class)->persistFlush($student);
            if ($this->isStatusSuccess()) {
                if ($tabName === 'School') {
                    $form = $this->createSchoolStudentForm($student);
                } else {
                    $form = $this->createStudentForm($student);
                }
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }

    /**
     * createStudentForm
     *
     * 20/08/2020 14:18
     * @param Student $student
     * @return FormInterface
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
     *
     * 20/08/2020 14:18
     * @param Student $student
     * @return FormInterface
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

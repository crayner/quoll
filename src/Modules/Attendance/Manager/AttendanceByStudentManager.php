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
 * Date: 27/10/2020
 * Time: 17:37
 */

namespace App\Modules\Attendance\Manager;

use App\Manager\StatusManager;
use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Attendance\Form\AttendanceByStudentType;
use App\Modules\Student\Entity\Student;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Validator\SchoolDay;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use DateTimeImmutable;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AttendanceByStudentManager
 *
 * 28/10/2020 08:02
 * @package App\Modules\Attendance\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByStudentManager
{
    /**
     * @var Student|null
     */
    private ?Student $student;

    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $date;

    /**
     * @var string
     */
    private string $dailyTime = 'all_day';

    /**
     * @var StatusManager
     */
    private StatusManager $status;

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var FormFactory 
     */
    private  FormFactoryInterface $formFactory;

    /**
     * AttendanceByStudentManager constructor.
     *
     * @param StatusManager $status
     * @param RouterInterface $router
     * @param Security $security
     * @param ValidatorInterface $validator
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(StatusManager $status, RouterInterface $router, Security $security, ValidatorInterface $validator, FormFactoryInterface $formFactory)
    {
        $this->status = $status;
        $this->router = $router;
        $this->security = $security;
        $this->validator = $validator;
        $this->formFactory = $formFactory;
    }

    /**
     * Student
     *
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return isset($this->student) ? $this->student : null;
    }

    /**
     * Student
     *
     * @param Student|null $student
     * @return AttendanceByStudentManager
     */
    public function setStudent(?Student $student): AttendanceByStudentManager
    {
        $this->student = $student;
        return $this;
    }

    /**
     * Date
     *
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return isset($this->date) ? $this->date : null;
    }

    /**
     * Date
     *
     * @param DateTimeImmutable|null $date
     * @return AttendanceByStudentManager
     */
    public function setDate(?DateTimeImmutable $date): AttendanceByStudentManager
    {
        $this->date = $date;
        return $this;
    }

    /**
     * DailyTime
     *
     * @return string
     */
    public function getDailyTime(): string
    {
        return $this->dailyTime;
    }

    /**
     * DailyTime
     *
     * @param string $dailyTime
     * @return AttendanceByStudentManager
     */
    public function setDailyTime(string $dailyTime): AttendanceByStudentManager
    {
        $this->dailyTime = $dailyTime;
        return $this;
    }

    /**
     * handleSubmit
     *
     * 11/11/2020 11:02
     * @param FormInterface $form
     * @param array $content
     * @return FormInterface
     */
    public function handleSubmit(FormInterface $form, array $content)
    {
        $as = $form->getData();

        ProviderFactory::create(AttendanceRollGroup::class)->persistFlush($as->getAttendanceRollGroup());

        $form->submit($content);

        if ($this->getStatus()->isStatusSuccess()) {
            if ($form->isValid()) {
                $as = $form->getData();
                ProviderFactory::create(AttendanceStudent::class)->persistFlush($as);
                if ($this->getStatus()->isStatusSuccess()) {
                    $this->getStatus()->setReDirect($this->getRouter()->generate('attendance_by_student', ['student' => $content['student'], 'date' => $content['date'], 'dailyTime' => $content['dailyTime']]), true);
                } else {
                    $this->getStatus()->databaseError();
                }
            } else {
                $this->getStatus()
                    ->resetStatus()
                    ->invalidInputs();
            }
        } else {
            $this->getStatus()->databaseError();
        }

        return $form;
    }

    /**
     * getStatus
     *
     * 28/10/2020 07:54
     * @return StatusManager
     */
    public function getStatus(): StatusManager
    {
        return $this->status;
    }

    /**
     * Router
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Security
     *
     * @return Security
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }

    /**
     * isSelectionValid
     *
     * 7/11/2020 13:22
     * @param FormInterface $form
     * @param array $content
     * @param Student|null $student
     * @return bool
     */
    public function isSelectionValid(FormInterface $form, array $content, ?Student $student): bool
    {
        $errors = false;
        if ($student !== null && !$this->getSecurity()->isGranted('ROLE_STUDENT_ACCESS', $student)) {
            $form->get('student')->addError(new FormError(TranslationHelper::translate('return.error.student', ['student_name' => $student->getFullName('Formal')],'messages')));
            $errors = true;
        }

        if (key_exists('date', $content)) {
            try {
                $date = new DateTimeImmutable($content['date']);
            } catch (\Exception $e) {
                $date = null;
            }
            $errorList = $this->getValidator()->validate($date, [new NotBlank(), new SchoolDay()]);
            if ($errorList->count() > 0) {
                foreach ($errorList as $error) {
                    $form->get('date')->addError(new FormError($error->getMessage()));
                }
                $errors = true;
            }
        } else {
            $errorList = $this->getValidator()->validate(null, [new NotBlank(), new SchoolDay()]);
            if ($errorList->count() > 0) {
                foreach ($errorList as $error) {
                    $form->get('date')->addError(new FormError($error->getMessage()));
                }
                $errors = true;
            }
        }

        if (key_exists('dailyTime', $content)) {
            $errorList = $this->getValidator()->validate($content['dailyTime'], [new NotBlank(), new Choice(['choices' => AttendanceByRollGroupManager::getDailyTimeList()])]);
            if ($errorList->count() > 0) {
                foreach ($errorList as $error) {
                    $form->get('dailyTime')->addError(new FormError($error->getMessage()));
                }
                $errors = true;
            }
        }

        if ($errors) {
            $this->getStatus()->invalidInputs();
        };

        return !$errors;
    }

    /**
     * Validator
     *
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * FormFactory
     *
     * @return FormFactory
     */
    public function getFormFactory(): FormFactory
    {
        return $this->formFactory;
    }

    /**
     * refreshForm
     *
     * 7/11/2020 14:05
     * @param FormInterface $form
     * @param array $content
     * @param Student|null $student
     * @return FormInterface
     */
    private function refreshForm(FormInterface $form, array $content, ?Student $student): FormInterface
    {
        $as = $form->getData();
        try {
            $as->setStudent($student)
                ->setDate(new DateTimeImmutable($content['date']))
                ->setDailyTime($content['dailyTime']);
        } catch (\Exception $e) {
            $as->setStudent($student)
                ->setDate(new DateTimeImmutable())
                ->setDailyTime($content['dailyTime']);
        }
        return $this->getFormFactory()->create(AttendanceByStudentType::class, $as,
            [
                'action' => UrlGeneratorHelper::getUrl('attendance_by_student', ['student' => $student ? $student->getId() : null, 'date' => $content['date'], 'dailyTime' => $content['dailyTime'] ?: 'all_day']),
                'studentAccess' => $student ? $this->getSecurity()->isGranted('ROLE_STUDENT_ACCESS', $student) : false,
            ]
        );

    }

    /**
     * isSelectionChanged
     *
     * 11/11/2020 10:46
     * @param array $content
     * @param Request $request
     * @return bool
     */
    public function isSelectionChanged(array $content, Request $request): bool
    {
        $params = $request->attributes->get('_route_params');

        foreach ($params as $name=>$value) {
            if ($content[$name] !== $value) {
                $this->getStatus()->warning('A change was made in the attendance selection.  No data has been saved.', [], 'Attendance');
                return true;
            }
        }

        return false;
    }
}

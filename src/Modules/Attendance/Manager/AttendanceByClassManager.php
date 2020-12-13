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
 * Date: 2/10/2020
 * Time: 16:04
 */
namespace App\Modules\Attendance\Manager;

use App\Manager\StatusManager;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceCourseClass;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;

/**
 * Class AttendanceByCode
 * @package App\Modules\Attendance\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByClassManager
{
    /**
     * @var CourseClass|null
     */
    private ?CourseClass $courseClass;

    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $date;

    /**
     * @var Collection|TimetablePeriodClass[]|null
     */
    private ?Collection $periodClasses;

    /**
     * @var Collection|AttendanceCourseClass[]|null
     */
    private ?Collection $attendanceCourseClasses;

    /**
     * @var Collection
     */
    private Collection $students;

    /**
     * AttendanceByRollGroupManager constructor.
     */
    public function __construct()
    {
        TranslationHelper::addTranslation('Present', [], 'Attendance');
        TranslationHelper::addTranslation('Absent', [], 'Attendance');
        TranslationHelper::addTranslation('Save Attendance', [], 'Attendance');
        TranslationHelper::addTranslation('Total students', [], 'Attendance');
        TranslationHelper::addTranslation('Total students present in the room', [], 'Attendance');
        TranslationHelper::addTranslation('Total students absent from the room', [], 'Attendance');
        TranslationHelper::addTranslation('Change All?', [], 'Attendance');
        TranslationHelper::addTranslation('Change all students to these settings', [], 'Attendance');
    }

    /**
     * getCourseClass
     *
     * 12/11/2020 09:10
     * @return CourseClass|null
     */
    public function getCourseClass(): ?CourseClass
    {
        return isset($this->courseClass) ? $this->courseClass : null;
    }

    /**
     * setCourseClass
     *
     * 12/11/2020 09:10
     * @param CourseClass|null $courseClass
     * @return AttendanceByClassManager
     */
    public function setCourseClass(?CourseClass $courseClass): AttendanceByClassManager
    {
        $this->courseClass = $courseClass;
        return $this;
    }

    /**
     * getDate
     *
     * 3/11/2020 13:55
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date = isset($this->date) ? $this->date : new DateTimeImmutable();
    }

    /**
     * setDate
     *
     * 12/11/2020 09:10
     * @param DateTimeImmutable|null $date
     * @return AttendanceByClassManager
     */
    public function setDate(?DateTimeImmutable $date): AttendanceByClassManager
    {
        $this->date = $date;
        return $this;
    }

    /**
     * getPeriodClasses
     *
     * 15/11/2020 10:40
     * @return ArrayCollection|null
     */
    public function getPeriodClasses(): ?ArrayCollection
    {
        if (!isset($this->periodClasses) || is_null($this->periodClasses)) {
            $this->periodClasses = new ArrayCollection(ProviderFactory::getRepository(TimetablePeriodClass::class)->findCourseClassDate($this->getCourseClass(),$this->getDate()));
        }
        return $this->periodClasses;
    }

    /**
     * PeriodClass
     *
     * @param Collection|TimetablePeriodClass[]|null $periodClasses
     * @return AttendanceByClassManager
     */
    public function setPeriodClasses(?Collection $periodClasses): AttendanceByClassManager
    {
        $this->periodClasses = $periodClasses;
        return $this;
    }

    /**
     * countPeriodClasses
     *
     * 17/11/2020 08:47
     * @return int
     */
    public function countPeriodClasses(): int
    {
        if (!$this->getCourseClass()) return 0;

        return ProviderFactory::getRepository(TimetablePeriodClass::class)->countCourseClassDate($this->getCourseClass(),$this->getDate());
    }

    /**
     * findPeriodClasses
     *
     * 15/11/2020 09:42
     * @return ArrayCollection
     */
    public function findPeriodClasses(): ArrayCollection
    {
        return new ArrayCollection(ProviderFactory::getRepository(TimetablePeriodClass::class)->findCourseClassDate($this->getCourseClass(),$this->getDate()));
    }

    /**
     * AttendanceCourseClasses
     *
     * @return Collection|AttendanceCourseClass[]|null
     */
    public function getAttendanceCourseClasses(): ?Collection
    {
        if (!isset($this->attendanceCourseClasses)) $this->attendanceCourseClasses = new ArrayCollection(ProviderFactory::getRepository(AttendanceCourseClass::class)->findBy(['courseClass' => $this->getCourseClass(), 'date' => $this->getDate()]));

        return $this->attendanceCourseClasses;
    }

    /**
     * AttendanceCourseClasses
     *
     * @param Collection|AttendanceCourseClass[]|null $attendanceCourseClasses
     * @return AttendanceByClassManager
     */
    public function setAttendanceCourseClasses(?Collection $attendanceCourseClasses): AttendanceByClassManager
    {
        $this->attendanceCourseClasses = $attendanceCourseClasses;
        return $this;
    }

    /**
     * Students
     *
     * @return Collection
     */
    public function getStudents(): Collection
    {
        if ((!isset($this->students) || $this->students === null) && $this->getAttendanceCourseClasses()->count() > 0) {
            $this->students = new ArrayCollection(ProviderFactory::getRepository(AttendanceStudent::class)->findBy(['attendanceCourseClass' => $this->getAttendanceCourseClasses()->first() ?: null]));
        }

        if ((!isset($this->students) || $this->students === null) && $this->getAttendanceCourseClasses()->count() === 0) {
            $this->getAttendanceCourseClasses()->add(new AttendanceCourseClass($this->getCourseClass(), $this->getDate(), $this->getPeriodClasses()->first() ?: null));
            $this->students = new ArrayCollection();
            foreach ($this->getCourseClass()->getStudents() as $ccs) {
                $als = new AttendanceStudent();
                $als->setAttendanceCourseClass($this->getAttendanceCourseClasses()->first())
                    ->setDate($this->getDate())
                    ->setStudent($ccs->getStudent());
                $this->students->add($als);
            }
        }

        if ($this->getMissingAttendanceTakenCount() > 0) {
            foreach ($this->getCourseClass()->getStudents() as $ccs) {
                $found = false;
                foreach ($this->students as $als) {
                    if ($als->getStudent()->isEqualTo($ccs->getStudent())) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $als = new AttendanceStudent();
                    $als->setAttendanceCourseClasses($this->getAttendanceCourseClasses())
                        ->setDate($this->getDate())
                        ->setStudent($ccs->getStudent());
                    $this->students->add($als);
                }
            }
        }

        return isset($this->students) ? $this->sortStudents() : new ArrayCollection();
    }

    /**
     * setStudents
     *
     * 13/11/2020 09:16
     * @param Collection|null $students
     * @return $this
     */
    public function setStudents(?Collection $students): AttendanceByClassManager
    {
        if ($students instanceof Collection) $this->students = $students;
        return $this;
    }

    /**
     * sortStudents
     *
     * 13/11/2020 09:16
     * @return ArrayCollection
     */
    private function sortStudents(): ArrayCollection
    {
        try {
            $iterator = $this->students->getIterator();

            $iterator->uasort(
                function (AttendanceStudent $a, AttendanceStudent $b) {
                    return $a->getStudent()->getFullNameReversed() < $b->getStudent()->getFullNameReversed() ? -1 : 1 ;
                }
            );

            $this->students  = new ArrayCollection(iterator_to_array($iterator, false));
        } catch (\Exception $e) {
        }

        return $this->students;
    }

    /**
     * isSelectionValid
     *
     * 12/11/2020 09:43
     * @return bool
     */
    public function isSelectionValid(): bool
    {
        if ($this->getCourseClass() === null) return false;
        if ($this->getDate() === null) return false;
        return true;
    }

    /**
     * isClassDate
     *
     * 12/11/2020 11:28
     * @return bool
     */
    public function isClassDate(): bool
    {
        return $this->countPeriodClasses() > 0;
    }

    /**
     * isAttendanceTaken
     *
     * 12/11/2020 11:42
     * @return bool
     */
    public function isAttendanceTaken(): bool
    {
        return $this->getAttendanceCourseClasses()->count() > 0;
    }

    /**
     * isAllAttendanceTaken
     *
     * 19/11/2020 08:44
     * @return bool
     */
    public function isAllAttendanceTaken(): bool
    {
        return $this->getAttendanceCourseClasses()->count() === $this->getPeriodClasses()->count();
    }

    /**
     * getAttendanceHistory
     *
     * 19/11/2020 08:52
     * @return array
     */
    public function getAttendanceHistory(): array
    {
        $instances = [];
        foreach (ProviderFactory::getRepository(AttendanceCourseClass::class)->findByCourseClassDateHistory($this->getCourseClass(),$this->getDate()) as $item) {
            $instances[] = TranslationHelper::translate('course_class_recorder_details', ['recorded_on' => $item['recordedOn']->format('D jS M/Y H:i:s'), '_period_' => $item['period'], '_recorder_' => $item['recorder']], 'Attendance');
        }
        return [
            'course_class' => $this->getCourseClass()->getFullName(),
            '_date_' => $this->getDate()->format('D jS M/Y'),
            '_periods_' => '<li>'. implode('</li><li>', $instances) . '</li>',
        ];
    }

    /**
     * isSelectionChanged
     *
     * 12/11/2020 12:36
     * @param array $routeParams
     * @return bool
     */
    public function isSelectionChanged(array $routeParams): bool
    {
        if (key_exists('courseClass', $routeParams) && $routeParams['courseClass'] !== ($this->getCourseClass() ? $this->getCourseClass()->getId() : null)) return true;
        if ((!key_exists('courseClass', $routeParams) || $routeParams['courseClass'] === '') && $this->getCourseClass() instanceof CourseClass) return true;
        if (key_exists('date', $routeParams) && $routeParams['date'] !== ($this->getDate() ? $this->getDate()->format('Y-m-d') : null)) return true;
        if ((!key_exists('date', $routeParams) || $routeParams['date'] === '') && $this->getDate() instanceof DateTimeImmutable) return true;
        return false;
    }

    /**
     * getMissingAttendanceTakenCount
     *
     * 13/11/2020 09:17
     * @return int
     */
    public function getMissingAttendanceTakenCount(): int
    {
        return ($this->getCourseClass() ? $this->getCourseClass()->getStudentCount() : 0) - count(ProviderFactory::getRepository(AttendanceStudent::class)->findBy(['attendanceCourseClass' => $this->getAttendanceCourseClasses()->first() ?: null]));
    }

    /**
     * isSchoolDate
     *
     * 14/11/2020 07:44
     * @return bool
     */
    public function isSchoolDate(): bool
    {
        return ProviderFactory::getRepository(TimetableDate::class)->isSchoolOpen($this->getDate());
    }

    /**
     * handleForm
     *
     * 16/11/2020 15:23
     * @param FormInterface $form
     * @param StatusManager $statusManager
     */
    public function handleForm(FormInterface $form, StatusManager $statusManager)
    {
        $existingPeriodClasses = new ArrayCollection(ProviderFactory::getRepository(TimetablePeriodClass::class)->findCourseClassDate($this->getCourseClass(),$this->getDate()));
        $periodClasses = [null];

        if ($form->get('periodClasses')->getData() === null) {
            $this->setPeriodClasses($existingPeriodClasses);
        } else if ($form->get('periodClasses')->getData()->count() === 0 && $existingPeriodClasses->count() > 0) {
            $this->setPeriodClasses($existingPeriodClasses);
            $periodClasses = $form->get('periodClasses')->getData();
            $periodClasses = $periodClasses->count() > 0 ?: [null];
        } else if ($form->get('periodClasses')->getData()->count() > 0) {
            $periodClasses = $form->get('periodClasses')->getData();
        }

        $defaultCode = ProviderFactory::getRepository(AttendanceCode::class)->findOneByName(SettingFactory::getSettingManager()->get('Attendance', 'defaultClassAttendanceType', 'Present'));

        foreach ($periodClasses as $q=>$periodClass) {
            $acc = $this->getAttendanceCourseClasses()[$q] ?: $this->createAttendanceCourseClass($periodClass);
            if ($acc->getId() === null) ProviderFactory::create(AttendanceCourseClass::class)->persist($acc);

            if ($statusManager->isStatusSuccess()) {
                foreach ($this->getStudents() as $student) {
                    $attendStudent = ProviderFactory::getRepository(AttendanceStudent::class)->findOneBy(
                        [
                            'student' => $student->getStudent(),
                            'attendanceCourseClass' => $acc,
                            'date' => $this->getDate()
                        ]
                    ) ?: new AttendanceStudent();
                    $attendStudent
                        ->setStudent($student->getStudent())
                        ->setDate($student->getDate())
                        ->setCode($student->getCode() ?: $defaultCode)
                        ->setDailyTime('not_used')
                        ->setContext('Class')
                        ->setAttendanceCourseClass($acc);
                    ProviderFactory::create(AttendanceStudent::class)->persist($attendStudent);
                }
                ProviderFactory::create(AttendanceStudent::class)->flush(false);
            }
        }
        if ($statusManager->isStatusSuccess()) $statusManager->success();
    }

    /**
     * createAttendanceCourseClass
     *
     * 19/11/2020 10:42
     * @param TimetablePeriodClass $periodClass
     * @return AttendanceCourseClass
     */
    private function createAttendanceCourseClass(TimetablePeriodClass $periodClass): AttendanceCourseClass
    {
        return new AttendanceCourseClass($this->getCourseClass(), $this->getDate(), $periodClass);
    }

    /**
     * getPeriodChoices
     *
     * 20/11/2020 15:35
     * @return array
     */
    public function getPeriodChoices(): array
    {
        $choices = new ArrayCollection();
        foreach ($this->getPeriodClasses() as $q=>$class) {
            $choices->add(['id' => $class->getId(), 'label' => $class->getPeriodName(),'value' => $q]);
        }
        return $choices->toArray();
    }
}

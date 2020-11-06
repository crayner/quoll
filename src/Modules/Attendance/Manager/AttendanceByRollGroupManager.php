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
 * Date: 17/10/2020
 * Time: 08:21
 */
namespace App\Modules\Attendance\Manager;

use App\Manager\StatusManager;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Manager\Hidden\Day;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;

/**
 * Class AttendanceByRollGroupManager
 *
 * 17/10/2020 08:23
 * @package App\Modules\Attendance\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByRollGroupManager
{
    /**
     * @var RollGroup|null
     */
    private ?RollGroup $rollGroup;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $date;

    /**
     * @var string
     */
    private string $dailyTime = 'all_day';

    /**
     * @var Collection
     */
    private Collection $students;

    /**
     * @var array
     */
    private array $content;

    /**
     * @var array
     */
    private array $previousDays;

    /**
     * @var array
     */
    private array $statusMessage;

    /**
     * @var AttendanceRollGroup|null
     */
    private ?AttendanceRollGroup $AttendanceRollGroup;

    /**
     * @var Security
     */
    private Security $security;

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
     * getRollGroup
     *
     * 17/10/2020 08:24
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return isset($this->rollGroup) ? $this->rollGroup : null;
    }

    /**
     * setRollGroup
     *
     * 17/10/2020 09:20
     * @param RollGroup|null $rollGroup
     * @return $this
     */
    public function setRollGroup(?RollGroup $rollGroup): AttendanceByRollGroupManager
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * getDate
     *
     * 17/10/2020 08:24
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return isset($this->date) ? $this->date : new DateTimeImmutable();
    }

    /**
     * setDate
     *
     * 17/10/2020 08:24
     * @param DateTimeImmutable|null $date
     * @return $this
     */
    public function setDate(?DateTimeImmutable $date): AttendanceByRollGroupManager
    {
        $this->date = $date ?: new DateTimeImmutable();
        return $this;
    }

    /**
     * getDailyTime
     *
     * 23/10/2020 11:53
     * @return string
     */
    public function getDailyTime(): string
    {
        return isset($this->dailyTime) ? $this->dailyTime : 'all_day';
    }

    /**
     * setTime
     *
     * 23/10/2020 11:53
     * @param string|null $dailyTime
     * @return $this
     */
    public function setDailyTime(?string $dailyTime): AttendanceByRollGroupManager
    {
        $this->dailyTime = $dailyTime ?: 'all_day';
        return $this;
    }

    /**
     * getDailyTimeList
     *
     * 23/10/2020 11:59
     * @return array
     */
    public static function getDailyTimeList(): array
    {
        return array_values(SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']));
    }

    /**
     * isValid
     *
     * 25/10/2020 07:21
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->getDate() === null || $this->getRollGroup() === null) return false;

        if (!AcademicYearHelper::isDateInCurrentYear($this->getDate())) return false;

        if ($this->getRollGroup()->getStudentCount() === 0) return false;

        if ($this->getSecurity() && !$this->getSecurity()->isGranted('ROLE_ROLL_GROUP', $this->getRollGroup())) return false;

        if (count(self::getDailyTimeList()) > 1 && $this->getDailyTime() === null) return false;

        $day = new Day($this->getDate());
        if (!$day->isSchoolOpen()) return $this->setStatusMessage('not_a_school_day');

        if ($this->getAttendanceRollGroup() === null || $this->getAttendanceRollGroup()->getId() === null) {
            $this->setStatusMessage('no_attendance_recorded');
            return true;
        }

        if ($this->getMissingAttendanceTakenCount() === 0) {
            $this->setStatusMessage('attendance_recorded');
        } else {
            $this->setStatusMessage('partial_attendance_recorded');
        }


        return true;
    }

    /**
     * getStatusMessage
     *
     * 22/10/2020 13:03
     * @return array
     */
    public function getStatusMessage(): array
    {
        return isset($this->statusMessage) ? $this->statusMessage : ['status' => 'hidden', 'message' => ''];
    }

    /**
     * setStatusMessage
     *
     * 22/10/2020 13:03
     * @param string $message
     * @return bool
     */
    public function setStatusMessage(string $message): bool
    {
        switch ($message) {
            case 'no_attendance_recorded':
            case 'partial_attendance_recorded':
            case 'not_a_school_day':
                $this->statusMessage = [
                    'status' => 'warning',
                    'message' => $message,
                ];
                break;
            case 'attendance_recorded':
                $this->statusMessage = [
                    'status' => 'success',
                    'message' => $message,
                ];
                break;
            default:
                $this->statusMessage = [
                    'status' => 'hidden',
                    'message' => '',
                ];
        }
        return false;
    }

    /**
     * generateContent
     *
     * 19/10/2020 11:36
     * @return array
     */
    public function generateContent(): array
    {
        if (!isset($this->content)) {
            $students = [];
            foreach (ProviderFactory::getRepository(Student::class)->findByRollGroup($this->getRollGroup()) as $student) {
                $students[$student['id']] = $student;
            }
            $studentList = array_keys($students);

            // grab student attendance loo for date/time/rollGroup
            $result = ProviderFactory::getRepository(AttendanceStudent::class)->findByRollGroupDateDailyTimeStudent($this->getRollGroup(), $this->getDate(), $studentList, $this->getDailyTime());
            foreach ($result as $student) {
                $id = $student['student'];
                $students[$id] = array_merge($student, $students[$id]);
            }
            $result = ProviderFactory::getRepository(AttendanceStudent::class)->countOutByRollGroupDateDailyTimeStudent($this->getRollGroup(), $this->getDate(), $studentList, $this->getDailyTime());
            foreach ($result as $student) {
                $id = $student['student'];
                $students[$id] = array_merge($student, $students[$id]);
            }

            foreach ($students as $id => $student) {
                foreach ($this->getPreviousDays() as $time) {
                    foreach ($time['days'] as $day) {
                        $detail = ProviderFactory::getRepository(AttendanceStudent::class)->findOneByStudentDateRollGroupDailyTime($id, $day['date'], $this->getRollGroup(), $time['name']);
                        $name = empty($time['name']) ? '' : $time['name'];
                        if ($detail === null) {
                            $students[$id][$name][$day['date']]['className'] = 'highlightNoData';
                            $students[$id][$name][$day['date']]['status'] = '';
                        } else {
                            $students[$id][$name][$day['date']]['className'] = $detail->getCode()->getDirection() === 'In' ? 'highlightPresent' : 'highlightAbsent';
                            $students[$id][$name][$day['date']]['status'] = $detail->getCode()->getName();
                        }
                    }
                }
            }

            $this->content = array_values($students);
        }
        return $this->content;
    }

    /**
     * getAttendanceCodes
     *
     * 20/10/2020 14:35
     * @return array
     */
    public function getAttendanceCodes(): array
    {
        $result = [];
        foreach (ProviderFactory::getRepository(AttendanceCode::class)->findBy(['active' => true], ['sortOrder' => 'ASC']) as $code) {
            $result[$code->getId()] = $code->getName();
        }
        return $result;
    }

    /**
     * getReasons
     *
     * 20/10/2020 15:35
     * @return array
     */
    public function getReasons(): array
    {
        return SettingFactory::getSettingManager()->get('Attendance', 'attendanceReasons');
    }

    /**
     * getPreviousDays
     *
     * 22/10/2020 08:59
     * @return array
     */
    public function getPreviousDays()
    {
        if (!isset($this->previousDays)) {
            $times = self::getDailyTimeList();
            $days = AcademicYearHelper::getPreviousSchoolDays(clone $this->getDate());
            $result = [];

            foreach ($times as $w) {
                $x['name'] = $w;
                $x['days'] = $days;
                $result[] = $x;
            }

            $this->previousDays = $result;
        }
        return $this->previousDays;
    }

    /**
     * getAttendanceRollGroup
     *
     * 24/10/2020 15:03
     * @return AttendanceRollGroup|null
     */
    public function getAttendanceRollGroup(): ?AttendanceRollGroup
    {
        return $this->AttendanceRollGroup = isset($this->AttendanceRollGroup) ? $this->AttendanceRollGroup : ProviderFactory::getRepository(AttendanceRollGroup::class)->findOneBy(['rollGroup' => $this->getRollGroup(), 'date' => $this->getDate(), 'dailyTime' => $this->getDailyTime()]);
    }

    /**
     * getRecorderLogs
     *
     * 27/10/2020 09:45
     * @return ArrayCollection
     */
    public function getRecorderLogs(): ArrayCollection
    {
        return $this->getAttendanceRollGroup() ? $this->getAttendanceRollGroup()->getRecorderLogs() : new ArrayCollection();
    }

    /**
     * getCreatorName
     *
     * 24/10/2020 15:29
     * @return string
     */
    public function getCreatorName(): string
    {
        return $this->getAttendanceRollGroup() && $this->getAttendanceRollGroup()->getCreator() ? $this->getAttendanceRollGroup()->getCreator()->getFullName() : '';
    }

    /**
     * getCreationDate
     *
     * 24/10/2020 15:13
     * @return DateTimeImmutable|null
     */
    public function getCreationDate(): ?DateTimeImmutable
    {
        return $this->getAttendanceRollGroup() && $this->getAttendanceRollGroup()->getCreationDate() ? $this->getAttendanceRollGroup()->getCreationDate() : null;
    }

    /**
     * getRecordedDate
     *
     * 24/10/2020 15:21
     * @return DateTimeImmutable|null
     */
    public function getRecordedDate(): ?DateTimeImmutable
    {
        return $this->getAttendanceRollGroup() && $this->getAttendanceRollGroup()->getRecordedDate() ? $this->getAttendanceRollGroup()->getRecordedDate() : null;
    }

    /**
     * getMissingAttendanceTakenCount
     *
     * 26/10/2020 15:10
     * @return int
     */
    public function getMissingAttendanceTakenCount(): int
    {
        return ($this->getRollGroup() ? $this->getRollGroup()->getStudentCount() : 0) - count(ProviderFactory::getRepository(AttendanceStudent::class)->findBy(['attendanceRollGroup' => $this->getAttendanceRollGroup()]));
    }

    /**
     * getStudents
     *
     * 25/10/2020 10:06
     * @return Collection
     */
    public function getStudents(): Collection
    {
        if ((!isset($this->students) || $this->students === null) && $this->getAttendanceRollGroup() !== null) {

            $this->students = new ArrayCollection(ProviderFactory::getRepository(AttendanceStudent::class)->findBy(['attendanceRollGroup' => $this->getAttendanceRollGroup()]));

        }

        if ((!isset($this->students) || $this->students === null) && $this->getAttendanceRollGroup() === null) {
            $this->AttendanceRollGroup = new AttendanceRollGroup($this->getRollGroup(),$this->getDate(),$this->getDailyTime());
            $this->students = new ArrayCollection();
            foreach ($this->getRollGroup()->getStudentRollGroups() as $srg) {
                $als = new AttendanceStudent();
                $als->setAttendanceRollGroup($this->getAttendanceRollGroup())
                    ->setDailyTime($this->getDailyTime())
                    ->setDate($this->getDate())
                    ->setStudent($srg->getStudent());
                $this->students->add($als);
            }
        }

        if ($this->getMissingAttendanceTakenCount() > 0) {
            foreach ($this->getRollGroup()->getStudentRollGroups() as $srg) {
                $found = false;
                foreach ($this->students->toArray() as $als) {
                    if ($als->getStudent()->isEqualTo($srg->getStudent())) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $als = new AttendanceStudent();
                    $als->setAttendanceRollGroup($this->getAttendanceRollGroup())
                        ->setDailyTime($this->getDailyTime())
                        ->setDate($this->getDate())
                        ->setStudent($srg->getStudent());
                    $this->students->add($als);
                }
            }
        }

        return isset($this->students) ? $this->sortStudents() : new ArrayCollection();
    }

    /**
     * sortStudents
     *
     * 26/10/2020 09:00
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
     * setStudents
     *
     * 25/10/2020 08:42
     * @param Collection|null $students
     * @return AttendanceByRollGroupManager
     */
    public function setStudents(?Collection $students): AttendanceByRollGroupManager
    {
        if ($students instanceof Collection) $this->students = $students;
        return $this;
    }

    /**
     * requestEqualsSubmit
     *
     * 25/10/2020 14:23
     * @param array $params
     * @return bool
     */
    public function requestEqualsSubmit(array $params): bool
    {
        if ($this->getRollGroup()->getId() !== $params['rollGroup']) return false;

        if ($this->getDailyTime() !== $params['dailyTime']) return false;

        if ($this->getDate()->format('Y-m-d') !== $params['date']) return false;
        return true;
    }

    /**
     * storeAttendance
     *
     * 30/10/2020 12:43
     * @param array $post
     * @param bool $autoFill
     */
    public function storeAttendance(array $post, bool $autoFill)
    {
        $staff = SecurityHelper::getCurrentUser()->getStaff();
        ProviderFactory::getEntityManager()->refresh($staff);
        $alrgs = [];
        $alrgs[$this->getDailyTime()] = ProviderFactory::getRepository(AttendanceRollGroup::class)->findOneBy(['rollGroup' => $this->getRollGroup(), 'date' => $this->getDate(), 'dailyTime' => $this->getDailyTime()]) ?: new AttendanceRollGroup($this->getRollGroup(), $this->getDate(), $this->getDailyTime());
        $defaultCode = ProviderFactory::getRepository(AttendanceCode::class)->findOneByDefaultCode(true);
        if ($alrgs[$this->getDailyTime()]->getId() === null) ProviderFactory::create(AttendanceRollGroup::class)->persist($alrgs[$this->getDailyTime()]);
        $codes = [];
        $dailyTimes = SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']);
        foreach ($post['students'] as $student) {
            $student['code'] = $student['code'] === '' ? $defaultCode->getId() : $student['code'];
            $codes[$student['code']] = key_exists($student['code'],$codes) ? $codes[$student['code']] : ProviderFactory::getRepository(AttendanceCode::class)->find($student['code']);
            $studentEntity = ProviderFactory::getRepository(Student::class)->find($student['student']);
            $als = ProviderFactory::getRepository(AttendanceStudent::class)->findOneBy(['dailyTime' => $this->getDailyTime(), 'date' => $this->getDate(), 'attendanceRollGroup' => $alrgs[$this->getDailyTime()], 'student' => $studentEntity]) ?: new AttendanceStudent();
            $als->setStudent($studentEntity)
                ->setAttendanceRollGroup($alrgs[$this->getDailyTime()])
                ->setCode($codes[$student['code']])
                ->setDate($this->getDate())
                ->setDailyTime($this->getDailyTime())
                ->setComment($student['comment'])
                ->setReason($student['reason'])
                ->setContext('Roll Group');
            ProviderFactory::create(AttendanceStudent::class)->persist($als);
            if ($autoFill && count($dailyTimes) > 1) {
                foreach ($dailyTimes as $dailyTime) {
                    if ($dailyTime === $this->getDailyTime()) continue;
                    $alrgs[$dailyTime] = key_exists($dailyTime, $alrgs) ? $alrgs[$dailyTime] : (ProviderFactory::getRepository(AttendanceRollGroup::class)->findOneBy(['rollGroup' => $this->getRollGroup(), 'date' => $this->getDate(), 'dailyTime' => $dailyTime]) ?: new AttendanceRollGroup($this->getRollGroup(), $this->getDate(), $dailyTime));
                    if ($alrgs[$dailyTime]->getId() === null) ProviderFactory::create(AttendanceRollGroup::class)->persist($alrgs[$dailyTime]);
                    $als = ProviderFactory::getRepository(AttendanceStudent::class)->findOneBy(['dailyTime' => $dailyTime, 'date' => $this->getDate(), 'attendanceRollGroup' => $alrgs[$dailyTime], 'student' => $studentEntity]) ?: new AttendanceStudent();
                    if ($als->getId() === null) {
                        $als->setStudent($studentEntity)
                            ->setAttendanceRollGroup($alrgs[$dailyTime])
                            ->setCode($codes[$student['code']])
                            ->setDate($this->getDate())
                            ->setDailyTime($dailyTime)
                            ->setComment($student['comment'])
                            ->setReason($student['reason'])
                            ->setContext('Roll Group');
                        ProviderFactory::create(AttendanceStudent::class)->persist($als);
                    }
                }
            }
        }
        ProviderFactory::create(AttendanceStudent::class)->flush();
    }

    /**
     * changeAll
     *
     * 29/10/2020 08:22
     * @param array $data
     * @param StatusManager $statusManager
     */
    public function changeAll(array $data, StatusManager $statusManager)
    {
        $count = 0;
        foreach ($this->getStudents() as $student) {
            if ($student->getId() === null) {
                $statusManager->warning('The attendance for student could not be changed as it not been recorded.', ['student' => $student->getStudent()->getFullName()], 'Attendance');
                continue;
            }
            if (!empty($data['code'])) $student->setCode($data['code']);
            $student
                ->setComment(empty($data['comment']) ? null : $data['comment'])
                ->setReason(empty($data['reason']) ? null : $data['reason']);
            $count++;
            ProviderFactory::create(AttendanceStudent::class)->persist($student);
        }
        if ($count === $this->getStudents()->count()) {
            $statusManager->success('roll_group_change_all', ['count' => $count, 'student_number' => $this->getStudents()->count()], 'Attendance');
        } else {
            $statusManager->warning('roll_group_change_all', ['count' => $count, 'student_number' => $this->getStudents()->count()], 'Attendance');
        }

        ProviderFactory::create(AttendanceStudent::class)->flush();
    }

    /**
     * getSecurity
     *
     * 6/11/2020 10:27
     * @return Security|null
     */
    public function getSecurity(): ?Security
    {
        return isset($this->security) ? $this->security : null;
    }

    /**
     * Security
     *
     * @param Security $security
     * @return AttendanceByRollGroupManager
     */
    public function setSecurity(Security $security): AttendanceByRollGroupManager
    {
        $this->security = $security;
        return $this;
    }
}

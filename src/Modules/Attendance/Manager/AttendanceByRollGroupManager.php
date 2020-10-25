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

use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceLogRollGroup;
use App\Modules\Attendance\Entity\AttendanceLogStudent;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Manager\Hidden\Day;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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
     * @var CsrfTokenManagerInterface|null
     */
    private ?CsrfTokenManagerInterface $csrfTokenManager;

    /**
     * @var AttendanceLogRollGroup|null
     */
    private ?AttendanceLogRollGroup $attendanceLogRollGroup;

    /**
     * AttendanceByRollGroupManager constructor.
     *
     * @param CsrfTokenManagerInterface|null $csrfTokenManager
     */
    public function __construct(?CsrfTokenManagerInterface $csrfTokenManager = null)
    {
        $this->csrfTokenManager = $csrfTokenManager;
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

        if (count(self::getDailyTimeList()) > 1 && $this->getDailyTime() === null) return false;

        $day = new Day($this->getDate());
        if (!$day->isSchoolOpen()) return $this->setStatusMessage('not_a_school_day');

        if ($this->getAttendanceLogRollGroup() === null) {
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
            $result = ProviderFactory::getRepository(AttendanceLogStudent::class)->findByRollGroupDateDailyTimeStudent($this->getRollGroup(), $this->getDate(), $studentList, $this->getDailyTime());
            foreach ($result as $student) {
                $id = $student['student'];
                $students[$id] = array_merge($student, $students[$id]);
            }
            $result = ProviderFactory::getRepository(AttendanceLogStudent::class)->countOutByRollGroupDateDailyTimeStudent($this->getRollGroup(), $this->getDate(), $studentList, $this->getDailyTime());
            foreach ($result as $student) {
                $id = $student['student'];
                $students[$id] = array_merge($student, $students[$id]);
            }

            foreach ($students as $id => $student) {
                foreach ($this->getPreviousDays() as $time) {
                    foreach ($time['days'] as $day) {
                        $detail = ProviderFactory::getRepository(AttendanceLogStudent::class)->findOneByStudentDateRollGroupDailyTime($id, $day['date'], $this->getRollGroup(), $time['name']);
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
     * CsrfTokenManager
     *
     * @return CsrfTokenManagerInterface
     */
    public function getCsrfTokenManager(): CsrfTokenManagerInterface
    {
        return $this->csrfTokenManager;
    }

    /**
     * getCsrfToken
     *
     * 23/10/2020 09:50
     * @return CsrfToken
     */
    public function getCsrfToken(): CsrfToken
    {
        return $this->getCsrfTokenManager()->getToken('attendance_roll_group');
    }

    /**
     * getAttendanceLogRollGroup
     *
     * 24/10/2020 15:03
     * @return AttendanceLogRollGroup|null
     */
    public function getAttendanceLogRollGroup(): ?AttendanceLogRollGroup
    {
        return $this->attendanceLogRollGroup = isset($this->attendanceLogRollGroup) ? $this->attendanceLogRollGroup : ProviderFactory::getRepository(AttendanceLogRollGroup::class)->findOneBy(['rollGroup' => $this->getRollGroup(), 'date' => $this->getDate(), 'dailyTime' => $this->getDailyTime()]);
    }

    /**
     * getRecorderName
     *
     * 24/10/2020 15:29
     * @return string
     */
    public function getRecorderName(): string
    {
        return $this->getAttendanceLogRollGroup() ? $this->getAttendanceLogRollGroup()->getRecorder()->getFullName() : '';
    }

    /**
     * getCreatorName
     *
     * 24/10/2020 15:29
     * @return string
     */
    public function getCreatorName(): string
    {
        return $this->getAttendanceLogRollGroup() ? $this->getAttendanceLogRollGroup()->getCreator()->getFullName() : '';
    }

    /**
     * getCreationDate
     *
     * 24/10/2020 15:13
     * @return DateTimeImmutable|null
     */
    public function getCreationDate(): ?DateTimeImmutable
    {
        return $this->getAttendanceLogRollGroup() ? $this->getAttendanceLogRollGroup()->getCreationDate() : null;
    }

    /**
     * getRecordedDate
     *
     * 24/10/2020 15:21
     * @return DateTimeImmutable|null
     */
    public function getRecordedDate(): ?DateTimeImmutable
    {
        return $this->getAttendanceLogRollGroup() ? $this->getAttendanceLogRollGroup()->getRecordedDate() : null;
    }

    /**
     * getMissingAttendanceTakenCount
     *
     * 24/10/2020 15:57
     * @return int
     */
    public function getMissingAttendanceTakenCount(): int
    {
        return $this->getRollGroup()->getStudentCount() - count(ProviderFactory::getRepository(AttendanceLogStudent::class)->findBy(['attendanceRollGroup' => $this->getAttendanceLogRollGroup()]));
    }

    /**
     * getStudents
     *
     * 25/10/2020 10:06
     * @return Collection
     */
    public function getStudents(): Collection
    {
        if (!isset($this->students) && $this->getAttendanceLogRollGroup() !== null) {

            $this->students = new ArrayCollection(ProviderFactory::getRepository(AttendanceLogStudent::class)->findBy(['attendanceRollGroup' => $this->getAttendanceLogRollGroup()]));

            try {
                $iterator = $this->students->getIterator();

                $iterator->uasort(
                    function (AttendanceLogStudent $a, AttendanceLogStudent $b) {
                        return $a->getStudent()->getFullNameReversed() < $b->getStudent()->getFullNameReversed() ? -1 : 1 ;
                    }
                );

                $this->students  = new ArrayCollection(iterator_to_array($iterator, false));
            } catch (\Exception $e) {
            }
        }
        return isset($this->students) ? $this->students : new ArrayCollection();
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
}

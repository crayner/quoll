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
 * Date: 19/10/2020
 * Time: 12:22
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Attendance\Validator\AttendanceLogTime;
use App\Modules\Attendance\Validator\Reason;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttendanceLogStudent
 *
 * 19/10/2020 12:23
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceLogStudentRepository")
 * @ORM\Table(name="AttendanceLogStudent",
 *     indexes={
 *      @ORM\Index(name="student",columns={"student"}),
 *      @ORM\Index(name="recorder",columns={"recorder"}),
 *      @ORM\Index(name="attendance_code",columns={"attendance_code"}),
 *      @ORM\Index(name="attendance_course_class",columns={"attendance_course_class"}),
 *      @ORM\Index(name="attendance_roll_group",columns={"attendance_roll_group"})
 *     },
 * )
 * @ORM\HasLifecycleCallbacks()
 * @AttendanceLogTime()
 * @\App\Modules\Attendance\Validator\AttendanceLogStudent()
 */
class AttendanceLogStudent extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var AttendanceCode|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Attendance\Entity\AttendanceCode")
     * @ORM\JoinColumn(name="attendance_code",nullable=false)
     * @Assert\NotBlank()
     */
    private ?AttendanceCode $code;

    /**
     * @var Student|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\Student")
     * @ORM\JoinColumn(name="student",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Student $student;

    /**
     * @var string|null
     * @ORM\Column(length=32,nullable=true)
     * @Reason()
     */
    private ?string $reason;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\Choice(callback="getContextList")
     */
    private ?string $context;

    /**
     * @var array|string[]
     */
    private static array $contextList = [
        'Roll Group',
        'Class',
        'Person',
        'Future',
        'Self Registration'
    ];

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private ?string $comment;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=false)
     * @Assert\NotBlank()
     */
    private ?DateTimeImmutable $date;

    /**
     * @var string|null
     * @ORM\Column(length=32,nullable=false,name="daily_time")
     * @Assert\Choice(callback="getDailyTimeList")
     */
    private string $dailyTime = 'all_day';

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(nullable=false,name="recorder")
     * @Assert\NotBlank()
     */
    private ?Staff $recorder;

    /**
     * @var AttendanceLogRollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Attendance\Entity\AttendanceLogRollGroup")
     * @ORM\JoinColumn(nullable=true,name="attendance_roll_group")
     */
    private ?AttendanceLogRollGroup $attendanceRollGroup;

    /**
     * @var AttendanceLogClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Attendance\Entity\AttendanceLogClass")
     * @ORM\JoinColumn(nullable=true,name="attendance_course_class")
     */
    private ?AttendanceLogClass $attendanceClass;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable",nullable=false,name="creation_date")
     */
    private DateTimeImmutable $creationDate;

    /**
     * Id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * Id
     *
     * @param string|null $id
     * @return AttendanceLogStudent
     */
    public function setId(?string $id): AttendanceLogStudent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Code
     *
     * @return AttendanceCode|null
     */
    public function getCode(): ?AttendanceCode
    {
        return $this->code;
    }

    /**
     * Code
     *
     * @param AttendanceCode|null $code
     * @return AttendanceLogStudent
     */
    public function setCode(?AttendanceCode $code): AttendanceLogStudent
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Student
     *
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * Student
     *
     * @param Student|null $student
     * @return AttendanceLogStudent
     */
    public function setStudent(?Student $student): AttendanceLogStudent
    {
        $this->student = $student;
        return $this;
    }

    /**
     * Reason
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return isset($this->reason) ? $this->reason : null;
    }

    /**
     * Reason
     *
     * @param string|null $reason
     * @return AttendanceLogStudent
     */
    public function setReason(?string $reason): AttendanceLogStudent
    {
        $this->reason = in_array($reason, self::getReasonList()) ? $reason : null;
        return $this;
    }

    /**
     * getReasonList
     *
     * 19/10/2020 16:20
     * @return array
     */
    public static function getReasonList(): array
    {
        return array_values(SettingFactory::getSettingManager()->get('Attendance', 'attendanceReasons'));
    }

    /**
     * Context
     *
     * @return string|null
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Context
     *
     * @param string|null $context
     * @return AttendanceLogStudent
     */
    public function setContext(?string $context): AttendanceLogStudent
    {
        $this->context = $context;
        return $this;
    }

    /**
     * ContextList
     *
     * @return array|string[]
     */
    public static function getContextList()
    {
        return self::$contextList;
    }

    /**
     * @param array|string[] $contextList
     */
    public static function setContextList($contextList): void
    {
        self::$contextList = $contextList;
    }

    /**
     * Comment
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return isset($this->comment) ? $this->comment : null;
    }

    /**
     * Comment
     *
     * @param string|null $comment
     * @return AttendanceLogStudent
     */
    public function setComment(?string $comment): AttendanceLogStudent
    {
        $this->comment = !empty($comment) ? $comment : null;
        return $this;
    }

    /**
     * Date
     *
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Date
     *
     * @param DateTimeImmutable|null $date
     * @return AttendanceLogStudent
     */
    public function setDate(?DateTimeImmutable $date): AttendanceLogStudent
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
        return $this->dailyTime = $this->dailyTime ?: 'all_day';
    }

    /**
     * DailyTime
     *
     * @param string|null $dailyTime
     * @return AttendanceLogStudent
     */
    public function setDailyTime(?string $dailyTime): AttendanceLogStudent
    {
        $this->dailyTime = $dailyTime;
        return $this;
    }

    /**
     * getDailyTimeList
     *
     * 23/10/2020 12:28
     * @return array
     */
    public function getDailyTimeList(): array
    {
        return array_values(SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']));
    }

    /**
     * Recorder
     *
     * @return Staff|null
     */
    public function getRecorder(): ?Staff
    {
        return $this->recorder;
    }

    /**
     * Recorder
     *
     * @param Staff|null $recorder
     * @return AttendanceLogStudent
     */
    public function setRecorder(?Staff $recorder): AttendanceLogStudent
    {
        $this->recorder = $recorder;
        return $this;
    }

    /**
     * getAttendanceRollGroup
     *
     * 19/10/2020 13:09
     * @return AttendanceLogRollGroup|null
     */
    public function getAttendanceRollGroup(): ?AttendanceLogRollGroup
    {
        return isset($this->attendanceRollGroup) ? $this->attendanceRollGroup : null;
    }

    /**
     * AttendanceRollGroup
     *
     * @param AttendanceLogRollGroup|null $attendanceRollGroup
     * @return AttendanceLogStudent
     */
    public function setAttendanceRollGroup(?AttendanceLogRollGroup $attendanceRollGroup): AttendanceLogStudent
    {
        $this->attendanceRollGroup = $attendanceRollGroup;
        return $this;
    }

    /**
     * getAttendanceClass
     *
     * 19/10/2020 13:09
     * @return AttendanceLogClass|null
     */
    public function getAttendanceClass(): ?AttendanceLogClass
    {
        return isset($this->attendanceClass) ? $this->attendanceClass : null;
    }

    /**
     * AttendanceClass
     *
     * @param AttendanceLogClass|null $attendanceClass
     * @return AttendanceLogStudent
     */
    public function setAttendanceClass(?AttendanceLogClass $attendanceClass): AttendanceLogStudent
    {
        $this->attendanceClass = $attendanceClass;
        return $this;
    }

    /**
     * CreationDate
     *
     * @ORM\PrePersist()
     * @return DateTimeImmutable
     */
    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creationDate = isset($this->creationDate) ? $this->creationDate : new DateTimeImmutable();
    }

    /**
     * CreationDate
     *
     * @param DateTimeImmutable $creationDate
     * @return AttendanceLogStudent
     */
    public function setCreationDate(DateTimeImmutable $creationDate): AttendanceLogStudent
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * toArray
     *
     * 19/10/2020 12:29
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }
}

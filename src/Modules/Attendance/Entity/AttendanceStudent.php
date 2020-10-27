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
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttendanceStudent
 *
 * 19/10/2020 12:23
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceStudentRepository")
 * @ORM\Table(name="AttendanceStudent",
 *     indexes={
 *      @ORM\Index(name="student",columns={"student"}),
 *      @ORM\Index(name="attendance_code",columns={"attendance_code"}),
 *      @ORM\Index(name="attendance_course_class",columns={"attendance_course_class"}),
 *      @ORM\Index(name="attendance_roll_group",columns={"attendance_roll_group"})
 *     }
 * )
 * @AttendanceLogTime()
 * @\App\Modules\Attendance\Validator\AttendanceStudent()
 */
class AttendanceStudent extends AbstractEntity
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
     * @var AttendanceRollGroup|null
     * @ORM\ManyToOne(targetEntity="AttendanceRollGroup")
     * @ORM\JoinColumn(nullable=true,name="attendance_roll_group")
     */
    private ?AttendanceRollGroup $attendanceRollGroup;

    /**
     * @var AttendanceCourseClass|null
     * @ORM\ManyToOne(targetEntity="AttendanceCourseClass")
     * @ORM\JoinColumn(nullable=true,name="attendance_course_class")
     */
    private ?AttendanceCourseClass $attendanceClass;

    /**
     * @var array|null
     */
    private ?array $previousDays;

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
     * @return AttendanceStudent
     */
    public function setId(?string $id): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setCode(?AttendanceCode $code): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setStudent(?Student $student): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setReason(?string $reason): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setContext(?string $context): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setComment(?string $comment): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setDate(?DateTimeImmutable $date): AttendanceStudent
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
     * @return AttendanceStudent
     */
    public function setDailyTime(?string $dailyTime): AttendanceStudent
    {
        $this->dailyTime = $dailyTime ?: 'all_day';
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
     * getAttendanceRollGroup
     *
     * 19/10/2020 13:09
     * @return AttendanceRollGroup|null
     */
    public function getAttendanceRollGroup(): ?AttendanceRollGroup
    {
        return isset($this->attendanceRollGroup) ? $this->attendanceRollGroup : null;
    }

    /**
     * AttendanceRollGroup
     *
     * @param AttendanceRollGroup|null $attendanceRollGroup
     * @return AttendanceStudent
     */
    public function setAttendanceRollGroup(?AttendanceRollGroup $attendanceRollGroup): AttendanceStudent
    {
        $this->attendanceRollGroup = $attendanceRollGroup;
        return $this;
    }

    /**
     * getAttendanceClass
     *
     * 19/10/2020 13:09
     * @return AttendanceCourseClass|null
     */
    public function getAttendanceClass(): ?AttendanceCourseClass
    {
        return isset($this->attendanceClass) ? $this->attendanceClass : null;
    }

    /**
     * AttendanceClass
     *
     * @param AttendanceCourseClass|null $attendanceClass
     * @return AttendanceStudent
     */
    public function setAttendanceClass(?AttendanceCourseClass $attendanceClass): AttendanceStudent
    {
        $this->attendanceClass = $attendanceClass;
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

    /**
     * getStudentName
     *
     * 25/10/2020 09:34
     * @return string
     */
    public function getStudentName(): string
    {
        return $this->getStudent()->getFullName();
    }

    /**
     * getPersonalImage
     *
     * 25/10/2020 09:35
     * @return string
     */
    public function getPersonalImage(): string
    {
        return $this->getStudent()->getPerson()->getPersonalDocumentation()->getPersonalImage() ?: '/build/static/DefaultPerson.png';
    }

    /**
     * getAbsenceCount
     *
     * 25/10/2020 09:48
     * @return string
     */
    public function getAbsenceCount(): string
    {
        return TranslationHelper::translate('Days Absent', ['count' => ProviderFactory::getRepository(AttendanceStudent::class)->countStudentAbsences($this) ?: 0], 'Attendance');
    }

    /**
     * getPreviousDays
     *
     * 25/10/2020 11:43
     * @return array
     */
    public function getPreviousDays(): array
    {
        return ProviderFactory::create(AttendanceStudent::class)->getPreviousDaysStatus($this) ;
    }

    /**
     * PreviousDays
     *
     * @param array|null $previousDays
     * @return AttendanceStudent
     */
    public function setPreviousDays(?array $previousDays): AttendanceStudent
    {
        $this->previousDays = $previousDays;
        return $this;
    }

    /**
     * getContextType
     *
     * 27/10/2020 12:51
     * @return string
     */
    public function getContextType(): string
    {
        if ($this->getAttendanceRollGroup() !== null && in_array($this->getContext(), ['Roll Group','Person'])) return 'roll_group';
        if ($this->getAttendanceClass() !== null && in_array($this->getContext(), ['Class','Person'])) return 'course_class';
        if ($this->getContext() === 'Future') return 'future';
        if ($this->getContext() === 'Self Registration') return 'self';
        return 'unknown';
    }
}

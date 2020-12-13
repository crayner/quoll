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
 * Time: 12:45
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttendanceRecorderLogClass
 *
 * 19/10/2020 12:51
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceCourseClassRepository")
 * @ORM\Table(name="AttendanceCourseClass",
 *     indexes={@ORM\Index(name="course_class",columns={"course_class"}),
 *      @ORM\Index(name="period_class",columns={"period_class"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="unique_key",columns={"unique_key"})})
 * @UniqueEntity({"uniqueKey"})
 * @ORM\HasLifecycleCallbacks()
 */
class AttendanceCourseClass extends AbstractEntity
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
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass")
     * @ORM\JoinColumn(nullable=false,name="course_class")
     * @Assert\NotBlank()
     */
    private ?CourseClass $courseClass;

    /**
     * @var TimetablePeriodClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Timetable\Entity\TimetablePeriodClass")
     * @ORM\JoinColumn(nullable=true,name="period_class")
     */
    private ?TimetablePeriodClass $periodClass;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=false)
     */
    private ?DateTimeImmutable $date;

    /**
     * @var string|null
     * This is provided to ensure uniqueness when periodClass is NULL, on days the class is not normally timetabled.
     * It should not be set manually.
     * @ORM\Column(nullable=false,name="unique_key",length=191)
     */
    private ?string $uniqueKey;

    /**
     * @var ArrayCollection|null
     */
    private ?ArrayCollection $recorderLogs;

    /**
     * AttendanceRollGroup constructor.
     *
     * @param CourseClass|null $courseClass
     * @param DateTimeImmutable|null $date
     * @param TimetablePeriodClass|null $period
     */
    public function __construct(?CourseClass $courseClass, ?DateTimeImmutable $date, ?TimetablePeriodClass $period)
    {
        $this->setCourseClass($courseClass)
            ->setPeriodClass($period)
            ->setDate($date);
    }

    /**
     * Id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ?$this->id : null;
    }

    /**
     * Id
     *
     * @param string|null $id
     * @return AttendanceCourseClass
     */
    public function setId(?string $id): AttendanceCourseClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * getCourseClass
     *
     * 31/10/2020 08:21
     * @return CourseClass|null
     */
    public function getCourseClass(): ?CourseClass
    {
        return isset($this->courseClass) ? $this->courseClass : null;
    }

    /**
     * CourseClass
     *
     * @param CourseClass|null $courseClass
     * @return AttendanceCourseClass
     */
    public function setCourseClass(?CourseClass $courseClass): AttendanceCourseClass
    {
        $this->courseClass = $courseClass;
        return $this;
    }

    /**
     * getPeriodClass
     *
     * 3/11/2020 14:08
     * @return TimetablePeriodClassClass|null
     */
    public function getPeriodClass(): ?TimetablePeriodClass
    {
        return isset($this->periodClass) ? $this->periodClass : null;
    }

    /**
     * PeriodClass
     *
     * @param TimetablePeriodClass|null $periodClass
     * @return AttendanceCourseClass
     */
    public function setPeriodClass(?TimetablePeriodClass $periodClass): AttendanceCourseClass
    {
        $this->periodClass = $periodClass;
        return $this;
    }

    /**
     * getDate
     *
     * 31/10/2020 08:21
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
     * @return AttendanceCourseClass
     */
    public function setDate(?DateTimeImmutable $date): AttendanceCourseClass
    {
        $this->date = $date;
        return $this;
    }

    /**
     * UniqueKey
     *
     * @return string|null
     */
    public function getUniqueKey(): ?string
    {
        return isset($this->uniqueKey) ? $this->uniqueKey : null;
    }

    /**
     * setUniqueKey
     *
     * 13/11/2020 08:41
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @return AttendanceCourseClass
     */
    public function setUniqueKey(): AttendanceCourseClass
    {
        $this->uniqueKey = ($this->getCourseClass() ? $this->getCourseClass()->getId() : '') . ($this->getDate() ? $this->getDate()->format('Y-m-d') : '') . ($this->getPeriodClass() ? $this->getPeriodClass()->getId() : '');
        return $this;
    }

    /**
     * getRecorderLogs
     *
     * 27/10/2020 09:29
     * @return ArrayCollection
     */
    public function getRecorderLogs(): ArrayCollection
    {
        if (!isset($this->recorderLogs)) {
            $this->recorderLogs = new ArrayCollection(ProviderFactory::getRepository(AttendanceRecorderLog::class)->findBy(['logKey' => 'Class', 'logId' => $this->getId()],['recordedOn' => 'ASC']));
        }
        return $this->recorderLogs;
    }

    /**
     * toArray
     *
     * 19/10/2020 12:52
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }
}

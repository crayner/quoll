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
 * Time: 09:41
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Attendance\Validator\AttendanceLogTime;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttendanceLogRollGroup
 *
 * 17/10/2020 09:42
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceLogRollGroupRepository")
 * @ORM\Table(name="AttendanceLogRollGroup",
 *     uniqueConstraints={
 *      @ORM\UniqueConstraint(name="roll_group_date_daily_time",columns={"roll_group","daily_time","date"})
 *     },
 *     indexes={
 *      @ORM\Index(name="recorder",columns={"recorder"}),
 *      @ORM\Index(name="creator",columns={"creator"}),
 *      @ORM\Index(name="roll_group",columns={"roll_group"})
 *     }
 * )
 * @UniqueEntity(ignoreNull=false,fields={"dailyTime","rollGroup","date"})
 * @AttendanceLogTime()
 */
class AttendanceLogRollGroup extends AbstractEntity
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
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\RollGroup\Entity\RollGroup")
     * @ORM\JoinColumn(name="rollGroup",nullable=false,name="roll_group")
     * @Assert\NotBlank()
     */
    private ?RollGroup $rollGroup;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="recorder",nullable=false,name="recorder")
     * @Assert\NotBlank()
     */
    private ?Staff $recorder;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="recorder",nullable=false,name="creator")
     * @Assert\NotBlank()
     */
    private ?Staff $creator;

    /**
     * @var string
     * @ORM\Column(length=32,nullable=false,name="daily_time",options={"default": "all_day"})
     */
    private string $dailyTime = 'all_day';

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=false)
     */
    private ?DateTimeImmutable $date;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable",nullable=false,name="creation_date")
     */
    private DateTimeImmutable $creationDate;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable",nullable=false,name="recorded_date")
     */
    private DateTimeImmutable $recordedDate;

    /**
     * AttendanceLogRollGroup constructor.
     *
     * @param RollGroup|null $rollGroup
     * @param string $dailyTime
     * @param DateTimeImmutable|null $date
     */
    public function __construct(?RollGroup $rollGroup, ?DateTimeImmutable $date, string $dailyTime = 'all_day')
    {
        $this->setRollGroup($rollGroup)
            ->setDailyTime($dailyTime)
            ->setDate($date);
    }


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
     * @return AttendanceLogRollGroup
     */
    public function setId(?string $id): AttendanceLogRollGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * RollGroup
     *
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return isset($this->rollGroup) ? $this->rollGroup : null;
    }

    /**
     * RollGroup
     *
     * @param RollGroup|null $rollGroup
     * @return AttendanceLogRollGroup
     */
    public function setRollGroup(?RollGroup $rollGroup): AttendanceLogRollGroup
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * Recorder
     *
     * @return Staff|null
     */
    public function getRecorder(): ?Staff
    {
        return isset($this->recorder) ? $this->recorder : null;
    }

    /**
     * setRecorder
     *
     * 24/10/2020 15:19
     * @param Staff|null $recorder
     * @return $this
     */
    public function setRecorder(?Staff $recorder = null): AttendanceLogRollGroup
    {
        $this->recorder = $recorder ?: SecurityHelper::getCurrentUser()->getStaff();
        return $this;
    }

    /**
     * DailyTime
     *
     * @return string|null
     */
    public function getDailyTime(): ?string
    {
        return $this->dailyTime = isset($this->dailyTime) ? $this->dailyTime : null;
    }

    /**
     * DailyTime
     *
     * @param string|null $dailyTime
     * @return AttendanceLogRollGroup
     */
    public function setDailyTime(?string $dailyTime): AttendanceLogRollGroup
    {
        $this->dailyTime = $dailyTime;
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
     * @return AttendanceLogRollGroup
     */
    public function setDate(?DateTimeImmutable $date): AttendanceLogRollGroup
    {
        $this->date = $date;
        return $this;
    }

    /**
     * CreationDate
     *
     * @return DateTimeImmutable
     */
    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creationDate = isset($this->creationDate) ? $this->creationDate : new DateTimeImmutable();
    }

    /**
     * setCreationDate
     *
     * 24/10/2020 15:18
     * @param DateTimeImmutable|null $creationDate
     * @return $this
     */
    public function setCreationDate(?DateTimeImmutable $creationDate = null): AttendanceLogRollGroup
    {
        $this->creationDate = $creationDate ?: new DateTimeImmutable();
        $this->setRecordedDate();
        return $this;
    }

    /**
     * Creator
     *
     * @return Staff|null
     */
    public function getCreator(): ?Staff
    {
        return isset($this->creator) ? $this->creator : null;
    }

    /**
     * setCreator
     *
     * 24/10/2020 15:17
     * @param Staff|null $creator
     * @return $this
     */
    public function setCreator(?Staff $creator = null): AttendanceLogRollGroup
    {
        $this->creator = $creator ?: SecurityHelper::getCurrentUser()->getStaff();
        return $this;
    }

    /**
     * RecordedDate
     *
     * @return DateTimeImmutable
     */
    public function getRecordedDate(): DateTimeImmutable
    {
        return isset($this->recordedDate) ? $this->recordedDate : new DateTimeImmutable();
    }

    /**
     * setRecordedDate
     *
     * 24/10/2020 15:17
     * @param DateTimeImmutable|null $recordedDate
     * @return $this
     */
    public function setRecordedDate(?DateTimeImmutable $recordedDate = null): AttendanceLogRollGroup
    {
        $this->recordedDate = $recordedDate ?: new DateTimeImmutable();
        return $this;
    }

    /**
     * toArray
     *
     * 17/10/2020 10:09
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

}

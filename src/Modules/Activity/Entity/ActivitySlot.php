<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Activity\Entity;

use App\Manager\AbstractEntity;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\School\Entity\Facility;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ActivitySlot
 * @package App\Modules\Activity\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Activity\Repository\ActivitySlotRepository")
 * @ORM\Table(name="ActivitySlot",
 *     indexes={
 *     @ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="activity", columns={"activity"}),
 *     @ORM\Index(name="day_of_week", columns={"day_of_week"})}
 * )
 */
class ActivitySlot extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Activity|null
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="slots")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $activity;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Facility")
     * @ORM\JoinColumn(name="facility",referencedColumnName="id")
     */
    private $facility;

    /**
     * @var string|null
     * @ORM\Column(length=50, name="location_external")
     * @Assert\Length(max=50)
     */
    private $locationExternal;

    /**
     * @var DaysOfWeek|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\DaysOfWeek")
     * @ORM\JoinColumn(name="day_of_week",referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $dayOfWeek;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",name="time_start")
     */
    private $timeStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",name="time_end")
     */
    private $timeEnd;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return ActivitySlot
     */
    public function setId(?string $id): ActivitySlot
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Activity|null
     */
    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity|null $activity
     * @return ActivitySlot
     */
    public function setActivity(?Activity $activity): ActivitySlot
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * Facility.
     *
     * @param Facility|null $facility
     * @return ActivitySlot
     */
    public function setFacility(?Facility $facility): ActivitySlot
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocationExternal(): ?string
    {
        return $this->locationExternal;
    }

    /**
     * @param string|null $locationExternal
     * @return ActivitySlot
     */
    public function setLocationExternal(?string $locationExternal): ActivitySlot
    {
        $this->locationExternal = mb_substr($locationExternal,0,50);
        return $this;
    }

    /**
     * @return DaysOfWeek|null
     */
    public function getDayOfWeek(): ?DaysOfWeek
    {
        return $this->dayOfWeek;
    }

    /**
     * @param DaysOfWeek|null $dayOfWeek
     * @return ActivitySlot
     */
    public function setDayOfWeek(?DaysOfWeek $dayOfWeek): ActivitySlot
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeStart(): ?\DateTimeImmutable
    {
        return $this->timeStart;
    }

    /**
     * TimeStart.
     *
     * @param \DateTimeImmutable|null $timeStart
     * @return ActivitySlot
     */
    public function setTimeStart(?\DateTimeImmutable $timeStart): ActivitySlot
    {
        $this->timeStart = $timeStart;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeEnd(): ?\DateTimeImmutable
    {
        return $this->timeEnd;
    }

    /**
     * TimeEnd.
     *
     * @param \DateTimeImmutable|null $timeEnd
     * @return ActivitySlot
     */
    public function setTimeEnd(?\DateTimeImmutable $timeEnd): ActivitySlot
    {
        $this->timeEnd = $timeEnd;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array
     * 3/06/2020 16:37
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__ActivitySlot` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `activity` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `facility` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `day_of_week` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `location_external` varchar(50) NOT NULL,
                    `time_start` time NOT NULL COMMENT '(DC2Type:time_immutable)',
                    `time_end` time NOT NULL COMMENT '(DC2Type:time_immutable)',
                    PRIMARY KEY (`id`),
                    KEY `facility` (`facility`),
                    KEY `activity` (`activity`),
                    KEY `day_of_week` (`day_of_week`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 3/06/2020 16:38
     */
    public function foreignConstraints() : string
    {
        return "ALTER TABLE `__prefix__ActivitySlot`
                    ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`day_of_week`) REFERENCES `__prefix__DaysOfWeek` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`activity`) REFERENCES `__prefix__Activity` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 3/06/2020 16:38
     */
    public static function getVersion() : string
    {
        return static::VERSION;
    }
}
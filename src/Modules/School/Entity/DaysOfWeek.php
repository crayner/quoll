<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DaysOfWeek
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\DaysOfWeekRepository")
 * @ORM\Table(name="DaysOfWeek",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation",columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="sequence_number",columns={"sequence_number"}) })
 * @UniqueEntity("name")
 * @UniqueEntity("abbreviation")
 */
class DaysOfWeek extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     * @Assert\Length(max=10)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=4)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $abbreviation;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=7)
     */
    private $sequenceNumber;

    /**
     * @var string
     * @ORM\Column(length=1,options={"default": "Y"})
     */
    private $schoolDay = 'Y';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolOpen;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolEnd;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolClose;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return DaysOfWeek
     */
    public function setId(?string $id): DaysOfWeek
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DaysOfWeek
     */
    public function setName(string $name): DaysOfWeek
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @param string $abbreviation
     * @return DaysOfWeek
     */
    public function setAbbreviation(string $abbreviation): DaysOfWeek
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSequenceNumber(): ?int
    {
        return $this->sequenceNumber;
    }

    /**
     * @param int|null $sequenceNumber
     * @return DaysOfWeek
     */
    public function setSequenceNumber(?int $sequenceNumber): DaysOfWeek
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * isSchoolDay
     * @return bool
     */
    public function isSchoolDay(): bool
    {
        return $this->getSchoolDay() === 'Y';
    }
    /**
     * @return string
     */
    public function getSchoolDay(): string
    {
        return self::checkBoolean($this->schoolDay);
    }

    /**
     * @param string $schoolDay
     * @return DaysOfWeek
     */
    public function setSchoolDay(string $schoolDay): DaysOfWeek
    {
        $this->schoolDay = self::checkBoolean($schoolDay);
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolOpen(): ?\DateTimeImmutable
    {
        return $this->schoolOpen;
    }

    /**
     * SchoolOpen.
     *
     * @param \DateTimeImmutable|null $schoolOpen
     * @return DaysOfWeek
     */
    public function setSchoolOpen(?\DateTimeImmutable $schoolOpen): DaysOfWeek
    {
        $this->schoolOpen = $schoolOpen;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolStart(): ?\DateTimeImmutable
    {
        return $this->schoolStart;
    }

    /**
     * SchoolStart.
     *
     * @param \DateTimeImmutable|null $schoolStart
     * @return DaysOfWeek
     */
    public function setSchoolStart(?\DateTimeImmutable $schoolStart): DaysOfWeek
    {
        $this->schoolStart = $schoolStart;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolEnd(): ?\DateTimeImmutable
    {
        return $this->schoolEnd;
    }

    /**
     * SchoolEnd.
     *
     * @param \DateTimeImmutable|null $schoolEnd
     * @return DaysOfWeek
     */
    public function setSchoolEnd(?\DateTimeImmutable $schoolEnd): DaysOfWeek
    {
        $this->schoolEnd = $schoolEnd;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolClose(): ?\DateTimeImmutable
    {
        return $this->schoolClose;
    }

    /**
     * SchoolClose.
     *
     * @param \DateTimeImmutable|null $schoolClose
     * @return DaysOfWeek
     */
    public function setSchoolClose(?\DateTimeImmutable $schoolClose): DaysOfWeek
    {
        $this->schoolClose = $schoolClose;
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

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__DaysOfWeek` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` char(10) NOT NULL,
                    `abbreviation` char(4) NOT NULL,
                    `sequence_number` smallint(6) NOT NULL,
                    `school_day` varchar(1) NOT NULL DEFAULT 'Y',
                    `school_open` time DEFAULT NULL COMMENT '(DC2Type:time_immutable)',
                    `school_start` time DEFAULT NULL COMMENT '(DC2Type:time_immutable)',
                    `school_end` time DEFAULT NULL COMMENT '(DC2Type:time_immutable)',
                    `school_close` time DEFAULT NULL COMMENT '(DC2Type:time_immutable)',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `abbreviation` (`abbreviation`),
                    UNIQUE KEY `sequence_number` (`sequence_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * getVersion
     * @return string
     */
    public static function getVersion(): string
    {
        return DaysOfWeek::VERSION;
    }
    
    public function coreData(): array 
    {
        return Yaml::parse("
-
  name: 'Monday'
  abbreviation: 'Mon'
  sequenceNumber: 1
  schoolDay: 'Y'
  schoolOpen: '07:45:00'
  schoolStart: '08:30:00'
  schoolEnd: '15:30:00'
  schoolClose: '17:00:00'
-
  name: 'Tuesday'
  abbreviation: 'Tue'
  sequenceNumber: 2
  schoolDay: 'Y'
  schoolOpen: '07:45:00'
  schoolStart: '08:30:00'
  schoolEnd: '15:30:00'
  schoolClose: '17:00:00'
-
  name: 'Wednesday'
  abbreviation: 'Wed'
  sequenceNumber: 3
  schoolDay: 'Y'
  schoolOpen: '07:45:00'
  schoolStart: '08:30:00'
  schoolEnd: '15:30:00'
  schoolClose: '17:00:00'
-
  name: 'Thursday'
  abbreviation: 'Thu'
  sequenceNumber: 4
  schoolDay: 'Y'
  schoolOpen: '07:45:00'
  schoolStart: '08:30:00'
  schoolEnd: '15:30:00'
  schoolClose: '17:00:00'
-
  name: 'Friday'
  abbreviation: 'Fri'
  sequenceNumber: 5
  schoolDay: 'Y'
  schoolOpen: '07:45:00'
  schoolStart: '08:30:00'
  schoolEnd: '15:30:00'
  schoolClose: '17:00:00'
-
  name: 'Saturday'
  abbreviation: 'Sat'
  sequenceNumber: 6
  schoolDay: 'N'
-
  name: 'Sunday'
  abbreviation: 'Sun'
  sequenceNumber: 7
  schoolDay: 'N'
");
    }
}
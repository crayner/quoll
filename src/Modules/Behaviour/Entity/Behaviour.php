<?php
/**
 * Created by PhpStorm.
 *
 * __prefix__
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Behaviour\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use App\Modules\Planner\Entity\PlannerEntry;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Behaviour
 * @package App\Modules\Behaviour\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Behaviour\Repository\BehaviourRepository")
 * @ORM\Table(name="Behaviour", 
 *     indexes={@ORM\Index(name="person",columns={"person"}),
 *     @ORM\Index(name="academic_year",columns={"academic_year"}),
 *     @ORM\Index(name="creator",columns={"creator"}),
 *     @ORM\Index(name="planner_entry",columns={"planner_entry"})})
 * @ORM\HasLifecycleCallbacks
 */
class Behaviour extends AbstractEntity
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
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     */
    private $academicYear;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $date;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=8,options={"default": "Positive"})
     */
    private $type = 'Positive';

    /**
     * @var array
     */
    private static $typeList = ['Positive', 'Negative'];

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $descriptor;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $level;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $followup;

    /**
     * @var PlannerEntry|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Planner\Entity\PlannerEntry")
     * @ORM\JoinColumn(name="planner_entry", referencedColumnName="id")
     */
    private $plannerEntry;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator", referencedColumnName="id", nullable=false)
     */
    private $creator;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",name="modified_on")
     */
    private $modifiedOn;

    /**
     * Behaviour constructor.
     */
    public function __construct()
    {
        $this->setModifiedOn(new \DateTimeImmutable('now'));
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Behaviour
     */
    public function setId(?string $id): Behaviour
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @param AcademicYear|null $academicYear
     * @return Behaviour
     */
    public function setAcademicYear(?AcademicYear $academicYear): Behaviour
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable|null $date
     * @return Behaviour
     */
    public function setDate(?\DateTimeImmutable $date): Behaviour
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return Behaviour
     */
    public function setPerson(?Person $person): Behaviour
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Behaviour
     */
    public function setType(?string $type): Behaviour
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return string|null
     */
    public function getDescriptor(): ?string
    {
        return $this->descriptor;
    }

    /**
     * @param string|null $descriptor
     * @return Behaviour
     */
    public function setDescriptor(?string $descriptor): Behaviour
    {
        $this->descriptor = $descriptor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return $this->level;
    }

    /**
     * @param string|null $level
     * @return Behaviour
     */
    public function setLevel(?string $level): Behaviour
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return Behaviour
     */
    public function setComment(?string $comment): Behaviour
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFollowup(): ?string
    {
        return $this->followup;
    }

    /**
     * @param string|null $followup
     * @return Behaviour
     */
    public function setFollowup(?string $followup): Behaviour
    {
        $this->followup = $followup;
        return $this;
    }

    /**
     * @return PlannerEntry|null
     */
    public function getPlannerEntry(): ?PlannerEntry
    {
        return $this->plannerEntry;
    }

    /**
     * @param PlannerEntry|null $plannerEntry
     * @return Behaviour
     */
    public function setPlannerEntry(?PlannerEntry $plannerEntry): Behaviour
    {
        $this->plannerEntry = $plannerEntry;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getCreator(): ?Person
    {
        return $this->creator;
    }

    /**
     * @param Person|null $creator
     * @return Behaviour
     */
    public function setCreator(?Person $creator): Behaviour
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getModifiedOn(): ?\DateTimeImmutable
    {
        return $this->modifiedOn;
    }

    /**
     * @param \DateTimeImmutable|null $modifiedOn
     * @return Behaviour
     */
    public function setModifiedOn(?\DateTimeImmutable $modifiedOn): Behaviour
    {
        $this->modifiedOn = $modifiedOn;
        return $this;
    }

    /**
     * updateModifiedOn
     * @return Behaviour
     * @throws \Exception
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedOn(): Behaviour
    {
        return $this->setModifiedOn(new \DateTimeImmutable('now'));
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 22/06/2020 10:10
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Behaviour` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `academic_year` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `planner_entry` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `type` varchar(8) NOT NULL DEFAULT 'Positive',
                    `descriptor` varchar(100) DEFAULT NULL,
                    `level` varchar(100) DEFAULT NULL,
                    `comment` longtext,
                    `followup` longtext,
                    `modified_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    PRIMARY KEY (`id`),
                    KEY `person` (`person`),
                    KEY `academic_year` (`academic_year`),
                    KEY `creator` (`creator`),
                    KEY `planner_entry` (`planner_entry`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 22/06/2020 10:11
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Behaviour`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`planner_entry`) REFERENCES `__prefix__PlannerEntry` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 22/06/2020 09:56
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
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
namespace App\Modules\MarkBook\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\Curriculum\Entity\Rubric;
use App\Modules\Curriculum\Entity\Unit;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use App\Modules\Planner\Entity\PlannerEntry;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\Assess\Entity\Scale;
use App\Modules\System\Entity\Hook;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MarkBookColumn
 * @package App\Modules\MarkBook\Entity
 * @ORM\Entity(repositoryClass="App\Modules\MarkBook\Repository\MarkBookColumnRepository")
 * @ORM\Table(name="MarkBookColumn", 
 *     indexes={@ORM\Index(name="course_class", columns={"course_class"}), 
 *     @ORM\Index(name="complete_date", columns={"complete_date"}),
 *     @ORM\Index(name="unit", columns={"unit"}),
 *     @ORM\Index(name="hook", columns={"hook"}),
 *     @ORM\Index(name="planner_entry", columns={"planner_entry"}),
 *     @ORM\Index(name="academic_year_term", columns={"academic_year_term"}),
 *     @ORM\Index(name="scale_attainment", columns={"scale_attainment"}),
 *     @ORM\Index(name="scale_effort", columns={"scale_effort"}),
 *     @ORM\Index(name="rubric_attainment", columns={"rubric_attainment"}),
 *     @ORM\Index(name="rubric_effort", columns={"rubric_effort"}),
 *     @ORM\Index(name="creator", columns={"creator"}),
 *     @ORM\Index(name="modifier", columns={"modifier"}),
 *     @ORM\Index(name="complete", columns={"complete"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_course_class",columns={"name","course_class"})})
 * @UniqueEntity({"name","courseClass"})
 * @ORM\HasLifecycleCallbacks()
 */
class MarkBookColumn extends AbstractEntity
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
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass")
     * @ORM\JoinColumn(name="course_class",referencedColumnName="id")
     */
    private $courseClass;

    /**
     * @var Hook|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Hook")
     * @ORM\JoinColumn(name="hook",referencedColumnName="id",nullable=true)
     */
    private $hook;

    /**
     * @var Unit|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Curriculum\Entity\Unit")
     * @ORM\JoinColumn(name="unit",referencedColumnName="id")
     */
    private $unit;

    /**
     * @var PlannerEntry|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Planner\Entity\PlannerEntry")
     * @ORM\JoinColumn(name="planner_entry",referencedColumnName="id")
     */
    private $plannerEntry;

    /**
     * @var AcademicYearTerm|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYearTerm")
     * @ORM\JoinColumn(name="academic_year_term",referencedColumnName="id")
     */
    private $AcademicYearTerm;

    /**
     * @var integer|null
     * @ORM\Column(nullable=true,type="integer",options={"comment": "A value used to group multiple markBook columns."},name="grouping_id")
     */
    private $groupingID;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $type;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $date;

    /**
     * @var int
     * @ORM\Column(type="smallint",options={"default": "0"})
     * @Assert\Range(min=0,max=999)
     */
    private $sequenceNumber;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $attachment;
    
    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $attainment = 'Y';

    /**
     * @var Scale|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Assess\Entity\Scale")
     * @ORM\JoinColumn(name="scale_attainment",referencedColumnName="id")
     */
    private $scaleAttainment;

    /**
     * @var float|null
     * @ORM\Column(type="decimal",precision=5,scale=2,nullable=true)
     */
    private $attainmentWeighting;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $attainmentRaw = 'N';

    /**
     * @var float|null
     * @ORM\Column(type="decimal",precision=8,scale=2,nullable=true)
     */
    private $attainmentRawMax;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $effort = 'Y';

    /**
     * @var Scale|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Assess\Entity\Scale")
     * @ORM\JoinColumn(name="scale_effort", referencedColumnName="id")
     */
    private $scaleEffort;

    /**
     * @var Rubric|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Curriculum\Entity\Rubric")
     * @ORM\JoinColumn(name="rubric_attainment", referencedColumnName="id")
     */
    private $rubricAttainment;

    /**
     * @var Rubric|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Curriculum\Entity\Rubric")
     * @ORM\JoinColumn(name="rubric_effort", referencedColumnName="id")
     */
    private $rubricEffort;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $comment = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $uploadedResponse = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $complete = 'N';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $completeDate;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $viewableStudents = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $viewableParents = 'N';

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator",referencedColumnName="id")
     */
    private $creator;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="modifier",referencedColumnName="id")
     */
    private $modifier;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return MarkBookColumn
     */
    public function setId(?string $id): MarkBookColumn
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return CourseClass|null
     */
    public function getCourseClass(): ?CourseClass
    {
        return $this->courseClass;
    }

    /**
     * @param CourseClass|null $courseClass
     * @return MarkBookColumn
     */
    public function setCourseClass(?CourseClass $courseClass): MarkBookColumn
    {
        $this->courseClass = $courseClass;
        return $this;
    }

    /**
     * @return Hook|null
     */
    public function getHook(): ?Hook
    {
        return $this->hook;
    }

    /**
     * @param Hook|null $hook
     * @return MarkBookColumn
     */
    public function setHook(?Hook $hook): MarkBookColumn
    {
        $this->hook = $hook;
        return $this;
    }

    /**
     * @return Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    /**
     * @param Unit|null $unit
     * @return MarkBookColumn
     */
    public function setUnit(?Unit $unit): MarkBookColumn
    {
        $this->unit = $unit;
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
     * @return MarkBookColumn
     */
    public function setPlannerEntry(?PlannerEntry $plannerEntry): MarkBookColumn
    {
        $this->plannerEntry = $plannerEntry;
        return $this;
    }

    /**
     * @return AcademicYearTerm|null
     */
    public function getAcademicYearTerm(): ?AcademicYearTerm
    {
        return $this->AcademicYearTerm;
    }

    /**
     * @param AcademicYearTerm|null $AcademicYearTerm
     * @return MarkBookColumn
     */
    public function setAcademicYearTerm(?AcademicYearTerm $AcademicYearTerm): MarkBookColumn
    {
        $this->AcademicYearTerm = $AcademicYearTerm;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getGroupingID(): ?int
    {
        return $this->groupingID;
    }

    /**
     * @param int|null $groupingID
     * @return MarkBookColumn
     */
    public function setGroupingID(?int $groupingID): MarkBookColumn
    {
        $this->groupingID = $groupingID;
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
     * @return MarkBookColumn
     */
    public function setType(?string $type): MarkBookColumn
    {
        $this->type = $type;
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
     * @param string|null $name
     * @return MarkBookColumn
     */
    public function setName(?string $name): MarkBookColumn
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return MarkBookColumn
     */
    public function setDescription(?string $description): MarkBookColumn
    {
        $this->description = $description;
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
     * @return MarkBookColumn
     */
    public function setDate(?\DateTimeImmutable $date): MarkBookColumn
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    /**
     * @param int $sequenceNumber
     * @return MarkBookColumn
     */
    public function setSequenceNumber(int $sequenceNumber): MarkBookColumn
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    /**
     * @param string|null $attachment
     * @return MarkBookColumn
     */
    public function setAttachment(?string $attachment): MarkBookColumn
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttainment(): ?string
    {
        return $this->attainment;
    }

    /**
     * @param string|null $attainment
     * @return MarkBookColumn
     */
    public function setAttainment(?string $attainment): MarkBookColumn
    {
        $this->attainment = self::checkBoolean($attainment);
        return $this;
    }

    /**
     * @return Scale|null
     */
    public function getScaleAttainment(): ?Scale
    {
        return $this->scaleAttainment;
    }

    /**
     * @param Scale|null $scaleAttainment
     * @return MarkBookColumn
     */
    public function setScaleAttainment(?Scale $scaleAttainment): MarkBookColumn
    {
        $this->scaleAttainment = $scaleAttainment;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAttainmentWeighting(): ?float
    {
        return $this->attainmentWeighting;
    }

    /**
     * @param float|null $attainmentWeighting
     * @return MarkBookColumn
     */
    public function setAttainmentWeighting(?float $attainmentWeighting): MarkBookColumn
    {
        $this->attainmentWeighting = $attainmentWeighting;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttainmentRaw(): ?string
    {
        return $this->attainmentRaw;
    }

    /**
     * @param string|null $attainmentRaw
     * @return MarkBookColumn
     */
    public function setAttainmentRaw(?string $attainmentRaw): MarkBookColumn
    {
        $this->attainmentRaw = self::checkBoolean($attainmentRaw, 'N');
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAttainmentRawMax(): ?float
    {
        return $this->attainmentRawMax;
    }

    /**
     * @param float|null $attainmentRawMax
     * @return MarkBookColumn
     */
    public function setAttainmentRawMax(?float $attainmentRawMax): MarkBookColumn
    {
        $this->attainmentRawMax = $attainmentRawMax;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEffort(): ?string
    {
        return $this->effort;
    }

    /**
     * @param string|null $effort
     * @return MarkBookColumn
     */
    public function setEffort(?string $effort): MarkBookColumn
    {
        $this->effort = self::checkBoolean($effort);
        return $this;
    }

    /**
     * @return Scale|null
     */
    public function getScaleEffort(): ?Scale
    {
        return $this->scaleEffort;
    }

    /**
     * @param Scale|null $scaleEffort
     * @return MarkBookColumn
     */
    public function setScaleEffort(?Scale $scaleEffort): MarkBookColumn
    {
        $this->scaleEffort = $scaleEffort;
        return $this;
    }

    /**
     * @return Rubric|null
     */
    public function getRubricAttainment(): ?Rubric
    {
        return $this->rubricAttainment;
    }

    /**
     * @param Rubric|null $rubricAttainment
     * @return MarkBookColumn
     */
    public function setRubricAttainment(?Rubric $rubricAttainment): MarkBookColumn
    {
        $this->rubricAttainment = $rubricAttainment;
        return $this;
    }

    /**
     * @return Rubric|null
     */
    public function getRubricEffort(): ?Rubric
    {
        return $this->rubricEffort;
    }

    /**
     * @param Rubric|null $rubricEffort
     * @return MarkBookColumn
     */
    public function setRubricEffort(?Rubric $rubricEffort): MarkBookColumn
    {
        $this->rubricEffort = $rubricEffort;
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
     * @return MarkBookColumn
     */
    public function setComment(?string $comment): MarkBookColumn
    {
        $this->comment = self::checkBoolean($comment);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUploadedResponse(): ?string
    {
        return $this->uploadedResponse;
    }

    /**
     * @param string|null $uploadedResponse
     * @return MarkBookColumn
     */
    public function setUploadedResponse(?string $uploadedResponse): MarkBookColumn
    {
        $this->uploadedResponse = self::checkBoolean($uploadedResponse);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplete(): ?string
    {
        return $this->complete;
    }

    /**
     * @param string|null $complete
     * @return MarkBookColumn
     */
    public function setComplete(?string $complete): MarkBookColumn
    {
        $this->complete = self::checkBoolean($complete, 'N');
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCompleteDate(): ?\DateTimeImmutable
    {
        return $this->completeDate;
    }

    /**
     * @param \DateTimeImmutable|null $completeDate
     * @return MarkBookColumn
     */
    public function setCompleteDate(?\DateTimeImmutable $completeDate): MarkBookColumn
    {
        $this->completeDate = $completeDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getViewableStudents(): ?string
    {
        return $this->viewableStudents;
    }

    /**
     * @param string|null $viewableStudents
     * @return MarkBookColumn
     */
    public function setViewableStudents(?string $viewableStudents): MarkBookColumn
    {
        $this->viewableStudents = self::checkBoolean($viewableStudents, 'N');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getViewableParents(): ?string
    {
        return $this->viewableParents;
    }

    /**
     * @param string|null $viewableParents
     * @return MarkBookColumn
     */
    public function setViewableParents(?string $viewableParents): MarkBookColumn
    {
        $this->viewableParents = self::checkBoolean($viewableParents, 'N');
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
     * setCreator
     * @param Person|null $creator
     * @return MarkBookColumn
     * @throws \Exception
     */
    public function setCreator(?Person $creator): MarkBookColumn
    {
        if (null === $creator && null === $this->creator)
            $creator = UserHelper::getCurrentUser();

        $this->creator = $creator;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getModifier(): ?Person
    {
        return $this->modifier;
    }

    /**
     * Modifier.
     *
     * @param Person|null $modifier
     * @return MarkBookColumn
     */
    public function setModifier(?Person $modifier): MarkBookColumn
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * changeModifier
     * @return MarkBookColumn
     * @throws \Exception
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function changeModifier(): MarkBookColumn
    {
        return $this->setModifier(new \DateTimeImmutable());
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 21/06/2020 09:03
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__MarkBookColumn` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `course_class` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `hook` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `unit` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `planner_entry` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `academic_year_term` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `scale_attainment` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `scale_effort` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `rubric_attainment` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `rubric_effort` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `modifier` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `grouping_id` int(11) DEFAULT NULL COMMENT 'A value used to group multiple markBook columns.',
                    `type` varchar(50) NOT NULL,
                    `name` varchar(20) NOT NULL,
                    `description` longtext NOT NULL,
                    `date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `sequence_number` smallint DEFAULT 0 NOT NULL,
                    `attachment` varchar(191) NOT NULL,
                    `attainment` varchar(1) NOT NULL DEFAULT 'Y',
                    `attainment_weighting` decimal(5,2) DEFAULT NULL,
                    `attainment_raw` varchar(1) NOT NULL DEFAULT 'N',
                    `attainment_raw_max` decimal(8,2) DEFAULT NULL,
                    `effort` varchar(1) NOT NULL DEFAULT 'Y',
                    `comment` varchar(1) NOT NULL DEFAULT 'Y',
                    `uploaded_response` varchar(1) NOT NULL DEFAULT 'Y',
                    `complete` varchar(1) NOT NULL,
                    `complete_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `viewable_students` varchar(1) NOT NULL,
                    `viewable_parents` varchar(1) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name_course_class` (`name`,`course_class`),
                    KEY `course_class` (`course_class`),
                    KEY `complete_date` (`complete_date`),
                    KEY `unit` (`unit`),
                    KEY `hook` (`hook`),
                    KEY `planner_entry` (`planner_entry`),
                    KEY `academic_year_term` (`academic_year_term`),
                    KEY `scale_attainment` (`scale_attainment`),
                    KEY `scale_effort` (`scale_effort`),
                    KEY `rubric_attainment` (`rubric_attainment`),
                    KEY `rubric_effort` (`rubric_effort`),
                    KEY `creator` (`creator`),
                    KEY `modifier` (`modifier`),
                    KEY `complete` (`complete`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__MarkBookColumn`
  ADD CONSTRAINT FOREIGN KEY (`scale_attainment`) REFERENCES `__prefix__Scale` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`scale_effort`) REFERENCES `__prefix__Scale` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`planner_entry`) REFERENCES `__prefix__PlannerEntry` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`hook`) REFERENCES `__prefix__Hook` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`modifier`) REFERENCES `__prefix__Person` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`rubric_effort`) REFERENCES `__prefix__Rubric` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`rubric_attainment`) REFERENCES `__prefix__Rubric` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`unit`) REFERENCES `__prefix__Unit` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`academic_year_term`) REFERENCES `__prefix__AcademicYearTerm` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 21/06/2020 09:03
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
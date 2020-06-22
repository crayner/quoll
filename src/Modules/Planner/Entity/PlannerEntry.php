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
namespace App\Modules\Planner\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\Curriculum\Entity\Unit;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PlannerEntry
 * @package App\Modules\Planner\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Planner\Repository\PlannerEntryRepository")
 * @ORM\Table(name="PlannerEntry",
 *     indexes={@ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="creator", columns={"creator"}),
 *     @ORM\Index(name="modifier", columns={"modifier"}),
 *     @ORM\Index(name="unit", columns={"unit"})})
 */
class PlannerEntry extends AbstractEntity
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
     * @var Unit|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Curriculum\Entity\Unit")
     * @ORM\JoinColumn(name="unit",referencedColumnName="id")
     */
    private $unit;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $date;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $timeStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $timeEnd;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $summary;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $teachersNotes;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     */
    private $homework = 'N';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $homeworkDueDateTime;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $homeworkDetails;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     */
    private $homeworkSubmission = ' ';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $homeworkSubmissionDateOpen;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkSubmissionDrafts = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=10,options={"default": ""})
     */
    private $homeworkSubmissionType;

    /**
     * @var array
     */
    private static $homeworkSubmissionTypeList = ['', 'Link', 'File', 'Link/File'];

    /**
     * @var string|null
     * @ORM\Column(length=10,options={"default": "Optional"})
     */
    private $homeworkSubmissionRequired = 'Optional';

    /**
     * @var array
     */
    private static $homeworkSubmissionRequiredList = ['Optional', 'Compulsory'];

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssess = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssessOtherTeachersRead = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssessOtherParentsRead = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssessClassmatesParentsRead = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssessSubmitterParentsRead = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssessOtherStudentsRead = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $homeworkCrowdAssessClassmatesRead = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $viewableStudents = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $viewableParents = 'N';

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $creator;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="modifier",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $modifier;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="App\Modules\Planner\Entity\PlannerEntryStudentHomework",mappedBy="plannerEntry")
     */
    private $studentHomeworkEntries;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="App\Modules\Planner\Entity\PlannerEntryGuest",mappedBy="plannerEntry")
     */
    private $plannerEntryGuests;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return PlannerEntry
     */
    public function setId(?string $id): PlannerEntry
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
     * @return PlannerEntry
     */
    public function setCourseClass(?CourseClass $courseClass): PlannerEntry
    {
        $this->courseClass = $courseClass;
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
     * @return PlannerEntry
     */
    public function setUnit(?Unit $unit): PlannerEntry
    {
        $this->unit = $unit;
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
     * @return PlannerEntry
     */
    public function setDate(?\DateTimeImmutable $date): PlannerEntry
    {
        $this->date = $date;
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
     * @param \DateTimeImmutable|null $timeStart
     * @return PlannerEntry
     */
    public function setTimeStart(?\DateTimeImmutable $timeStart): PlannerEntry
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
     * @param \DateTimeImmutable|null $timeEnd
     * @return PlannerEntry
     */
    public function setTimeEnd(?\DateTimeImmutable $timeEnd): PlannerEntry
    {
        $this->timeEnd = $timeEnd;
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
     * @return PlannerEntry
     */
    public function setName(?string $name): PlannerEntry
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string|null $summary
     * @return PlannerEntry
     */
    public function setSummary(?string $summary): PlannerEntry
    {
        $this->summary = $summary;
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
     * @return PlannerEntry
     */
    public function setDescription(?string $description): PlannerEntry
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTeachersNotes(): ?string
    {
        return $this->teachersNotes;
    }

    /**
     * @param string|null $teachersNotes
     * @return PlannerEntry
     */
    public function setTeachersNotes(?string $teachersNotes): PlannerEntry
    {
        $this->teachersNotes = $teachersNotes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomework(): ?string
    {
        return $this->homework;
    }

    /**
     * @param string|null $homework
     * @return PlannerEntry
     */
    public function setHomework(?string $homework): PlannerEntry
    {
        $this->homework = self::checkBoolean($homework, 'N');
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getHomeworkDueDateTime(): ?\DateTimeImmutable
    {
        return $this->homeworkDueDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $homeworkDueDateTime
     * @return PlannerEntry
     */
    public function setHomeworkDueDateTime(?\DateTimeImmutable $homeworkDueDateTime): PlannerEntry
    {
        $this->homeworkDueDateTime = $homeworkDueDateTime;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkDetails(): ?string
    {
        return $this->homeworkDetails;
    }

    /**
     * @param string|null $homeworkDetails
     * @return PlannerEntry
     */
    public function setHomeworkDetails(?string $homeworkDetails): PlannerEntry
    {
        $this->homeworkDetails = $homeworkDetails;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkSubmission(): ?string
    {
        return $this->homeworkSubmission;
    }

    /**
     * @param string|null $homeworkSubmission
     * @return PlannerEntry
     */
    public function setHomeworkSubmission(?string $homeworkSubmission): PlannerEntry
    {
        $this->homeworkSubmission = self::checkBoolean($homeworkSubmission, '');
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getHomeworkSubmissionDateOpen(): ?\DateTimeImmutable
    {
        return $this->homeworkSubmissionDateOpen;
    }

    /**
     * @param \DateTimeImmutable|null $homeworkSubmissionDateOpen
     * @return PlannerEntry
     */
    public function setHomeworkSubmissionDateOpen(?\DateTimeImmutable $homeworkSubmissionDateOpen): PlannerEntry
    {
        $this->homeworkSubmissionDateOpen = $homeworkSubmissionDateOpen;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkSubmissionDrafts(): ?string
    {
        return $this->homeworkSubmissionDrafts;
    }

    /**
     * @param string|null $homeworkSubmissionDrafts
     * @return PlannerEntry
     */
    public function setHomeworkSubmissionDrafts(?string $homeworkSubmissionDrafts): PlannerEntry
    {
        $this->homeworkSubmissionDrafts = $homeworkSubmissionDrafts;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkSubmissionType(): ?string
    {
        return $this->homeworkSubmissionType;
    }

    /**
     * @param string|null $homeworkSubmissionType
     * @return PlannerEntry
     */
    public function setHomeworkSubmissionType(?string $homeworkSubmissionType): PlannerEntry
    {
        $this->homeworkSubmissionType = in_array($homeworkSubmissionType, self::getHomeworkSubmissionTypeList()) ? $homeworkSubmissionType : '';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkSubmissionRequired(): ?string
    {
        return $this->homeworkSubmissionRequired;
    }

    /**
     * @param string|null $homeworkSubmissionRequired
     * @return PlannerEntry
     */
    public function setHomeworkSubmissionRequired(?string $homeworkSubmissionRequired): PlannerEntry
    {
        $this->homeworkSubmissionRequired = in_array($homeworkSubmissionRequired, self::getHomeworkSubmissionRequiredList()) ? $homeworkSubmissionRequired : 'Optional';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssess(): ?string
    {
        return $this->homeworkCrowdAssess;
    }

    /**
     * @param string|null $homeworkCrowdAssess
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssess(?string $homeworkCrowdAssess): PlannerEntry
    {
        $this->homeworkCrowdAssess = self::checkBoolean($homeworkCrowdAssess, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssessOtherTeachersRead(): ?string
    {
        return $this->homeworkCrowdAssessOtherTeachersRead;
    }

    /**
     * @param string|null $homeworkCrowdAssessOtherTeachersRead
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssessOtherTeachersRead(?string $homeworkCrowdAssessOtherTeachersRead): PlannerEntry
    {
        $this->homeworkCrowdAssessOtherTeachersRead = self::checkBoolean($homeworkCrowdAssessOtherTeachersRead, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssessOtherParentsRead(): ?string
    {
        return $this->homeworkCrowdAssessOtherParentsRead;
    }

    /**
     * @param string|null $homeworkCrowdAssessOtherParentsRead
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssessOtherParentsRead(?string $homeworkCrowdAssessOtherParentsRead): PlannerEntry
    {
        $this->homeworkCrowdAssessOtherParentsRead = self::checkBoolean($homeworkCrowdAssessOtherParentsRead, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssessClassmatesParentsRead(): ?string
    {
        return $this->homeworkCrowdAssessClassmatesParentsRead;
    }

    /**
     * @param string|null $homeworkCrowdAssessClassmatesParentsRead
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssessClassmatesParentsRead(?string $homeworkCrowdAssessClassmatesParentsRead): PlannerEntry
    {
        $this->homeworkCrowdAssessClassmatesParentsRead = self::checkBoolean($homeworkCrowdAssessClassmatesParentsRead, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssessSubmitterParentsRead(): ?string
    {
        return $this->homeworkCrowdAssessSubmitterParentsRead;
    }

    /**
     * @param string|null $homeworkCrowdAssessSubmitterParentsRead
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssessSubmitterParentsRead(?string $homeworkCrowdAssessSubmitterParentsRead): PlannerEntry
    {
        $this->homeworkCrowdAssessSubmitterParentsRead = self::checkBoolean($homeworkCrowdAssessSubmitterParentsRead, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssessOtherStudentsRead(): ?string
    {
        return $this->homeworkCrowdAssessOtherStudentsRead;
    }

    /**
     * @param string|null $homeworkCrowdAssessOtherStudentsRead
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssessOtherStudentsRead(?string $homeworkCrowdAssessOtherStudentsRead): PlannerEntry
    {
        $this->homeworkCrowdAssessOtherStudentsRead = self::checkBoolean($homeworkCrowdAssessOtherStudentsRead, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkCrowdAssessClassmatesRead(): ?string
    {
        return $this->homeworkCrowdAssessClassmatesRead;
    }

    /**
     * @param string|null $homeworkCrowdAssessClassmatesRead
     * @return PlannerEntry
     */
    public function setHomeworkCrowdAssessClassmatesRead(?string $homeworkCrowdAssessClassmatesRead): PlannerEntry
    {
        $this->homeworkCrowdAssessClassmatesRead = self::checkBoolean($homeworkCrowdAssessClassmatesRead, '');
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
     * @return PlannerEntry
     */
    public function setViewableStudents(?string $viewableStudents): PlannerEntry
    {
        $this->viewableStudents = self::checkBoolean($viewableStudents);
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
     * @return PlannerEntry
     */
    public function setViewableParents(?string $viewableParents): PlannerEntry
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
     * @param Person|null $creator
     * @return PlannerEntry
     */
    public function setCreator(?Person $creator): PlannerEntry
    {
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
     * @param Person|null $modifier
     * @return PlannerEntry
     */
    public function setModifier(?Person $modifier): PlannerEntry
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @return array
     */
    public static function getHomeworkSubmissionTypeList(): array
    {
        return self::$homeworkSubmissionTypeList;
    }

    /**
     * @return array
     */
    public static function getHomeworkSubmissionRequiredList(): array
    {
        return self::$homeworkSubmissionRequiredList;
    }

    /**
     * getStudentHomeworkEntries
     * @return Collection|null
     */
    public function getStudentHomeworkEntries(): ?Collection
    {
        if (empty($this->studentHomeworkEntries))
            $this->studentHomeworkEntries = new ArrayCollection();

        if ($this->studentHomeworkEntries instanceof PersistentCollection)
            $this->studentHomeworkEntries->initialize();

        return $this->studentHomeworkEntries;
    }

    /**
     * @param Collection|null $studentHomeworkEntries
     * @return PlannerEntry
     */
    public function setStudentHomeworkEntries(?Collection $studentHomeworkEntries): PlannerEntry
    {
        $this->studentHomeworkEntries = $studentHomeworkEntries;
        return $this;
    }

    /**
     * getPlannerEntryGuests
     * @return Collection|null
     */
    public function getPlannerEntryGuests(): ?Collection
    {
        if (empty($this->plannerEntryGuests))
            $this->plannerEntryGuests = new ArrayCollection();

        if ($this->plannerEntryGuests instanceof PersistentCollection)
            $this->plannerEntryGuests->initialize();

        return $this->plannerEntryGuests;
    }

    /**
     * @param Collection|null $plannerEntryGuests
     * @return PlannerEntry
     */
    public function setPlannerEntryGuests(?Collection $plannerEntryGuests): PlannerEntry
    {
        $this->plannerEntryGuests = $plannerEntryGuests;
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
     * @return array|string[]
     * 21/06/2020 09:41
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__PlannerEntry` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `course_class` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `unit` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `modifier` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `time_start` time DEFAULT NULL COMMENT '(DC2Type:time_immutable)',
                    `time_end` time DEFAULT NULL COMMENT '(DC2Type:time_immutable)',
                    `name` varchar(50) NOT NULL,
                    `summary` varchar(191) NOT NULL,
                    `description` longtext,
                    `teachers_notes` longtext,
                    `homework` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_due_date_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `homework_details` longtext NOT NULL,
                    `homework_submission` varchar(1) NOT NULL,
                    `homework_submission_date_open` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `homework_submission_drafts` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_submission_type` varchar(10) NOT NULL DEFAULT '',
                    `homework_submission_required` varchar(10) NOT NULL DEFAULT 'Optional',
                    `homework_crowd_assess` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_crowd_assess_other_teachers_read` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_crowd_assess_other_parents_read` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_crowd_assess_classmates_parents_read` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_crowd_assess_submitter_parents_read` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_crowd_assess_other_students_read` varchar(1) NOT NULL DEFAULT 'N',
                    `homework_crowd_assess_classmates_read` varchar(1) NOT NULL DEFAULT 'N',
                    `viewable_students` varchar(1) NOT NULL DEFAULT 'Y',
                    `viewable_parents` varchar(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`id`),
                    KEY `creator` (`creator`),
                    KEY `modifier` (`modifier`),
                    KEY `course_class` (`course_class`),
                    KEY `unit` (`unit`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__PlannerEntry`
                    ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`modifier`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`unit`) REFERENCES `__prefix__Unit` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 21/06/2020 09:43
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
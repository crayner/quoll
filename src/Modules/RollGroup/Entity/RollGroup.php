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
namespace App\Modules\RollGroup\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Manager\Traits\EntityGlobals;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\Facility;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RollGroup
 * @package App\Modules\RollGroup\Entity
 * @ORM\Entity(repositoryClass="App\Modules\RollGroup\Repository\RollGroupRepository")
 * @ORM\Table(name="RollGroup",
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(name="name_academic_year", columns={"name","academic_year"}),
 *     @ORM\UniqueConstraint(name="abbr_academic_year", columns={"abbreviation","academic_year"}),
 *     @ORM\UniqueConstraint(name="tutor_academic_year", columns={"tutor1","academic_year"}),
 *     @ORM\UniqueConstraint(name="facility_academic_year", columns={"facility","academic_year"})},
 *     indexes={@ORM\Index(name="tutor1",columns={"tutor1"}),
 *     @ORM\Index(name="tutor2",columns={"tutor2"}),
 *     @ORM\Index(name="tutor3",columns={"tutor3"}),
 *     @ORM\Index(name="assistant1",columns={"assistant1"}),
 *     @ORM\Index(name="assistant2",columns={"assistant2"}),
 *     @ORM\Index(name="assistant3",columns={"assistant3"}),
 *     @ORM\Index(name="facility",columns={"facility"}),
 *     @ORM\Index(name="academic_year",columns={"academic_year"}),
 *     @ORM\Index(name="next_roll_group",columns={"next_roll_group"}),})
 * @UniqueEntity({"name","academicYear"})
 * @UniqueEntity({"abbreviation","academicYear"})
 * @UniqueEntity({"tutor","academicYear"})
 * @UniqueEntity({"facility","academicYear"})
 */
class RollGroup extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    use EntityGlobals;

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
     * @var string|null
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=5, name="abbreviation")
     * @Assert\NotBlank()
     */
    private $abbreviation;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="tutor1",referencedColumnName="id",nullable=true)
     * @Assert\NotBlank()
     */
    private $tutor;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="tutor2",referencedColumnName="id",nullable=true)
     */
    private $tutor2;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="tutor3",referencedColumnName="id",nullable=true)
     */
    private $tutor3;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="assistant1",referencedColumnName="id",nullable=true)
     */
    private $assistant;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="assistant2",referencedColumnName="id",nullable=true)
     */
    private $assistant2;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="assistant3",referencedColumnName="id",nullable=true)
     */
    private $assistant3;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Facility")
     * @ORM\JoinColumn(name="facility", referencedColumnName="id",nullable=true)
     * @Assert\NotBlank()
     */
    private $facility;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\RollGroup\Entity\RollGroup")
     * @ORM\JoinColumn(name="next_roll_group", referencedColumnName="id",nullable=true)
     */
    private $nextRollGroup;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $attendance = 'Y';

    /**
     * @var string|null
     * @ORM\Column(nullable=true,length=191)
     * @Assert\Url()
     */
    private $website;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\StudentEnrolment", mappedBy="rollGroup")
     */
    private $studentEnrolments;

    /**
     * RollGroup constructor.
     */
    public function __construct()
    {
        $this->studentEnrolments = new ArrayCollection();
    }

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
     * @return RollGroup
     */
    public function setId(?string $id): RollGroup
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
     * @return RollGroup
     */
    public function setAcademicYear(?AcademicYear $academicYear): RollGroup
    {
        $this->academicYear = $academicYear;
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
     * @return RollGroup
     */
    public function setName(?string $name): RollGroup
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
     * @param string|null $abbreviation
     * @return RollGroup
     */
    public function setAbbreviation(?string $abbreviation): RollGroup
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getTutor(): ?Person
    {
        return $this->tutor;
    }

    /**
     * @param Person|null $tutor
     * @return RollGroup
     */
    public function setTutor(?Person $tutor): RollGroup
    {
        $this->tutor = $tutor;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getTutor2(): ?Person
    {
        return $this->tutor2;
    }

    /**
     * @param Person|null $tutor2
     * @return RollGroup
     */
    public function setTutor2(?Person $tutor2): RollGroup
    {
        $this->tutor2 = $tutor2;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getTutor3(): ?Person
    {
        return $this->tutor3;
    }

    /**
     * @param Person|null $tutor3
     * @return RollGroup
     */
    public function setTutor3(?Person $tutor3): RollGroup
    {
        $this->tutor3 = $tutor3;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getAssistant(): ?Person
    {
        return $this->assistant;
    }

    /**
     * @param Person|null $assistant
     * @return RollGroup
     */
    public function setAssistant(?Person $assistant): RollGroup
    {
        $this->assistant = $assistant;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getAssistant2(): ?Person
    {
        return $this->assistant2;
    }

    /**
     * @param Person|null $assistant2
     * @return RollGroup
     */
    public function setAssistant2(?Person $assistant2): RollGroup
    {
        $this->assistant2 = $assistant2;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getAssistant3(): ?Person
    {
        return $this->assistant3;
    }

    /**
     * @param Person|null $assistant3
     * @return RollGroup
     */
    public function setAssistant3(?Person $assistant3): RollGroup
    {
        $this->assistant3 = $assistant3;
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
     * @return RollGroup
     */
    public function setFacility(?Facility $facility): RollGroup
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return RollGroup|null
     */
    public function getNextRollGroup(): ?RollGroup
    {
        return $this->nextRollGroup;
    }

    /**
     * @param RollGroup|null $nextRollGroup
     * @return RollGroup
     */
    public function setNextRollGroup(?RollGroup $nextRollGroup): RollGroup
    {
        $this->nextRollGroup = $nextRollGroup;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttendance(): ?string
    {
        return $this->attendance;
    }

    /**
     * @param string|null $attendance
     * @return RollGroup
     */
    public function setAttendance(?string $attendance): RollGroup
    {
        $this->attendance = self::checkBoolean($attendance);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param string|null $website
     * @return RollGroup
     */
    public function setWebsite(?string $website): RollGroup
    {
        $this->website = $website;
        return $this;
    }

    /**
     * getStudentEnrolments
     * @param string|null $sortBy
     * @return Collection
     */
    public function getStudentEnrolments(?string $sortBy = ''): Collection
    {
        if (empty($this->studentEnrolments))
            $this->studentEnrolments = new ArrayCollection();

        if ($this->studentEnrolments instanceof PersistentCollection)
            $this->studentEnrolments->initialize();

        if ('' !== $sortBy) {
            $iterator = $this->studentEnrolments->getIterator();
            $iterator->uasort(
                function ($a, $b) use ($sortBy) {
                    if (!$a->getPerson() instanceof Person || !$b->getPerson() instanceof Person)
                        return 1;

                    if (strpos($sortBy, 'rollOrder') === 0)
                        return ($a->getRollOrder().$a->getPerson()->getSurname().$a->getPerson()->getPreferredName() < $b->getRollOrder().$b->getPerson()->getSurname().$b->getPerson()->getPreferredName()) ? -1 : 1;

                    if (strpos($sortBy, 'surname') === 0)
                        return ($a->getPerson()->getSurname().$a->getPerson()->getPreferredName() < $b->getPerson()->getSurname().$b->getPerson()->getPreferredName()) ? -1 : 1;

                    if (strpos($sortBy, 'preferredName') === 0)
                        return ($a->getPerson()->getPreferredName().$a->getPerson()->getSurname() < $b->getPerson()->getPreferredName().$b->getPerson()->getSurname()) ? -1 : 1;

                    return 1;
                }
            );

            $this->studentEnrolments = new ArrayCollection(iterator_to_array($iterator, false));
        }


        return $this->studentEnrolments;
    }

    /**
     * @param Collection|null $studentEnrolments
     * @return RollGroup
     */
    public function setStudentEnrolments(?Collection $studentEnrolments): RollGroup
    {
        $this->studentEnrolments = $studentEnrolments;
        return $this;
    }

    /**
     * getTutors
     * @return array
     */
    public function getTutors(): array
    {
        $tutors = [];
        if ($this->getTutor())
            $tutors[] = $this->getTutor();
        if ($this->getTutor2())
            $tutors[] = $this->getTutor2();
        if ($this->getTutor3())
            $tutors[] = $this->getTutor3();

        return $tutors;
    }

    /**
     * getFormatTutors
     * @return string
     */
    public function getFormatTutors(string $style = null): string
    {
        $result = '';
        $result = array_map(function (Person $person) use ($style) {
            return $person->formatName($style);
        }, $this->getTutors());
        return implode("<br/>\n", $result);
    }

    /**
     * getFacilityName
     * @return string
     */
    public function getFacilityName(): string
    {
        return $this->getFacility() ? $this->getFacility()->getName() : '';
    }

    /**
     * getStudentCount
     * @return int
     */
    public function getStudentCount(): int
    {
        return $this->getStudentEnrolments() ? count($this->getStudentEnrolments()) : 0;
    }

    /**
     * getAssistants
     * @return array
     */
    public function getAssistants(): array
    {
        $tutors = [];
        if ($this->getAssistant())
            $tutors[] = $this->getAssistant();
        if ($this->getAssistant2())
            $tutors[] = $this->getAssistant2();
        if ($this->getAssistant3())
            $tutors[] = $this->getAssistant3();

        return $tutors;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 22/06/2020 13:55
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'abbr' => $this->getAbbreviation(),
            'tutors' => $this->getFormatTutors(),
            'location' => $this->getFacilityName(),
            'website' => $this->getWebsite(),
            'students' => $this->getStudentEnrolments()->count() ?: TranslationHelper::translate('None'),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * canDelete
     * @return bool
     * 17/06/2020 12:54
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(RollGroup::class)->canDelete($this);
    }

    /**
     * create
     * @return array|string[]
     * 17/06/2020 12:54
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__RollGroup` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` VARCHAR(10) NOT NULL,
                    `abbreviation` VARCHAR(5) NOT NULL,
                    `attendance` VARCHAR(1) NOT NULL DEFAULT 'Y',
                    `website` VARCHAR(191) DEFAULT NULL,
                    `academic_year` CHAR(36) DEFAULT NULL,
                    `tutor1` CHAR(36) DEFAULT NULL,
                    `tutor2` CHAR(36) DEFAULT NULL,
                    `tutor3` CHAR(36) DEFAULT NULL,
                    `assistant1` CHAR(36) DEFAULT NULL,
                    `assistant2` CHAR(36) DEFAULT NULL,
                    `assistant3` CHAR(36) DEFAULT NULL,
                    `facility` CHAR(36) DEFAULT NULL,
                    `next_roll_group` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name_academic_year` (`name`,`academic_year`),
                    UNIQUE KEY `abbr_academic_year` (`abbreviation`,`academic_year`),
                    UNIQUE KEY `tutor_academic_year` (`tutor1`,`academic_year`),
                    KEY `academic_year` (`academic_year`),
                    KEY `tutor1` (`tutor1`),
                    KEY `tutor2` (`tutor2`),
                    KEY `tutor3` (`tutor3`),
                    KEY `assistant1` (`assistant1`),
                    KEY `assistant2` (`assistant2`),
                    KEY `assistant3` (`assistant3`),
                    KEY `facility` (`facility`),
                    KEY `next_roll_group` (`next_roll_group`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 17/06/2020 12:55
     */
    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__RollGroup`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`assistant1`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`assistant2`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`assistant3`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`tutor1`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`tutor2`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`tutor3`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`next_roll_group`) REFERENCES `__prefix__RollGroup` (`id`);';
    }

    /**
     * getVersion
     * @return string
     * 17/06/2020 12:55
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}

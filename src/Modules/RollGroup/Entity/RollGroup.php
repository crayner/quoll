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
use App\Manager\Traits\EntityGlobals;
use App\Modules\Enrolment\Entity\StudentRollGroup;
use App\Modules\Staff\Entity\Staff;
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

    use EntityGlobals;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id = null;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     */
    private ?AcademicYear $academicYear;

    /**
     * @var string|null
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     */
    private ?string $name;

    /**
     * @var string|null
     * @ORM\Column(length=5, name="abbreviation")
     * @Assert\NotBlank()
     */
    private ?string $abbreviation;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="tutor1",referencedColumnName="id",nullable=true)
     * @Assert\NotBlank()
     */
    private ?Staff $tutor;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="tutor2",referencedColumnName="id",nullable=true)
     */
    private ?Staff $tutor2;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="tutor3",referencedColumnName="id",nullable=true)
     */
    private ?Staff $tutor3;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="assistant1",referencedColumnName="id",nullable=true)
     */
    private ?Staff $assistant;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="assistant2",referencedColumnName="id",nullable=true)
     */
    private ?Staff $assistant2;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="assistant3",referencedColumnName="id",nullable=true)
     */
    private ?Staff $assistant3;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Facility")
     * @ORM\JoinColumn(name="facility", referencedColumnName="id",nullable=true)
     * @Assert\NotBlank()
     */
    private ?Facility $facility;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\RollGroup\Entity\RollGroup")
     * @ORM\JoinColumn(name="next_roll_group", referencedColumnName="id",nullable=true)
     */
    private ?RollGroup $nextRollGroup = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $attendance = true;

    /**
     * @var string|null
     * @ORM\Column(nullable=true,length=191)
     * @Assert\Url()
     */
    private $website;

    /**
     * @var Collection|StudentRollGroup[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\StudentRollGroup", mappedBy="rollGroup")
     */
    private ?Collection $studentRollGroups;

    /**
     * RollGroup constructor.
     */
    public function __construct()
    {
        $this->setStudentRollGroups(new ArrayCollection());
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
     * @return Staff|null
     */
    public function getTutor(): ?Staff
    {
        return $this->tutor;
    }

    /**
     * @param Staff|null $tutor
     * @return RollGroup
     */
    public function setTutor(?Staff $tutor): RollGroup
    {
        $this->tutor = $tutor;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getTutor2(): ?Staff
    {
        return $this->tutor2;
    }

    /**
     * @param Staff|null $tutor2
     * @return RollGroup
     */
    public function setTutor2(?Staff $tutor2): RollGroup
    {
        $this->tutor2 = $tutor2;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getTutor3(): ?Staff
    {
        return $this->tutor3;
    }

    /**
     * @param Staff|null $tutor3
     * @return RollGroup
     */
    public function setTutor3(?Staff $tutor3): RollGroup
    {
        $this->tutor3 = $tutor3;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getAssistant(): ?Staff
    {
        return $this->assistant;
    }

    /**
     * @param Staff|null $assistant
     * @return RollGroup
     */
    public function setAssistant(?Staff $assistant): RollGroup
    {
        $this->assistant = $assistant;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getAssistant2(): ?Staff
    {
        return $this->assistant2;
    }

    /**
     * @param Staff|null $assistant2
     * @return RollGroup
     */
    public function setAssistant2(?Staff $assistant2): RollGroup
    {
        $this->assistant2 = $assistant2;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getAssistant3(): ?Staff
    {
        return $this->assistant3;
    }

    /**
     * @param Staff|null $assistant3
     * @return RollGroup
     */
    public function setAssistant3(?Staff $assistant3): RollGroup
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
     * isAttendance
     *
     * 17/08/2020 12:18
     * @return bool
     */
    public function isAttendance(): bool
    {
        return (bool)$this->attendance;
    }

    /**
     * setAttendance
     *
     * 17/08/2020 12:18
     * @param bool $attendance
     * @return $this
     */
    public function setAttendance(bool $attendance): RollGroup
    {
        $this->attendance = $attendance;
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
     * getStudentRollGroups
     * @param string|null $sortBy
     * @return Collection
     */
    public function getStudentRollGroups(?string $sortBy = ''): Collection
    {
        if (empty($this->studentRollGroups))
            $this->studentRollGroups = new ArrayCollection();

        if ($this->studentRollGroups instanceof PersistentCollection)
            $this->studentRollGroups->initialize();

        if ('' !== $sortBy) {
            $iterator = $this->studentRollGroups->getIterator();
            $iterator->uasort(
                function ($a, $b) use ($sortBy) {
                    if (!$a->getStaff() instanceof Staff || !$b->getStaff() instanceof Staff)
                        return 1;

                    if (strpos($sortBy, 'rollOrder') === 0)
                        return ($a->getRollOrder().$a->getStaff()->getSurname().$a->getStaff()->getPreferredName() < $b->getRollOrder().$b->getStaff()->getSurname().$b->getStaff()->getPreferredName()) ? -1 : 1;

                    if (strpos($sortBy, 'surname') === 0)
                        return ($a->getStaff()->getSurname().$a->getStaff()->getPreferredName() < $b->getStaff()->getSurname().$b->getStaff()->getPreferredName()) ? -1 : 1;

                    if (strpos($sortBy, 'preferredName') === 0)
                        return ($a->getStaff()->getPreferredName().$a->getStaff()->getSurname() < $b->getStaff()->getPreferredName().$b->getStaff()->getSurname()) ? -1 : 1;

                    return 1;
                }
            );

            $this->studentRollGroups = new ArrayCollection(iterator_to_array($iterator, false));
        }


        return $this->studentRollGroups;
    }

    /**
     * @param Collection|null $studentRollGroups
     * @return RollGroup
     */
    public function setStudentRollGroups(?Collection $studentRollGroups): RollGroup
    {
        $this->studentRollGroups = $studentRollGroups;
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
     * @param string|null $style
     * @return string
     */
    public function getFormatTutors(string $style = 'Formal'): string
    {
        $result = array_map(function (Staff $staff) use ($style) {
            return $style === 'Reversed' ? $staff->getFullNameReversed() : $staff->getFullName();
        }, $this->getTutors());
        return trim(implode("<br />\n", $result), "<br />\n");
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
        return $this->getStudentRollGroups() ? count($this->getStudentRollGroups()) : 0;
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
            'students' => $this->getStudentRollGroups()->count() ?: TranslationHelper::translate('None'),
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
}

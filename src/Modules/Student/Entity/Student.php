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
 * Date: 1/07/2020
 * Time: 15:20
 */
namespace App\Modules\Student\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\SchoolCommonFields;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\ApplicationForm;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Entity\Theme;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Student
 * @package App\Modules\Student\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Student\Repository\StudentRepository")
 * @ORM\Table(name="Student",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="student_identifier",columns={"student_identifier"}),
 *      @ORM\UniqueConstraint("person", columns={"person"})},
 *  indexes={
 *      @ORM\Index(name="application_form",columns={"application_form"}),
 *      @ORM\Index(name="theme",columns={"theme"}),
 *      @ORM\Index(name="graduation_year",columns={"graduation_year"}),
 *      @ORM\Index(name="locale",columns={"locale"}),
 *      @ORM\Index(name="house",columns={"house"})
 *  }
 * )
 * @UniqueEntity("studentIdentifier")
 */
class Student extends AbstractEntity
{
    use SchoolCommonFields;

    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",inversedBy="student")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $studentIdentifier;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $studentAgreements;

    /**
     * @var StudentEnrolment[]|Collection||null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\StudentEnrolment", mappedBy="student")
     */
    private $studentEnrolments;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $lastSchool;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $nextSchool;

    /**
     * @var string|null
     * @ORM\Column(length=50,nullable=true)
     */
    private $departureReason;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $transport;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $transportNotes;

    /**
     * @var ApplicationForm|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\ApplicationForm")
     * @ORM\JoinColumn(name="application_form", referencedColumnName="id", nullable=true)
     */
    private $applicationForm;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $privacy;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true,options={"comment": "Student day type, as specified in the application form."})
     */
    private $dayType;

    /**
     * @var Theme|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Theme")
     * @ORM\JoinColumn(name="theme", referencedColumnName="id", nullable=true)
     */
    private $theme;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(nullable=true,name="graduation_year",referencedColumnName="id")
     * @ORM\OrderBy({"firstDay" = "ASC"})
     */
    private $graduationYear;

    /**
     * @var I18n|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\I18n")
     * @ORM\JoinColumn(name="locale",referencedColumnName="id",nullable=true)
     */
    private $locale;

    /**
     * @var Collection|FamilyMemberStudent[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\FamilyMemberStudent",mappedBy="student")
     */
    private $memberOfFamilies;

    /**
     * Student constructor.
     * @param Person|null $person
     */
    public function __construct(?Person $person = null)
    {
        $this->setPerson($person);
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
     * @return Student
     */
    public function setId(?string $id): Student
    {
        $this->id = $id;
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
     * @return Student
     */
    public function setPerson(?Person $person): Student
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStudentIdentifier(): ?string
    {
        return $this->studentIdentifier;
    }

    /**
     * @param string|null $studentIdentifier
     * @return Student
     */
    public function setStudentIdentifier(?string $studentIdentifier): Student
    {
        $this->studentIdentifier = $studentIdentifier;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getStudentAgreements(): ?array
    {
        return $this->studentAgreements;
    }

    /**
     * @param array|null $studentAgreements
     * @return Student
     */
    public function setStudentAgreements(?array $studentAgreements): Student
    {
        $this->studentAgreements = $studentAgreements;
        return $this;
    }

    /**
     * getStudentEnrolments
     * @return Collection|null
     */
    public function getStudentEnrolments(): ?Collection
    {
        if (null === $this->studentEnrolments) $this->studentEnrolments = new ArrayCollection();

        if ($this->studentEnrolments instanceof PersistentCollection) $this->studentEnrolments->initialize();

        return $this->studentEnrolments;
    }

    /**
     * StudentEnrolments.
     *
     * @param StudentEnrolment[]|Collection $studentEnrolments
     * @return Student
     */
    public function setStudentEnrolments(Collection $studentEnrolments): Student
    {
        $this->studentEnrolments = $studentEnrolments;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastSchool(): ?string
    {
        return $this->lastSchool;
    }

    /**
     * @param null|string $lastSchool
     * @return Student
     */
    public function setLastSchool(?string $lastSchool): Student
    {
        $this->lastSchool = mb_substr($lastSchool, 0, 100);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNextSchool(): ?string
    {
        return $this->nextSchool;
    }

    /**
     * @param null|string $nextSchool
     * @return Student
     */
    public function setNextSchool(?string $nextSchool): Student
    {
        $this->nextSchool = mb_substr($nextSchool, 0, 100);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDepartureReason(): ?string
    {
        return $this->departureReason;
    }

    /**
     * @param null|string $departureReason
     * @return Student
     */
    public function setDepartureReason(?string $departureReason): Student
    {
        $this->departureReason = mb_substr($departureReason, 0, 50);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTransport(): ?string
    {
        return $this->transport;
    }

    /**
     * @param null|string $transport
     * @return Student
     */
    public function setTransport(?string $transport): Student
    {
        $this->transport = mb_substr($transport, 0, 50);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTransportNotes(): ?string
    {
        return $this->transportNotes;
    }

    /**
     * @param null|string $transportNotes
     * @return Student
     */
    public function setTransportNotes(?string $transportNotes): Student
    {
        $this->transportNotes = $transportNotes;
        return $this;
    }

    /**
     * @return ApplicationForm|null
     */
    public function getApplicationForm(): ?ApplicationForm
    {
        return $this->applicationForm;
    }

    /**
     * @param ApplicationForm|null $applicationForm
     * @return Student
     */
    public function setApplicationForm(?ApplicationForm $applicationForm): Student
    {
        $this->applicationForm = $applicationForm;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLockerNumber(): ?string
    {
        return $this->lockerNumber;
    }

    /**
     * @param string|null $lockerNumber
     * @return Student
     */
    public function setLockerNumber(?string $lockerNumber): Student
    {
        $this->lockerNumber = $lockerNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPersonalBackground(): ?string
    {
        return $this->personalBackground;
    }

    /**
     * @param string|null $personalBackground
     * @return Student
     */
    public function setPersonalBackground(?string $personalBackground): Student
    {
        $this->personalBackground = $personalBackground;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getMessengerLastBubble(): ?\DateTimeImmutable
    {
        return $this->messengerLastBubble;
    }

    /**
     * @param \DateTimeImmutable|null $messengerLastBubble
     * @return Student
     */
    public function setMessengerLastBubble(?\DateTimeImmutable $messengerLastBubble): Student
    {
        $this->messengerLastBubble = $messengerLastBubble;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrivacy(): ?string
    {
        return $this->privacy;
    }

    /**
     * @param string|null $privacy
     * @return Student
     */
    public function setPrivacy(?string $privacy): Student
    {
        $this->privacy = $privacy;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayType(): ?string
    {
        return $this->dayType;
    }

    /**
     * @param string|null $dayType
     * @return Student
     */
    public function setDayType(?string $dayType): Student
    {
        $this->dayType = $dayType;
        return $this;
    }

    /**
     * @return Theme|null
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * @param Theme|null $theme
     * @return Student
     */
    public function setTheme(?Theme $theme): Student
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getGraduationYear(): ?AcademicYear
    {
        return $this->graduationYear;
    }

    /**
     * @param AcademicYear|null $graduationYear
     * @return Student
     */
    public function setGraduationYear(?AcademicYear $graduationYear): Student
    {
        $this->graduationYear = $graduationYear;
        return $this;
    }

    /**
     * @return I18n|null
     */
    public function getLocale(): ?I18n
    {
        return $this->locale;
    }

    /**
     * @param I18n|null $locale
     * @return Student
     */
    public function setLocale(?I18n $locale): Student
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return FamilyMemberStudent[]|Collection|null
     */
    public function getMemberOfFamilies(): Collection
    {
        if (null === $this->memberOfFamilies) {
            $this->memberOfFamilies = new ArrayCollection();
        }

        if ($this->memberOfFamilies instanceof PersistentCollection) {
            $this->memberOfFamilies->initialize();
        }

        return $this->memberOfFamilies;
    }

    /**
     * @param FamilyMemberStudent[]|Collection|null $memberOfFamilies
     * @return Student
     */
    public function setMemberOfFamilies($memberOfFamilies): Student
    {
        $this->memberOfFamilies = $memberOfFamilies;
        return $this;
    }

    /**
     * addStudent
     * @param FamilyMemberStudent|null $student
     * @return $this
     * 3/07/2020 14:14
     */
    public function addMemberOfFamily(?FamilyMemberStudent $student): Student
    {
        if (null === $student || $this->getMemberOfFamilies()->contains($student)) {
            return $this;
        }

        $this->memberOfFamilies->add($student);

        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }
}

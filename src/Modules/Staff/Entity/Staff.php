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
namespace App\Modules\Staff\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\CustomFieldData;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Additional\SchoolCommonFields;
use App\Modules\School\Entity\ApplicationForm;
use App\Modules\System\Entity\Theme;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Staff
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Staff\Repository\StaffRepository")
 * @ORM\Table(name="staff",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person", columns={"person"})
 *  },
 *  indexes={
 *      @ORM\Index(name="emergency_contact1",columns={"emergency_contact1"}),
 *      @ORM\Index(name="emergency_contact2",columns={"emergency_contact2"}),
 *      @ORM\Index(name="theme",columns={"theme"}),
 *      @ORM\Index(name="application_form",columns={"application_form"}),
 *      @ORM\Index(name="house",columns={"house"})
 *  }
 * )
 * @UniqueEntity({"person"})
 */
class Staff extends AbstractEntity
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
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",inversedBy="staff")
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\Choice(callback="getTypeList")
     */
    private $type;

    /**
     * @var array
     */
    private static $typeList = [
        'Teaching',
        'Support',
        'Volunteer',
        'Other',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $jobTitle;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private $smartWorkflowHelp = true;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean",options={"default": 0})
     */
    private $firstAidQualified = false;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $firstAidExpiry;

    /**
     * @var string|null
     * @ORM\Column(nullable=true,length=191)
     */
    private $qualifications;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $biography;

    /**
     * @var string|null
     * @ORM\Column(length=100,options={"comment": "Used for group staff when creating a staff directory."},nullable=true)
     */
    private $biographicalGrouping;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",nullable=true)
     */
    private $biographicalGroupingPriority;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(nullable=true,name="emergency_contact1")
     */
    public $emergencyContact1;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(nullable=true,name="emergency_contact2")
     */
    public $emergencyContact2;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $viewCalendarSpaceBooking = false;

    /**
     * @var ApplicationForm|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\ApplicationForm")
     * @ORM\JoinColumn(name="application_form",referencedColumnName="id",nullable=true)
     */
    private $applicationForm;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $vehicleRegistration;

    /**
     * @var Theme|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Theme")
     * @ORM\JoinColumn(name="theme", referencedColumnName="id", nullable=true)
     */
    private $theme;

    /**
     * @var Collection|CustomFieldData[]
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\CustomFieldData",mappedBy="staff",cascade={"all"},orphanRemoval=true)
     */
    private $customData;

    /**
     * Staff constructor.
     * @param Person|null $person
     */
    public function __construct(?Person $person = null)
    {
        $this->setPerson($person)
            ->setCustomData(new ArrayCollection())
            ->setType('Other');
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
     * @return Staff
     */
    public function setId(?string $id): Staff
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
     * setPerson
     * @param Person|null $person
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:19
     */
    public function setPerson(?Person $person, bool $reflect = true): Staff
    {
        if ($reflect and $person instanceof Person)
            $person->setStaff($this, false);
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
     * @return Staff
     */
    public function setType(?string $type): Staff
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @param string|null $jobTitle
     * @return Staff
     */
    public function setJobTitle(?string $jobTitle): Staff
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSmartWorkflowHelp(): bool
    {
        return (bool)$this->smartWorkflowHelp;
    }

    /**
     * @param bool|null $smartWorkflowHelp
     * @return Staff
     */
    public function setSmartWorkflowHelp(?bool $smartWorkflowHelp): Staff
    {
        $this->smartWorkflowHelp = (bool)$smartWorkflowHelp;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFirstAidQualified(): bool
    {
        return (bool)$this->firstAidQualified;
    }

    /**
     * @param bool|null $firstAidQualified
     * @return Staff
     */
    public function setFirstAidQualified(?bool $firstAidQualified): Staff
    {
        $this->firstAidQualified = (bool)$firstAidQualified;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getFirstAidExpiry(): ?\DateTimeImmutable
    {
        return $this->firstAidExpiry;
    }

    /**
     * FirstAidExpiry.
     *
     * @param \DateTimeImmutable|null $firstAidExpiry
     * @return Staff
     */
    public function setFirstAidExpiry(?\DateTimeImmutable $firstAidExpiry): Staff
    {
        $this->firstAidExpiry = $firstAidExpiry;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQualifications(): ?string
    {
        return $this->qualifications;
    }

    /**
     * @param string|null $qualifications
     * @return Staff
     */
    public function setQualifications(?string $qualifications): Staff
    {
        $this->qualifications = $qualifications;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBiography(): ?string
    {
        return $this->biography;
    }

    /**
     * @param string|null $biography
     * @return Staff
     */
    public function setBiography(?string $biography): Staff
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBiographicalGrouping(): ?string
    {
        return $this->biographicalGrouping;
    }

    /**
     * @param string|null $biographicalGrouping
     * @return Staff
     */
    public function setBiographicalGrouping(?string $biographicalGrouping): Staff
    {
        $this->biographicalGrouping = $biographicalGrouping;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBiographicalGroupingPriority(): ?int
    {
        return $this->biographicalGroupingPriority;
    }

    /**
     * @param int|null $biographicalGroupingPriority
     * @return Staff
     */
    public function setBiographicalGroupingPriority(?int $biographicalGroupingPriority): Staff
    {
        $this->biographicalGroupingPriority = $biographicalGroupingPriority;
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
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        if ($this->getPerson())
            return $this->getPerson()->formatName();
        return $this->getId() ?: 'New Record.';
    }

    /**
     * @return Person|null
     */
    public function getEmergencyContact1(): ?Person
    {
        return $this->emergencyContact1;
    }

    /**
     * EmergencyContact1.
     *
     * @param Person|null $emergencyContact1
     * @return Staff
     */
    public function setEmergencyContact1(?Person $emergencyContact1): Staff
    {
        $this->emergencyContact1 = $emergencyContact1;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getEmergencyContact2(): ?Person
    {
        return $this->emergencyContact2;
    }

    /**
     * EmergencyContact2.
     *
     * @param Person|null $emergencyContact2
     * @return Staff
     */
    public function setEmergencyContact2(?Person $emergencyContact2): Staff
    {
        $this->emergencyContact2 = $emergencyContact2;
        return $this;
    }

    /**
     * @return bool
     */
    public function isViewCalendarSpaceBooking(): bool
    {
        return (bool)$this->viewCalendarSpaceBooking;
    }

    /**
     * @param bool|null $viewCalendarSpaceBooking
     * @return Staff
     */
    public function setViewCalendarSpaceBooking(?bool $viewCalendarSpaceBooking): Staff
    {
        $this->viewCalendarSpaceBooking = (bool)$viewCalendarSpaceBooking;
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
     * @return Staff
     */
    public function setApplicationForm(?ApplicationForm $applicationForm): Staff
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
     * @return Staff
     */
    public function setLockerNumber(?string $lockerNumber): Staff
    {
        $this->lockerNumber = $lockerNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVehicleRegistration(): ?string
    {
        return $this->vehicleRegistration;
    }

    /**
     * @param string|null $vehicleRegistration
     * @return Staff
     */
    public function setVehicleRegistration(?string $vehicleRegistration): Staff
    {
        $this->vehicleRegistration = $vehicleRegistration;
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
     * @return Staff
     */
    public function setTheme(?Theme $theme): Staff
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * getCustomData
     * @return Collection
     * @throws \Exception
     * 31/07/2020 09:41
     */
    public function getCustomData(): Collection
    {
        if ($this->customData === null) $this->customData = new ArrayCollection();

        if ($this->customData instanceof PersistentCollection) $this->customData->initialize();

        $iterator = $this->customData->getIterator();
        $iterator->uasort(
            function (CustomFieldData $a, CustomFieldData $b) {
                return $a->getCustomField()->getDisplayOrder() <= $b->getCustomField()->getDisplayOrder() ? -1 : 1;
            }
        );

        $this->customData = new ArrayCollection();
        foreach(iterator_to_array($iterator, false) as $item) {
            $this->addCustomData($item);
        }

        return $this->customData;
    }

    /**
     * @param CustomFieldData[]|Collection|null $customData
     * @return Staff
     */
    public function setCustomData(?Collection $customData): Staff
    {
        $this->customData = $customData;
        return $this;
    }

    /**
     * addCustomData
     * @param CustomFieldData $data
     * @return $this
     * 29/07/2020 11:11
     */
    public function addCustomData(CustomFieldData $data): Staff
    {
        if ($data === null || $this->getCustomData()->containsKey($data->getCustomField()->getId())) return $this;

        $this->customData->set($data->getCustomField()->getId(), $data);

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
     * getFullNameReversed
     * @return string
     * 17/07/2020 10:14
     */
    public function getFullNameReversed(): string
    {
        return $this->getPerson()->getFullNameReversed();
    }

    /**
     * getFullName
     * @return string
     * 17/07/2020 10:14
     */
    public function getFullName(): string
    {
        return $this->getPerson()->getFullName();
    }

    /**
     * isEqualTo
     * @param Staff|null $staff
     * @return bool
     * 2/08/2020 09:22
     */
    public function isEqualTo(?Staff $staff): bool
    {
        return $staff ? $this->getPerson()->isEqualTo($staff->getPerson()) : false ;
    }
}

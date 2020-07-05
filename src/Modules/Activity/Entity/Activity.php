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
use App\Manager\Traits\BooleanList;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\School\Entity\YearGroup;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Action
 * @package App\Modules\Activity\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Activity\Repository\ActivityRepository")
 * @ORM\Table(name="Activity",
 *     indexes={@ORM\Index(name="academic_year", columns={"academic_year"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_academic_year", columns={"name", "academic_year"})})
 * @UniqueEntity({"name","academicYear"})
 */
class Activity extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id",nullable=false)
     */
    private $AcademicYear;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"comment": "Can a parent/student select this for registration?", "default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $registration = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=40)
     * @Assert\NotBlank()
     * @Assert\Length(max=40)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=8,options={"default": "School"})
     * @Assert\Choice(callback="getProviderList")
     */
    private $provider = 'School';

    /**
     * @var array
     */
    private static $providerList = ['School', 'External'];

    /**
     * @var string|null
     * @ORM\Column(length=191, name="activity_type")
     * @Assert\Choice(callback="getActivityTypeList")
     */
    private $activityType;

    /**
     * @var Collection|AcademicYearTerm[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\School\Entity\AcademicYearTerm")
     * @ORM\JoinTable(name="ActivityAcademicYearTerm",
     *      joinColumns={@ORM\JoinColumn(name="activity",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="academic_year_term",referencedColumnName="id")}
     *  )
     */
    private $academicYearTerms;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", name="listing_start", nullable=true)
     */
    private $listingStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", name="listing_end", nullable=true)
     */
    private $listingEnd;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", name="program_start", nullable=true, nullable=true)
     */
    private $programStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", name="program_end", nullable=true)
     */
    private $programEnd;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=true)
     */
    private $payment;

    /**
     * @var string
     * @ORM\Column(length=9, name="payment_firmness", nullable=true, options={"default": "Finalised"})
     * @Assert\Choice(callback="getPaymentFirmnessList")
     */
    private $paymentFirmness = 'Finalised';

    /**
     * @var array
     */
    private static $paymentFirmnessList = ['Finalised', 'Estimated'];

    /**
     * @var string
     * @ORM\Column(length=24, name="payment_type", nullable=true, options={"default": "Entire Programme"})
     * @Assert\Choice(callback="getPaymentTypeList")
     */
    private $paymentType = 'Entire Programme';

    /**
     * @var array
     */
    private static $paymentTypeList = ['Entire Programme','Per Session','Per Week','Per Term'];

    /**
     * @var Collection|YearGroup[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinTable(name="ActivityYearGroup",
     *      joinColumns={@ORM\JoinColumn(name="activity",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="year_group",referencedColumnName="id")}
     *      )
     */
    private $yearGroups;

    /**
     * @var int
     * @ORM\Column(type="smallint", name="max_participants", options={"default": "0"})
     */
    private $maxParticipants = 0;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="ActivityStaff", mappedBy="activity")
     */
    private $staff;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="ActivityStudent", mappedBy="activity")
     */
    private $students;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="ActivitySlot", mappedBy="activity")
     */
    private $slots;

    /**
     * @return array
     */
    public static function getPaymentFirmnessList(): array
    {
        return self::$paymentFirmnessList;
    }

    /**
     * @return array
     */
    public static function getPaymentTypeList(): array
    {
        return self::$paymentTypeList;
    }

    /**
     * @return array
     */
    public static function getProviderList(): array
    {
        return self::$providerList;
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
     * @return Activity
     */
    public function setId(?string $id): Activity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYearTerm[]|Collection|null
     */
    public function getAcademicYearTerms()
    {
        if (null === $this->academicYearTerms) {
            $this->academicYearTerms = new ArrayCollection();
        }

        if ($this->academicYearTerms instanceof PersistentCollection) {
            $this->academicYearTerms->initialize();
        }

        return $this->academicYearTerms;
    }

    /**
     * @param AcademicYearTerm[]|Collection|null $academicYearTerms
     * @return Activity
     */
    public function setAcademicYearTerms($academicYearTerms): Activity
    {
        $this->academicYearTerms = $academicYearTerms;
        return $this;
    }

    /**
     * addAcademicYearTerm
     * @param AcademicYearTerm $term
     * @return $this
     * 3/06/2020 18:09
     */
    public function addAcademicYearTerm(AcademicYearTerm $term): Activity
    {
        if (!$this->getAcademicYearTerms()->contains($term)) {
            $this->academicYearTerms->add($term);
        }

        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->AcademicYear;
    }

    /**
     * @param AcademicYear|null $AcademicYear
     * @return Activity
     */
    public function setAcademicYear(?AcademicYear $AcademicYear): Activity
    {
        $this->AcademicYear = $AcademicYear;
        return $this;
    }

    /**
     * isActive
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * @return string
     */
    public function getActive(): string
    {
        return self::checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return Activity
     */
    public function setActive(?string $active): Activity
    {
        $this->active = in_array($active, self::getBooleanList()) ? $active : 'Y';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    /**
     * @param string|null $registration
     * @return Activity
     */
    public function setRegistration(?string $registration): Activity
    {
        $this->registration = in_array($registration, self::getBooleanList()) ? $registration : 'Y';
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
     * @return Activity
     */
    public function setName(?string $name): Activity
    {
        $this->name = mb_substr($name, 0, 40);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * @param string|null $provider
     * @return Activity
     */
    public function setProvider(?string $provider): Activity
    {
        $this->provider = in_array($provider, self::getProviderList()) ? $provider : 'School';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActivityType(): ?string
    {
        return $this->activityType;
    }

    /**
     * @param string|null $activityType
     * @return Activity
     */
    public function setActivityType(?string $activityType): Activity
    {
        $this->activityType = $activityType;
        return $this;
    }

    /**
     * getActivityTypeList
     * @return array
     */
    public static function getActivityTypeList(): array
    {
        return SettingFactory::getSettingManager()->getSettingByScopeAsArray('Activities', 'activityTypes');
    }

    /**
     * @return YearGroup[]|Collection|null
     */
    public function getYearGroups()
    {
        if (null === $this->yearGroups) {
            $this->yearGroups = new ArrayCollection();
        }

        if ($this->yearGroups instanceof PersistentCollection) {
            $this->yearGroups->initialize();
        }

        return $this->yearGroups;
    }

    /**
     * @param YearGroup[]|Collection|null $yearGroups
     * @return Activity
     */
    public function setYearGroups($yearGroups): Activity
    {
        $this->yearGroups = $yearGroups;

        return $this;
    }

    /**
     * addYearGroup
     * @param YearGroup $yearGroup
     * @return $this
     * 3/06/2020 16:56
     */
    public function addYearGroup(YearGroup $yearGroup): Activity
    {
        if (!$this->getYearGroups()->contains($yearGroup)) {
            $this->yearGroups->add($yearGroup);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxParticipants(): int
    {
        return $this->maxParticipants;
    }

    /**
     * setMaxParticipants
     * @param int|null $maxParticipants
     * @return Activity
     */
    public function setMaxParticipants(?int $maxParticipants): Activity
    {
        $this->maxParticipants = intval($maxParticipants);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): string
    {
        return $this->description ?: '';
    }

    /**
     * @param string|null $description
     * @return Activity
     */
    public function setDescription(?string $description): Activity
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPayment(): ?float
    {
        return $this->payment ? number_format($this->payment, 2) : null;
    }

    /**
     * @param float|null $payment
     * @return Activity
     */
    public function setPayment(?float $payment): Activity
    {
        $this->payment = $payment ? number_format($payment, 2) : null;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     * @return Activity
     */
    public function setPaymentType(string $paymentType): Activity
    {
        $this->paymentType = in_array($paymentType, self::getPaymentTypeList()) ? $paymentType : 'Entire Programme';
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentFirmness(): string
    {
        return $this->paymentFirmness;
    }

    /**
     * @param string $paymentFirmness
     * @return Activity
     */
    public function setPaymentFirmness(string $paymentFirmness): Activity
    {
        $this->paymentFirmness = in_array($paymentFirmness, self::getPaymentFirmnessList()) ? $paymentFirmness : 'Finalised';
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getListingStart(): ?\DateTimeImmutable
    {
        return $this->listingStart;
    }

    /**
     * ListingStart.
     *
     * @param \DateTimeImmutable|null $listingStart
     * @return Activity
     */
    public function setListingStart(?\DateTimeImmutable $listingStart): Activity
    {
        $this->listingStart = $listingStart;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getListingEnd(): ?\DateTimeImmutable
    {
        return $this->listingEnd;
    }

    /**
     * ListingEnd.
     *
     * @param \DateTimeImmutable|null $listingEnd
     * @return Activity
     */
    public function setListingEnd(?\DateTimeImmutable $listingEnd): Activity
    {
        $this->listingEnd = $listingEnd;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getProgramStart(): ?\DateTimeImmutable
    {
        return $this->programStart;
    }

    /**
     * ProgramStart.
     *
     * @param \DateTimeImmutable|null $programStart
     * @return Activity
     */
    public function setProgramStart(?\DateTimeImmutable $programStart): Activity
    {
        $this->programStart = $programStart;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getProgramEnd(): ?\DateTimeImmutable
    {
        return $this->programEnd;
    }

    /**
     * ProgramEnd.
     *
     * @param \DateTimeImmutable|null $programEnd
     * @return Activity
     */
    public function setProgramEnd(?\DateTimeImmutable $programEnd): Activity
    {
        $this->programEnd = $programEnd;
        return $this;
    }

    /**
     * getStaff
     * @return Collection|null
     */
    public function getStaff(): ?Collection
    {
        if (empty($this->staff))
            $this->staff = new ArrayCollection();
        
        if ($this->staff instanceof PersistentCollection)
            $this->staff->initialize();
        
        return $this->staff;
    }

    /**
     * @param Collection|null $staff
     * @return Activity
     */
    public function setStaff(?Collection $staff): Activity
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * getStudents
     * @param string $filter
     * @return Collection|null
     * 3/06/2020 16:48
     */
    public function getStudents(string $filter = ''): ?Collection
    {
        if (empty($this->students))
            $this->students = new ArrayCollection();

        if ($this->students instanceof PersistentCollection)
            $this->students->initialize();

        if ($filter !== '')
        {
            return $this->students->filter(function(ActivityStudent $student) use($filter) {
                if ($student->getStatus() === $filter)
                    return $student;
            });
        }

        return $this->students;
    }

    /**
     * @param Collection|null $students
     * @return Activity
     */
    public function setStudents(?Collection $students): Activity
    {
        $this->students = $students;
        return $this;
    }

    /**
     * getSlots
     * @return Collection|null
     */
    public function getSlots(): ?Collection
    {
        if (empty($this->slots))
            $this->slots = new ArrayCollection();

        if ($this->slots instanceof PersistentCollection)
            $this->slots->initialize();

        return $this->slots;
    }

    /**
     * Slots.
     *
     * @param Collection|null $slots
     * @return Activity
     */
    public function setSlots(?Collection $slots): Activity
    {
        $this->slots = $slots;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            'activityType' => $this->getActivityType(),
            'provider' => $this->getTranslatedProvider(),
            'terms' => $this->getAcademicYearTermListNames(),
            'days' => $this->getDaysOfWeek(),
            'years' => $this->getYears(),
            'cost' => $this->getPayment() ?: TranslationHelper::translate('Free', [], 'Activity'),
            'access' => $this->getAccess(),
            'studentCount' => $this->getStudentCount(),
        ];
    }

    /**
     * getDaysOfWeek
     * @return string
     */
    public function getDaysOfWeek(): string
    {
        $days = [];
        foreach($this->getSlots() as $slot)
            $days[] = TranslationHelper::translate($slot->getDayOfWeek()->getAbbreviation(), [], 'School');
        if (empty($days))
            $days[] = TranslationHelper::translate('None', [], 'School');
        return implode(', ', $days);
    }

    /**
     * getYears
     * @return string
     */
    public function getYears(): string
    {
        $result = [];
        $years = ProviderFactory::create(YearGroup::class)->findAll();
        foreach($this->getYearGroupList() as $id) {
            $id = intval($id);
            $result[] = $years[$id]->getAbbreviation();
        }

        if (empty($result) || count($result) === count($years))
            $result = [TranslationHelper::translate('All', [], 'School')];

        return implode(', ', $result);
    }

    /**
     * getTranslatedProvider
     * @return string|null
     */
    public function getTranslatedProvider(): string
    {
        return $this->getProvider() === 'External' ? TranslationHelper::translate('External', [], 'Activities') : SettingFactory::getSettingManager()->getSettingByScopeAsString('System','organisationAbbreviation');
    }

    /**
     * getAccess
     * @return boolean
     */
    public function getAccess(): bool
    {
        return in_array(SettingFactory::getSettingManager()->getSettingByScopeAsString('Activities', 'access'), ['View', 'Register']);
    }

    /**
     * getStudentCount
     * @return string
     */
    public function getStudentCount(): string
    {
        $result = $this->getStudents()->count();
        $result .= $this->getStudents('Waiting List')->count() > 0 ? '<br/><small><em>' . $this->getStudents('Waiting List')->count() . ' ' . TranslationHelper::translate('Waiting', [], 'Activities') . '</em></small>' : '';
        $result .= $this->getStudents('Pending')->count() > 0 ? '<br/><small><em>' . $this->getStudents('Pending')->count() . ' ' . TranslationHelper::translate('Pending', [], 'Activities') . '</em></small>' : '';

        return strval($result);
    }

    /**
     * create
     * @return array
     * 3/06/2020 16:42
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__activity` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `academic_year` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `active` varchar(1) NOT NULL DEFAULT 'Y',
                    `registration` varchar(1) NOT NULL DEFAULT 'Y' COMMENT 'Can a parent/student select this for registration?',
                    `name` varchar(40) NOT NULL,
                    `provider` varchar(8) NOT NULL DEFAULT 'School',
                    `activity_type` varchar(191) NOT NULL,
                    `listing_start` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `listing_end` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `program_start` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `program_end` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `description` longtext,
                    `payment` decimal(8,2) DEFAULT NULL,
                    `payment_firmness` varchar(9) DEFAULT 'Finalised',
                    `payment_type` varchar(24) DEFAULT 'Entire Programme',
                    `max_participants` smallint(6) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name_academic_year` (`name`,`academic_year`),
                    KEY `academic_year` (`academic_year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
                "CREATE TABLE `__prefix__ActivityAcademicYearTerm` (
                    `activity` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `academic_year_term` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    PRIMARY KEY (`activity`,`academic_year_term`),
                    KEY `IDX_F630C44DAC74095A` (`activity`),
                    KEY `IDX_F630C44DF4CDF1A0` (`academic_year_term`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
                "CREATE TABLE `__prefix__ActivityYearGroup` (
                    `activity` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `year_group` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    PRIMARY KEY (`activity`,`year_group`),
                    KEY `IDX_1A58648AAC74095A` (`activity`),
                    KEY `IDX_1A58648ACEAE6A12` (`year_group`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 3/06/2020 16:43
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Activity`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`);
                ALTER TABLE `__prefix__ActivityAcademicYearTerm`
                    ADD CONSTRAINT FOREIGN KEY (`activity`) REFERENCES `__prefix__Activity` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`academic_year_term`) REFERENCES `__prefix__AcademicYearTerm` (`id`);
                ALTER TABLE `__prefix__ActivityYearGroup`
                    ADD CONSTRAINT FOREIGN KEY (`activity`) REFERENCES `__prefix__Activity` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`year_group`) REFERENCES `__prefix__YearGroup` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 3/06/2020 16:43
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
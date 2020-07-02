<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: __prefix__
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 08:17
 */
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Validator\Constraints as ASSERT;

/**
 * Class Person
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\PersonRepository")
 * @ORM\Table(name="Person",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="student",columns={"student"}),
 *      @ORM\UniqueConstraint(name="security_user",columns={"security_user"}),
 *      @ORM\UniqueConstraint(name="staff",columns={"staff"}),
 *      @ORM\UniqueConstraint(name="contact",columns={"contact"}),
 *      @ORM\UniqueConstraint(name="personal_documentation",columns={"personal_documentation"}),
 *      @ORM\UniqueConstraint(name="parent",columns={"parent"})}
 *     )
 * @UniqueEntity("student")
 * @UniqueEntity("security_user")
 * @UniqueEntity("staff")
 * @UniqueEntity("contact")
 * @UniqueEntity("personal_documentation")
 * @UniqueEntity("staff")
 * @ORM\HasLifecycleCallbacks()
 */
class Person extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     * @ASSERT\Choice(callback="getTitleList")
     */
    private $title;

    /**
     * @var array
     */
    private static $titleList = [
        'Ms',
        'Miss',
        'Mr',
        'Mrs',
        'Dr',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private $surname;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private $firstName;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private $preferredName;

    /**
     * @var string|null
     * @ORM\Column(length=150,nullable=true)
     * @ASSERT\NotBlank()
     */
    private $officialName;

    /**
     * @var string|null
     * @ORM\Column(length=60,name="name_in_characters",nullable=true)
     */
    private $nameInCharacters;

    /**
     * @var Collection|CustomFieldData[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\CustomFieldData",mappedBy="person")
     */
    private $additionalFields;

    /**
     * @var string|null
     * @ORM\Column(length=16,options={"default": "Unspecified"})
     * @ASSERT\Choice(callback="getGenderAssert")
     */
    private $gender = 'Unspecified';

    /**
     * @var array
     */
    private static $genderList = [
        'Female' => 'F',
        'Male' => 'M',
        'Other' => 'Other',
        'Unspecified' => 'Unspecified',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=16, options={"default": "Full"})
     * @ASSERT\Choice(callback="getStatusList")
     */
    private $status = 'Full';

    /**
     * @var array
     */
    private static $statusList = [
        'Full',
        'Expected',
        'Left',
        'Pending Approval',
    ];

    /**
     * @var Student|null
     * @ORM\OneToOne(targetEntity="App\Modules\Student\Entity\Student",mappedBy="person",fetch="EAGER")
     * @ORM\JoinColumn(name="student",nullable=true)
     */
    private $student;

    /**
     * @var ParentContact|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\ParentContact", mappedBy="person")
     * @ORM\JoinColumn(name="parent",referencedColumnName="id")
     */
    private $parent;

    /**
     * @var Contact|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Contact", mappedBy="person")
     * @ORM\JoinColumn(name="contact",referencedColumnName="id")
     */
    private $contact;

    /**
     * @var PersonalDocumentation|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\PersonalDocumentation",mappedBy="person")
     * @ORM\JoinColumn(name="personal_documentation",referencedColumnName="id")
     */
    private $personalDocumentation;

    /**
     * @var Staff|null
     * @ORM\OneToOne(targetEntity="App\Modules\Staff\Entity\Staff", mappedBy="person")
     * @ORM\JoinColumn(name="staff",referencedColumnName="id")
     */
    private $staff;

    /**
     * @var SecurityUser|null
     * @ORM\OneToOne(targetEntity="App\Modules\Security\Entity\SecurityUser", mappedBy="person")
     * @ORM\JoinColumn(name="security_user",referencedColumnName="id")
     */
    private $securityUser;

    /**
     * Person constructor.
     */
    public function __construct()
    {
        $this->setStatus('Expected');
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * getGenderList
     *
     * @return array
     */
    public static function getGenderList(): array
    {
        return self::$genderList;
    }

    /**
     * getGenderAssert
     * @return array
     */
    public static function getGenderAssert(): array
    {
        return self::$genderList;
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
     * @return Person
     */
    public function setId(?string $id): Person
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title = in_array(rtrim($this->title,'.'), self::$titleList) ? rtrim($this->title,'.') : '';
    }

    /**
     * @param null|string $title
     * @return Person
     */
    public function setTitle(?string $title): Person
    {
        $this->title = in_array($title, self::getTitleList()) ? $title : '';
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param null|string $surname
     * @return Person
     */
    public function setSurname(?string $surname): Person
    {
        $this->surname = mb_substr($surname, 0, 60);
        $this->setOfficialName(null);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param null|string $firstName
     * @return Person
     */
    public function setFirstName(?string $firstName): Person
    {
        $this->firstName = mb_substr($firstName, 0, 60);

        if (null === $this->getPreferredName())
            return $this->setPreferredName($firstName);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPreferredName(): ?string
    {
        return $this->preferredName;
    }

    /**
     * @param null|string $preferredName
     * @return Person
     */
    public function setPreferredName(?string $preferredName): Person
    {
        $this->preferredName = mb_substr($preferredName, 0, 60);

        if (null === $this->getFirstName())
            return $this->setFirstName($preferredName);

        $this->setOfficialName(null);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    /**
     * @param null|string $officialName
     * @return Person
     */
    public function setOfficialName(?string $officialName): Person
    {
        if ($officialName === null && !empty($this->getSurname()) && !empty($this->getFirstName()))
            $officialName = $this->getFirstName() . ' ' . $this->getSurname();
        $this->officialName = mb_substr($officialName, 0, 150);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNameInCharacters(): ?string
    {
        return $this->nameInCharacters;
    }

    /**
     * @param null|string $nameInCharacters
     * @return Person
     */
    public function setNameInCharacters(?string $nameInCharacters): Person
    {
        $this->nameInCharacters = mb_substr($nameInCharacters, 0, 60);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getGender(): ?string
    {
        return $this->gender = in_array($this->gender, self::getGenderList()) ? $this->gender : 'Unspecified';
    }

    /**
     * @param null|string $gender
     * @return Person
     */
    public function setGender(?string $gender): Person
    {
        $this->gender = in_array($gender, self::getGenderList()) ? $gender : 'Unspecified';
        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status = in_array($this->status, self::getStatusList()) ? $this->status : 'Full' ;
    }

    /**
     * @param null|string $status
     * @return Person
     */
    public function setStatus(?string $status): Person
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Full' ;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields = is_array($this->fields) ? $this->fields : [];
    }

    /**
     * @param string|array $fields
     * @return Person
     */
    public function setFields(array $fields): Person
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * addField
     * @param $field
     * @param $value
     * @return Person
     */
    public function addField($key, $value): Person
    {
        $this->getFields();

        $this->fields[$key] = $value;
        return $this;
    }

    /**
     * mergeFields
     * @param array $fields
     * @return Person
     */
    public function mergeFields(array $fields): Person
    {
        foreach($fields as $field)
            if (! isset($this->getFields()[$field->getId()]))
                $this->fields[$field->getId()] = null;
        ksort($this->fields);

        return $this;
    }

    /**
     * isSystemAdmin
     * @return bool
     */
    public function isSystemAdmin(): bool
    {
        return in_array('ROLE_SYSTEM_ADMIN', $this->getSecurityRoles() ?: []);
    }

    /**
     * renderImage
     * @param int $dimension
     * @param bool $asHeight
     * @return string
     * @deprecated 4/Sep 2019: Please use Person::photo()
     */
    public function renderImage(int $dimension = 75, bool $asHeight = false)
    {
        trigger_error('Deprecated 4/Sep 2019: Please use Person::photo()', E_USER_DEPRECATED);
        return $this->photo($dimension);
    }

    /**
     * formatName
     * @param bool|array $options
     * @param bool $reverse
     * @param bool $informal
     * @param bool $initial
     * @param bool $title
     * @return string
     */
    public function formatName($options = true, bool $reverse = false, bool $informal = false, bool $initial = false, bool $title = false): string
    {
        if (is_array($options)) {
            return PersonNameManager::formatName($this, $options);
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        trigger_error(sprintf('Use of discrete settings for format name is deprecated since 1/Dec 2019.  Use the options configuration. Called from %s on line %s.',$trace[1]['file'], $trace[1]['line']), E_USER_DEPRECATED);
        $format = [];
        $format['preferred'] = $options;
        $format['reverse'] = $reverse;
        $format['informal'] = $informal;
        $format['initial'] = $initial;
        $format['title'] = $title;

        return PersonNameManager::formatName($this, $format);
    }

    /**
     * getFullName
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getSurname();
    }

    /**
     * getFullNameReversed
     * @return string
     */
    public function getFullNameReversed()
    {
        return $this->getSurname().': '.$this->getFirstName();
    }

    /**
     * getLocale
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->getI18nPersonal();
    }

    /**
     * @return array
     */
    public static function getTitleList(bool $forChoice = false): array
    {
        if ($forChoice)
        {
            $choice = [];
            foreach(self::$titleList as $name)
                $choice[$name] = $name;
            return $choice;
        }
        return self::$titleList;
    }

    /**
     * Returns an HTML <img> based on the supplied photo path, using a placeholder image if none exists. Size may be either 75 or 240 at this time.
     *
     * @param int|string $size
     * @param string $class
     * @return array
     */
    public function photo($size = 75, string $class = '')
    {
        $class .= ' inline-block shadow bg-white border border-gray-600 ';

        $path = $this->getImage240(true);

        switch ($size) {
            case 240:
            case 'lg':
                $class .= 'w-48 sm:w-64 max-w-full p-1 mx-auto';
                $imageSize = 240;
                break;
            case 75:
            case 'md':
                $class .= 'w-20 lg:w-24 p-1';
                $imageSize = 75;
                break;
            case 'sm':
                $class .= 'w-12 sm:w-20 p-px sm:p-1';
                $imageSize = 75;
                break;
            default:
                $imageSize = $size;
        }

        if (!file_exists(__DIR__ . '/../../public/' .$path) ) {
            $path = '/themes/{theme}/img/anonymous_'.$imageSize.'.jpg';
        }

        $result['class'] = $class;
        $result['asset'] = $path;
        $result['fileName'] = $path;
        $result['title'] = $this->formatName(['informal' => true]);
        $result['fileExists'] = true;
        return $result;
    }

    /**
     * Display an icon if this user's birthday is within the next week.
     *
     * @return string
     */
    public function birthdayIcon()
    {
        if (!$this->getDob() instanceof \DateTime)
            return '';

        $dob = new \DateTime(date('Y-') . $this->getDob()->format('m-d'));
        $today = new \DateTime('now');
        if ($today->format('Ymd') > $dob->format('Ymd'))
            return '';

        $daysUntilNextBirthday = $today->diff($dob)->days;
        if ($daysUntilNextBirthday >= 8)
            return '';

        // HEY SHORTY IT'S YOUR BIRTHDAY! (or Close)
        $result['colour'] = 'text-pink-800';
        $result['params']['{name}'] = $this->getPreferredName();
        $result['params']['count'] = $daysUntilNextBirthday;
        if ($daysUntilNextBirthday > 0)
            $result['colour'] = 'text-gray-800';

        return $result;
    }

    /**
     * getEmergencyRelationshipList
     * @return array
     */
    public static function getEmergencyRelationshipList():array
    {
        return [
            'Parent',
            'Spouse',
            'Offspring',
            'Friend',
            'Other Relation',
            'Doctor',
            'Other',
        ];
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getOfficialName() ?: $this->getSurname().' '.$this->getPreferredName();
    }

    /**
     * getLanguageList
     * @return array|string[]
     */
    public static function getLanguageList()
    {
        $languages = Languages::getNames();
        return array_flip($languages);
    }

    /**
     * uniqueIdentifier
     * @return string
     */
    public function uniqueIdentifier(): string
    {
        if (is_string($this->getStudentIdentifier()) && $this->getStudentIdentifier() !== '')
            return $this->getStudentIdentifier();

        if (is_string($this->getUsername()) && $this->getUsername() !== '')
                return $this->getUsername();

        return str_pad($this->getId(), 10, '0', STR_PAD_LEFT);
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 26/06/2020 10:56
     */
    public function toArray(?string $name = NULL): array
    {
        if ($name === 'short') {
            return [
                $this->getId(),
                $this->getSurname(),
                $this->getPreferredName(),
                $this->getFirstName(),
                $this->getEmail(),
                $this->getUsername(),
            ];
        }
        return [
            'fullName' => $this->formatName(['informal' => true, 'reverse' => true]),
            'photo' => $this->getImage240(false) ? ImageHelper::getRelativeImageURL($this->getImage240(false)) : '/build/static/DefaultPerson.png',
            'status' => TranslationHelper::translate($this->getStatus()),
            '_status' => $this->getStatus(),
            'family' => $this->getFamilyName(),
            'family_id' => $this->getFamilyId(),
            'username' => $this->getUsername(),
            'roles' => rtrim(implode(', ', SecurityHelper::translateRoles($this->getSecurityRoles() ?: [])),', '),
            'canDelete' => $this->canDelete(),
            'start_date' => $this->getDateStart() === null || $this->getDateStart() <= new \DateTimeImmutable() ? false : true,
            'end_date' => $this->getDateEnd() === null || $this->getDateEnd() >= new \DateTimeImmutable() ? false : true,
            'email' => $this->getEmail(),
            'studentIdentifier' => $this->getStudentIdentifier() ?: '',
            'phone' => $this->getPersonalPhone(),
            'rego' => $this->getVehicleRegistration() ?: '',
            'name' => $this->getSurname().' '.$this->getFirstName().' '.$this->getPreferredName(),
        ];
    }

    /**
     * hasRole
     * @param string $role
     * @return bool
     * 10/06/2020 12:19
     */
    public function hasRole(string $role): bool
    {
        $roles = SecurityHelper::getHierarchy()->getReachableRoleNames($this->getSecurityRolesAsStrings());
        return in_array($role, $roles);
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        if ($this->getStatus() === 'Full')
            return false;
        if ($this->getStudentEnrolments()->count() > 0)
            return false;
        if ($this->getChildren()->count() > 0)
            return false;
        if ($this->getAdults()->count() > 0)
            return false;
        return true;
    }

    /**
     * isEqualTo
     * @param Person $person
     * @return bool
     */
    public function isEqualTo(Person $person): bool
    {
        if ($person->getId() !== $this->getId())
            return false;
        if ($person->getUsername() !== $this->getUsername())
            return false;
        if ($person->getEmail() !== $this->getEmail())
            return false;
        if ($person->getPassword() !== $this->getPassword())
            return false;
        if ($person->getStudentIdentifier() !== $this->getStudentIdentifier())
            return false;
        return true;
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Person` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `title` varchar(5) DEFAULT NULL,
                    `surname` varchar(60) NOT NULL,
                    `first_name` varchar(60) NOT NULL,
                    `preferred_name` varchar(60) NOT NULL,
                    `official_name` varchar(150) DEFAULT NULL,
                    `name_in_characters` varchar(60) DEFAULT NULL,
                    `gender` varchar(16) NOT NULL DEFAULT 'Unspecified',
                    `status` varchar(16) NOT NULL DEFAULT 'Full',
                    `dob` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `email` varchar(75) DEFAULT NULL,
                    `email_alternate` varchar(75) DEFAULT NULL,
                    `image_240` varchar(191) DEFAULT NULL,
                    `physical_address` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `postal_address` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `personal_phone` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `website` varchar(191) DEFAULT NULL,
                    `language_first` varchar(5) DEFAULT NULL,
                    `language_second` varchar(5) DEFAULT NULL,
                    `language_third` varchar(5) DEFAULT NULL,
                    `country_of_birth` varchar(3) DEFAULT NULL,
                    `birth_certificate_scan` varchar(191) DEFAULT NULL,
                    `ethnicity` varchar(191) DEFAULT NULL,
                    `citizenship1` varchar(3) DEFAULT NULL,
                    `citizenship1_passport` varchar(30) DEFAULT NULL,
                    `citizenship1_passport_scan` varchar(191) DEFAULT NULL,
                    `citizenship2` varchar(3) DEFAULT NULL,
                    `citizenship2_passport` varchar(30) DEFAULT NULL,
                    `religion` varchar(30) DEFAULT NULL,
                    `national_card_number` varchar(30) DEFAULT NULL,
                    `national_card_scan` varchar(191) DEFAULT NULL,
                    `residency_status` varchar(191) DEFAULT NULL,
                    `visa_expiry_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `profession` varchar(90) DEFAULT NULL,
                    `employer` varchar(90) DEFAULT NULL,
                    `job_title` varchar(90) CHARACTER SET utf8mb4 DEFAULT NULL,
                    `emergency_contact1` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `emergency_contact2` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `student_identifier` varchar(20) DEFAULT NULL,
                    `date_start` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `date_end` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `last_school` varchar(100) DEFAULT NULL,
                    `next_school` varchar(100) DEFAULT NULL,
                    `departure_reason` varchar(50) DEFAULT NULL,
                    `transport` varchar(191) DEFAULT NULL,
                    `transport_notes` longtext CHARACTER SET utf8mb4,
                    `calendar_feed_personal` varchar(191) DEFAULT NULL,
                    `view_calendar_school` varchar(1) NOT NULL DEFAULT 'Y',
                    `view_calendar_personal` varchar(1) NOT NULL DEFAULT 'Y',
                    `view_calendar_space_booking` varchar(1) NOT NULL DEFAULT 'N',
                    `locker_number` varchar(20) DEFAULT NULL,
                    `vehicle_registration` varchar(20) DEFAULT NULL,
                    `personal_background` varchar(191) DEFAULT NULL,
                    `messenger_last_bubble` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `privacy` longtext,
                    `day_type` varchar(191) DEFAULT NULL COMMENT 'Student day type, as specified in the application form.',
                    `student_agreements` longtext,
                    `google_api_refresh_token` varchar(191) DEFAULT NULL,
                    `receive_notification_emails` varchar(1) NOT NULL DEFAULT 'Y',
                    `fields` longtext COMMENT 'Serialised array of custom field values(DC2Type:array)',
                    `house` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `class_of_academic_year` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `application_form` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `personal_theme` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `personal_i18n` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `student` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `username` (`username`),
                    UNIQUE INDEX `student` (`student`)
                    KEY `house` (`house`),
                    KEY `theme` (`personal_theme`),
                    KEY `i18n` (`personal_i18n`),
                    KEY `academic_year_class_of` (`class_of_academic_year`),
                    KEY `application_form` (`application_form`),
                    KEY `emergency_contact1` (`emergency_contact1`),
                    KEY `emergency_contact2` (`emergency_contact2`),
                    KEY `personal_phone` (`personal_phone`),
                    KEY `username_email` (`username`,`email`),
                    KEY `physical_address` (`physical_address`),
                    KEY `postal_address` (`postal_address`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
                "CREATE TABLE `__prefix__PersonalPhone` (
                    `person` CHAR(36) NOT NULL,
                    `phone` CHAR(36) NOT NULL,
                    PRIMARY KEY (`person`,`phone`),
                    KEY `person` (`person`),
                    KEY `phone` (`phone`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Person`
                    ADD CONSTRAINT FOREIGN KEY (`house`) REFERENCES `__prefix__House` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`class_of_academic_year`) REFERENCES `__prefix__AcademicYear` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`application_form`) REFERENCES `__prefix__ApplicationForm` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`personal_theme`) REFERENCES `__prefix__Theme` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`personal_i18n`) REFERENCES `__prefix__I18n` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`personal_phone`) REFERENCES `__prefix__Phone` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`postal_address`) REFERENCES `__prefix__Address` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`physical_address`) REFERENCES `__prefix__Address` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`emergency_contact1`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`emergency_contact2`) REFERENCES `__prefix__Person` (`id`);
                ALTER TABLE `__prefix__PersonalPhone`
                    ADD CONSTRAINT FOREIGN KEY (`phone`) REFERENCES `__prefix__Phone` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getHumanisedRole
     * @return string
     * 10/06/2020 11:57
     */
    public function getHumanisedRole(): string
    {
        if ($this->isStudent()) {
            return 'Student';
        }
        if ($this->isParent()) {
            return 'Parent';
        }
        if ($this->isStaff()) {
            return 'Staff';
        }
        return 'Other';
    }

    /**
     * isStudent
     * @return bool
     * 10/06/2020 11:59
     */
    public function isStudent(): bool
    {
        return $this->getStudent() instanceof Student;
    }

    /**
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * setStudent
     * @param Student|null $student
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:22
     */
    public function setStudent(?Student $student, bool $reflect = true): Person
    {
        $this->student = $student;
        if ($reflect && $student instanceof Student) {
            $student->setPerson($this, false);
        }
        return $this;
    }

    /**
     * isParent
     * @return bool
     * 10/06/2020 11:59
     */
    public function isParent(): bool
    {
        return $this->getParent() instanceof ParentContact;
    }

    /**
     * @return ParentContact|null
     */
    public function getParent(): ?ParentContact
    {
        return $this->parent;
    }

    /**
     * setParent
     * @param ParentContact|null $parent
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:13
     */
    public function setParent(?ParentContact $parent, bool $reflect = true): Person
    {
        $this->parent = $parent;
        if ($reflect && $parent instanceof ParentContact) {
            $parent->setPerson($this, false);
        }
        return $this;
    }

    /**
     * @return Contact|null
     */
    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    /**
     * setContact
     * @param Contact|null $contact
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:10
     */
    public function setContact(?Contact $contact, bool $reflect = true): Person
    {
        $this->contact = $contact;
        if ($reflect) {
            $contact->setPerson($this, false);
        }
        return $this;
    }

    /**
     * @return PersonalDocumentation|null
     */
    public function getPersonalDocumentation(): ?PersonalDocumentation
    {
        return $this->personalDocumentation;
    }

    /**
     * setPersonalDocumentation
     * @param PersonalDocumentation|null $personalDocumentation
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:10
     */
    public function setPersonalDocumentation(?PersonalDocumentation $personalDocumentation, bool $reflect = true): Person
    {
        $this->personalDocumentation = $personalDocumentation;
        if ($reflect) {
            $personalDocumentation->setPerson($this, false);
        }
        return $this;
    }

    /**
     * isTeacher
     * @return bool
     * 18/06/2020 15:25
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('ROLE_TEACHER');
    }

    /**
     * isRegistrar
     * @return bool
     * 18/06/2020 15:25
     */
    public function isRegistrar(): bool
    {
        return $this->hasRole('ROLE_REGISTRAR');
    }

    /**
     * isSupport
     * @return bool
     * 20/06/2020 11:50
     */
    public function isSupport(): bool
    {
        return $this->hasRole('ROLE_SUPPORT');
    }

    /**
     * isRegistrar
     * @return bool
     * 18/06/2020 15:25
     */
    public function isPrincipal(): bool
    {
        return $this->hasRole('ROLE_PRINCIPAL');
    }

    /**
     * isRegistrar
     * @return bool
     * 18/06/2020 15:25
     */
    public function isHeadTeacher(): bool
    {
        return $this->hasRole('ROLE_HEAD_TEACHER');
    }

    /**
     * getVersion
     * @return string
     * 10/06/2020 11:57
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * isStaff
     * @return bool
     * 2/07/2020 09:17
     */
    public function isStaff(): bool
    {
        return $this->getStaff() instanceof Staff;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * setStaff
     * @param Staff|null $staff
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:19
     */
    public function setStaff(?Staff $staff, bool $reflect = true): Person
    {
        $this->staff = $staff;
        if ($reflect && $staff instanceof Staff) {
            $staff->setPerson($this, false);
        }
        return $this;
    }

    /**
     * isSecurityUser
     * @return bool
     * 2/07/2020 09:17
     */
    public function isSecurityUser(): bool
    {
        return $this->getSecurityUser() instanceof SecurityUser;
    }

    /**
     * @return SecurityUser|null
     */
    public function getSecurityUser(): ?SecurityUser
    {
        return $this->securityUser;
    }

    /**
     * setSecurityUser
     * @param SecurityUser|null $securityUser
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:19
     */
    public function setSecurityUser(?SecurityUser $securityUser, bool $reflect = true): Person
    {
        $this->securityUser = $securityUser;
        if ($reflect && $securityUser instanceof SecurityUser) {
            $securityUser->setPerson($this, false);
        }
        return $this;
    }
}

<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
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
use App\Modules\People\Validator\StaffStudent;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
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
 *      @ORM\UniqueConstraint(name="care_giver",columns={"care_giver"})}
 *     )
 * @UniqueEntity("student")
 * @UniqueEntity("securityUser")
 * @UniqueEntity("staff")
 * @UniqueEntity("contact")
 * @UniqueEntity("personalDocumentation")
 * @UniqueEntity("careGiver")
 * @StaffStudent()
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
     * @ASSERT\Choice(callback="getTitleListNull")
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
     * @ORM\OneToOne(targetEntity="App\Modules\Student\Entity\Student",mappedBy="person",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="student",nullable=true)
     */
    private $student;

    /**
     * @var CareGiver|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\CareGiver",mappedBy="person",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="care_giver",referencedColumnName="id")
     */
    private $careGiver;

    /**
     * @var Contact|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Contact",mappedBy="person",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="contact",referencedColumnName="id")
     */
    private $contact;

    /**
     * @var PersonalDocumentation|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\PersonalDocumentation",mappedBy="person",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="personal_documentation",referencedColumnName="id")
     */
    private $personalDocumentation;

    /**
     * @var Staff|null
     * @ORM\OneToOne(targetEntity="App\Modules\Staff\Entity\Staff",mappedBy="person",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="staff",referencedColumnName="id")
     */
    private $staff;

    /**
     * @var SecurityUser|null
     * @ORM\OneToOne(targetEntity="App\Modules\Security\Entity\SecurityUser",mappedBy="person",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="security_user",referencedColumnName="id")
     */
    private $securityUser;

    /**
     * Person constructor.
     */
    public function __construct()
    {
        $this->setStatus('Expected');
        $this->setContact(new Contact($this));
        $this->setSecurityUser(new SecurityUser($this));
        $this->setPersonalDocumentation(new PersonalDocumentation($this));
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
     *
     * 24/08/2020 09:50
     * @param string $style
     * @param string|null $role
     * @return string
     */
    public function formatName(string $style = 'General', ?string $role = null): string
    {
        return PersonNameManager::formatName($this, str_replace(' ', '', $role ?: $this->getHumanisedRole()), $style);
    }

    /**
     * getFullName
     * @return string
     */
    public function getFullName()
    {
        return $this->formatName('Standard');
    }

    /**
     * getFullNameReversed
     * @return string
     */
    public function getFullNameReversed()
    {
        return $this->formatName('Reversed');
    }

    /**
     * getTitleList
     * @param bool $forChoice
     * @return array|string[]
     * 25/07/2020 12:27
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
     * getTitleListNull
     * @return array
     * 28/07/2020 14:35
     */
    public static function getTitleListNull(): array
    {
        return array_merge(self::$titleList, [null,'']);
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
        $result['title'] = $this->formatName('Preferred');
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
            'CareGiver',
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
                $this->getSecurityUser() ? $this->getSecurityUser()->getUsername() : null,
            ];
        }
        return [
            'fullName' => $this->formatName('Reversed'),
            'photo' => $this->getImage240(false) ? ImageHelper::getRelativeImageURL($this->getPersonalDocumentation()->getPersonalImage(false)) : '/build/static/DefaultPerson.png',
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
        if ($this->getSecurityUser()) {
            return $this->getSecurityUser()->hasRole($role);
        }

        return false;
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        if ($this->getStatus() === 'Full')
            return false;
        if ($this->getStudent())
            return false;
        if ($this->getStaff())
            return false;
        if ($this->getCareGiver())
            return false;
        return true;
    }

    /**
     * isEqualTo
     * @param Person|null $person
     * @return bool
     * 16/07/2020 09:49
     */
    public function isEqualTo(?Person $person): bool
    {
        if (is_null($person)) {
            return false;
        }
        if ($person->getId() !== $this->getId()) {
            return false;
        }
        if ($person->getFullName() !== $this->getFullName()) {
            return false;
        }
        return true;
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
        if ($this->isCareGiver()) {
            return 'Care Giver';
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
     * isCareGiver
     * @return bool
     * 10/06/2020 11:59
     */
    public function isCareGiver(): bool
    {
        return $this->getCareGiver() instanceof CareGiver;
    }

    /**
     * @return CareGiver|null
     */
    public function getCareGiver(): ?CareGiver
    {
        return $this->careGiver;
    }

    /**
     * setCareGiver
     * @param CareGiver|null $careGiver
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:13
     */
    public function setCareGiver(?CareGiver $careGiver, bool $reflect = true): Person
    {
        if ($reflect && $careGiver instanceof CareGiver) {
            $careGiver->setPerson($this, false);
        }
        $this->careGiver = $careGiver;
        return $this;
    }

    /**
     * @return Contact
     */
    public function getContact(): Contact
    {
        if ($this->contact->getPerson() === null) {
            $this->contact->setPerson($this);
        }
        return $this->contact;
    }

    /**
     * @param Contact|null $contact
     * @param bool $reflect
     * @return Person
     */
    public function setContact(Contact $contact): Person
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * reflectContact
     *
     * 24/08/2020 11:30
     * @param Contact $contact
     * @return Person
     */
    public function reflectContact(Contact $contact): Person
    {
        $contact->setPerson($this);
        return $this->setContact($contact);
    }

    /**
     * getPersonalDocumentation
     *
     * 22/08/2020 11:06
     * @return PersonalDocumentation
     */
    public function getPersonalDocumentation(): PersonalDocumentation
    {
        return $this->personalDocumentation = $this->personalDocumentation ?: new PersonalDocumentation($this);
    }

    /**
     * setPersonalDocumentation
     *
     * 22/08/2020 11:13
     * @param PersonalDocumentation|null $personalDocumentation
     * @return $this
     */
    public function setPersonalDocumentation(?PersonalDocumentation $personalDocumentation): Person
    {
        $this->personalDocumentation = $personalDocumentation ?: new PersonalDocumentation($this);
        $personalDocumentation->setPerson($this);

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

    /**
     * getEmail
     * @return string|null
     * 3/07/2020 11:15
     */
    public function getEmail(): ?string
    {
        if($this->getContact()) {
            return $this->getContact()->getEmail();
        }
        return null;
    }

    /**
     * canByStaff
     * @return bool
     * 19/07/2020 12:31
     */
    public function canBeStaff()
    {
        if ($this->getId() === null) return false;
        if ($this->isStaff() || $this->isStudent()) return false;
        return true;
    }

    /**
     * canBeStudent
     * @return bool
     * 19/07/2020 12:31
     */
    public function canBeStudent()
    {
        if ($this->getId() === null) return false;
        if ($this->isStaff() || $this->isStudent() || $this->isCareGiver()) return false;
        return true;
    }

    /**
     * canBeCareGiver
     * @return bool
     * 19/07/2020 12:34
     */
    public function canBeCareGiver()
    {
        if ($this->getId() === null) return false;
        if ($this->isStudent() || $this->isCareGiver()) return false;
        return true;
    }

    /**
     * createContact
     * 21/07/2020 08:53
     * @ORM\PostLoad()
     * @ORM\PrePersist()
     */
    public function createContactDocumentation()
    {
        if ($this->getContact() === null) {
            $contact = new Contact($this);
            ProviderFactory::getEntityManager()->persist($contact);
        }
        if ($this->getPersonalDocumentation() === null) {
            $documentation = new PersonalDocumentation($this);
            ProviderFactory::getEntityManager()->persist($documentation);
        }
        if ($this->getSecurityUser() === null) {
            $user = new SecurityUser($this);
            $user->setCanLogin(false);
            ProviderFactory::getEntityManager()->persist($user);
        }
    }

    /**
     * getInitial
     * @return string
     * 25/07/2020 12:21
     */
    public function getInitial(): string
    {
        return $this->getFirstName() ? substr($this->getFirstName(), 0, 1) : '';
    }
}

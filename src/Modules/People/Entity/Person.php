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
     * isSystemAdmin
     * @return bool
     */
    public function isSystemAdmin(): bool
    {
        return $this->getSecurityUser()->isSuperUser();
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
            'photo' => $this->getPersonalDocumentation()->getPersonalImage() ? ImageHelper::getRelativeImageURL($this->getPersonalDocumentation()->getPersonalImage(false)) : '/build/static/DefaultPerson.png',
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
     *
     * 29/08/2020 09:41
     * @param Student|null $student
     * @return $this
     */
    public function setStudent(?Student $student): Person
    {
        if (null !== $student && null === $student->getPerson()) return $this->reflectStudent($student);
        $this->student = $student;
        return $this;
    }

    /**
     * reflectStudent
     *
     * 29/08/2020 12:14
     * @param Student $student
     * @return Person
     */
    public function reflectStudent(Student $student): Person
    {
        $this->student = $student->setPerson($this);
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
     *
     * 29/08/2020 09:44
     * @param CareGiver|null $careGiver
     * @return $this
     */
    public function setCareGiver(?CareGiver $careGiver): Person
    {
        if (null !== $careGiver && null === $careGiver->getPerson()) return $this->reflectCareGiver($careGiver);
        $this->careGiver = $careGiver;
        return $this;
    }

    /**
     * reflectCareGiver
     *
     * 29/08/2020 09:44
     * @param CareGiver $careGiver
     * @return Person
     */
    public function reflectCareGiver(CareGiver $careGiver): Person
    {
        $this->careGiver = $careGiver->setPerson($this);
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
     * setContact
     *
     * 29/08/2020 10:06
     * @param Contact $contact
     * @return $this
     */
    public function setContact(Contact $contact): Person
    {
        if (null === $contact->getPerson()) return $this->reflectContact($contact);
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
        $this->contact = $contact->setPerson($this);
        return $this;
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
     * @param PersonalDocumentation $personalDocumentation
     * @return Person
     */
    public function setPersonalDocumentation(PersonalDocumentation $personalDocumentation): Person
    {
        if (null === $personalDocumentation->getPerson()) return $this->reflectPersonalDocumentation($personalDocumentation);
        $this->personalDocumentation = $personalDocumentation;
        return $this;
    }

    /**
     * reflectPersonalDocumentation
     *
     * 29/08/2020 10:07
     * @param PersonalDocumentation $personalDocumentation
     * @return Person
     */
    public function reflectPersonalDocumentation(PersonalDocumentation $personalDocumentation): Person
    {
        $this->personalDocumentation = $personalDocumentation->setPerson($this);
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
     * getStaff
     *
     * 29/08/2020 09:32
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * setStaff
     *
     * 29/08/2020 09:32
     * @param Staff|null $staff
     * @return $this
     */
    public function setStaff(?Staff $staff): Person
    {
        if (null !== $staff && null === $staff->getPerson()) return $this->reflectStaff($staff);
        $this->staff = $staff;
        return $this;
    }

    /**
     * reflectStaff
     *
     * 29/08/2020 12:15
     * @param Staff $staff
     * @return Person
     */
    public function reflectStaff(Staff $staff): Person
    {
        $this->staff = $staff->setPerson($this);
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
     *
     * 29/08/2020 10:44
     * @param SecurityUser|null $securityUser
     * @return $this
     */
    public function setSecurityUser(?SecurityUser $securityUser): Person
    {
        if (null !== $securityUser && null === $securityUser->getPerson()) return $this->reflectSecurityUser($securityUser);
        $this->securityUser = $securityUser;
        return $this;
    }

    /**
     * reflectSecurityUser
     *
     * 30/08/2020 08:07
     * @param SecurityUser $securityUser
     * @return $this
     */
    public function reflectSecurityUser(SecurityUser $securityUser): Person
    {
        $securityUser->setPerson($this);
        $this->securityUser = $securityUser;
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

    /**
     * getFullNameReversed
     *
     * 25/08/2020 13:53
     * @return string
     */
    public function getFullNameReversed(): string
    {
        return $this->formatName('Reversed');
    }

    /**
     * getFullName
     *
     * 25/08/2020 13:53
     * @param string $style
     * @return string
     */
    public function getFullName(string $style = 'Standard'): string
    {
        return $this->formatName($style);
    }

}

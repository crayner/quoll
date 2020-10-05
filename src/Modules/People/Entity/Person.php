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
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
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
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private ?string $surname;

    /**
     * @var string|null
     * @ORM\Column(length=60,nullable=false)
     * @ASSERT\NotBlank()
     */
    private ?string $firstName;

    /**
     * @var string|null
     * @ORM\Column(length=60, nullable=false)
     * @ASSERT\NotBlank()
     */
    private ?string $preferredName;

    /**
     * @var string|null
     * @ORM\Column(length=150,nullable=true)
     * @ASSERT\NotBlank()
     */
    private ?string $officialName;

    /**
     * @var string|null
     * @ORM\Column(length=60,name="name_in_characters",nullable=true)
     */
    private ?string $nameInCharacters;

    /**
     * @var string|null
     * @ORM\Column(length=16,options={"default": "Unspecified"})
     * @ASSERT\Choice(callback="getGenderAssert")
     */
    private string $gender = 'Unspecified';

    /**
     * @var array
     */
    private static array $genderList = [
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
    private string $status = 'Full';

    /**
     * @var array
     */
    private static array $statusList = [
        'Full',
        'Expected',
        'Left',
        'Pending Approval',
    ];

    /**
     * @var Student|null
     * @ORM\OneToOne(targetEntity="App\Modules\Student\Entity\Student",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="student",nullable=true)
     * @ASSERT\Valid()
     */
    private ?Student $student = null;

    /**
     * @var CareGiver|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\CareGiver",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="care_giver",referencedColumnName="id")
     * @ASSERT\Valid()
     */
    private ?CareGiver $careGiver = null;

    /**
     * @var Contact
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Contact",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="contact",referencedColumnName="id")
     * @ASSERT\NotBlank()
     * @ASSERT\Valid()
     */
    private Contact $contact;

    /**
     * @var PersonalDocumentation|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\PersonalDocumentation",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="personal_documentation",referencedColumnName="id")
     * @ASSERT\NotBlank()
     * @ASSERT\Valid()
     */
    private ?PersonalDocumentation $personalDocumentation = null;

    /**
     * @var Staff|null
     * @ORM\OneToOne(targetEntity="App\Modules\Staff\Entity\Staff",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="staff",referencedColumnName="id")
     * @ASSERT\Valid()
     */
    private ?Staff $staff = null;

    /**
     * @var SecurityUser|null
     * @ORM\OneToOne(targetEntity="App\Modules\Security\Entity\SecurityUser",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(name="security_user",referencedColumnName="id",nullable=false)
     * @ASSERT\NotBlank()
     * @ASSERT\Valid()
     */
    private ?SecurityUser $securityUser = null;

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
        return $this->title = in_array(rtrim($this->title,'.'), self::getTitleList()) ? rtrim($this->title,'.') : '';
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
     * getSurname
     *
     * 9/09/2020 08:59
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname = isset($this->surname) ? $this->surname : null;
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
        return $this->firstName = isset($this->firstName) ? $this->firstName : null;
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
        return $this->preferredName = isset($this->preferredName) ? $this->preferredName : null;
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
     * getOfficialName
     *
     * 9/09/2020 08:57
     * @return string|null
     */
    public function getOfficialName(): ?string
    {
        return $this->officialName = isset($this->officialName) ? $this->officialName : null;
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
        return $this->hasRole('ROLE_SYSTEM_ADMIN');
    }

    /**
     * isSuperUser
     *
     * 5/10/2020 14:51
     * @return bool
     */
    public function isSuperUser(): bool
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
     *
     * 6/09/2020 07:40
     * @param bool $forChoice
     * @return array
     */
    public static function getTitleList(bool $forChoice = false): array
    {
        $titleList = SettingFactory::getSettingManager()->get('People', 'titleList');

        if ($forChoice)
        {
            $choices = [];
            foreach($titleList as $name)
                $choices[$name] = $name;
            return $choices;
        }
        return $titleList;
    }

    /**
     * getTitleListNull
     *
     * 6/09/2020 07:43
     * @return array
     */
    public static function getTitleListNull(): array
    {
        return array_merge(SettingFactory::getSettingManager()->get('People', 'titleList'), [null,'']);
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
     * isTeacher
     * @return bool
     * 18/06/2020 15:25
     */
    public function isTeacher(): bool
    {
        return $this->isStaff() && ($this->getStaff()->getType() === 'Teaching' || $this->hasRole('ROLE_TEACHER'));
    }

    /**
     * isRegistrar
     * @return bool
     * 18/06/2020 15:25
     */
    public function isRegistrar(): bool
    {
        return $this->isStaff() && $this->hasRole('ROLE_REGISTRAR');
    }

    /**
     * isSupport
     * @return bool
     * 20/06/2020 11:50
     */
    public function isSupport(): bool
    {
        return $this->isStaff() && $this->hasRole('ROLE_SUPPORT');
    }

    /**
     * isRegistrar
     * @return bool
     * 18/06/2020 15:25
     */
    public function isPrincipal(): bool
    {
        return $this->isStaff() && $this->hasRole('ROLE_PRINCIPAL');
    }

    /**
     * isRegistrar
     * @return bool
     * 18/06/2020 15:25
     */
    public function isHeadTeacher(): bool
    {
        return $this->isStaff() && $this->hasRole('ROLE_HEAD_TEACHER');
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
     * isSecurityUser
     * @return bool
     * 2/07/2020 09:17
     */
    public function isSecurityUser(): bool
    {
        return $this->getSecurityUser() instanceof SecurityUser;
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

    /**
     * getFullNameReversedWithRollGroup
     *
     * 4/09/2020 12:02
     * @return string
     */
    public function getFullNameReversedWithRollGroup(): string
    {
        if ($this->isStudent()) {
            foreach ($this->getStudent()->getStudentRollGroups() as $se) {
                if ($se->getAcademicYear()->isEqualTo(AcademicYearHelper::getCurrentAcademicYear())) {
                    return '('.$se->getRollGroup()->getAbbreviation() . ') ' . $this->formatName('Reversed', 'Student');
                }
            }
        }
        return $this->getFullNameReversed();
    }

    /**
     * isStudent
     *
     * 6/09/2020 07:53
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->getStudent() instanceof Student;
    }

    /**
     * getStudent
     *
     * 6/09/2020 09:05
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * @param Student|null $student
     * @return Person
     */
    public function setStudent(?Student $student): Person
    {
        $this->student = $student;
        return $this;
    }

    /**
     * isCareGiver
     *
     * 6/09/2020 08:25
     * @return bool
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
     * @param CareGiver|null $careGiver
     * @return Person
     */
    public function setCareGiver(?CareGiver $careGiver): Person
    {
        $this->careGiver = $careGiver;
        return $this;
    }

    /**
     * getContact
     *
     * 6/09/2020 08:00
     * @return Contact|null
     */
    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    /**
     * @param Contact|null $contact
     * @return Person
     */
    public function setContact(?Contact $contact): Person
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * getPersonalDocumentation
     *
     * 6/09/2020 09:05
     * @return PersonalDocumentation|null
     */
    public function getPersonalDocumentation(): ?PersonalDocumentation
    {
        return $this->personalDocumentation;
    }

    /**
     * setPersonalDocumentation
     *
     * 6/09/2020 07:35
     * @param PersonalDocumentation|null $personalDocumentation
     * @return $this
     */
    public function setPersonalDocumentation(?PersonalDocumentation $personalDocumentation): Person
    {
        $this->personalDocumentation = $personalDocumentation;
        return $this;
    }

    /**
     * isStaff
     *
     * 6/09/2020 07:54
     * @return bool
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
     * @param Staff|null $staff
     * @return Person
     */
    public function setStaff(?Staff $staff): Person
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * @return SecurityUser|null
     */
    public function getSecurityUser(): ?SecurityUser
    {
        return $this->securityUser;
    }

    /**
     * @param SecurityUser|null $securityUser
     * @return Person
     */
    public function setSecurityUser(?SecurityUser $securityUser): Person
    {
        $this->securityUser = $securityUser;
        return $this;
    }

}

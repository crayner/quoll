<?php
/**
 * Created by PhpStorm.
 *
* Quoll
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
use App\Manager\Traits\BooleanList;
use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\People\Validator\Username;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\ApplicationForm;
use App\Modules\School\Entity\House;
use App\Modules\Security\Manager\RoleHierarchy;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Entity\Theme;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use App\Validator as ASSERTLOCAL;
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
 * @ORM\Table(
 *     name="Person",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="username", columns={"username"})},
 *     indexes={@ORM\Index(name="username_email", columns={"username", "email"}),
 *     @ORM\Index(name="personal_phone",columns={"personal_phone"}),
 *     @ORM\Index(name="house",columns={"house"}),
 *     @ORM\Index(name="physical_address",columns={"physical_address"}),
 *     @ORM\Index(name="postal_address",columns={"postal_address"}),
 *     @ORM\Index(name="academic_year_class_of",columns={"class_of_academic_year"}),
 *     @ORM\Index(name="application_form",columns={"application_form"}),
 *     @ORM\Index(name="theme",columns={"personal_theme"}),
 *     @ORM\Index(name="emergency_contact1",columns={"emergency_contact1"}),
 *     @ORM\Index(name="emergency_contact2",columns={"emergency_contact2"}),
 *     @ORM\Index(name="i18n",columns={"personal_i18n"})}
 *     )
 * @UniqueEntity(fields={"studentIdentifier"},ignoreNull=true)
 * @UniqueEntity(fields={"username"},ignoreNull=true)
 * @ORM\HasLifecycleCallbacks()
 * @Username()
 */
class Person extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * Person constructor.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->courseClassPerson = new ArrayCollection();
        $this->studentEnrolments = new ArrayCollection();
        $this->additionalPhones = new ArrayCollection();
        $this->setStatus('Expected')
            ->setSecurityRoles([])
            ->setCanLogin('N')
            ->setPasswordForceReset('N');

    }

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

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
     * @var string|null
     * @ORM\Column(length=5)
     */
    private $title = '';

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
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private $surname;

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
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private $firstName;

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
     * @var string|null
     * @ORM\Column(length=60)
     * @ASSERT\NotBlank()
     */
    private $preferredName;

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
     * @var string|null
     * @ORM\Column(length=150)
     * @ASSERT\NotBlank()
     */
    private $officialName = '';

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
     * @var string|null
     * @ORM\Column(length=60, name="name_in_characters")
     */
    private $nameInCharacters = '';

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
     * @var string|null
     * @ORM\Column(length=16, options={"default": "Unspecified"})
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
     * @var string|null
     * @ORM\Column(length=75,nullable=true)
     */
    private $username;

    /**
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param null|string $username
     * @return Person
     */
    public function setUsername(?string $username): Person
    {
        $this->username = $username ? mb_substr($username, 0, 75) : null;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191, name="password",nullable=true)
     */
    private $password;

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     * @return Person
     */
    public function setPassword(?string $password): Person
    {
        $this->password = mb_substr($password, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N", "comment": "Force user to reset password on next login."})
     */
    private $passwordForceReset = 'N';

    /**
     * isPasswordForceReset
     * @return bool
     */
    public function isPasswordForceReset(): bool
    {
        return $this->getPasswordForceReset() === 'Y';
    }

    /**
     * @return null|string
     */
    public function getPasswordForceReset(): ?string
    {
        return $this->passwordForceReset = in_array($this->passwordForceReset, self::getBooleanList()) ? $this->passwordForceReset : 'N' ;
    }

    /**
     * @param null|string $passwordForceReset
     * @return Person
     */
    public function setPasswordForceReset(?string $passwordForceReset): Person
    {
        $this->passwordForceReset = in_array($passwordForceReset, self::getBooleanList()) ? $passwordForceReset : 'N' ;
        return $this;
    }

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
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $canLogin = 'Y';

    /**
     * isCanLogin
     * @return bool
     */
    public function isCanLogin(): bool
    {
        return $this->getCanLogin() === 'Y' ? true : false;
    }

    /**
     * @return null|string
     */
    public function getCanLogin(): ?string
    {
        return $this->canLogin = in_array($this->canLogin, self::getBooleanList()) ? $this->canLogin : 'N' ;
    }

    /**
     * @param null|string $canLogin
     * @return Person
     */
    public function setCanLogin(?string $canLogin): Person
    {
        $this->canLogin = in_array($canLogin, self::getBooleanList()) ? $canLogin : 'N' ;
        return $this;
    }

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $securityRoles;

    /**
     * @return array
     */
    public function getSecurityRoles(): array
    {
        return $this->securityRoles ?: [];
    }

    /**
     * @param array|null $securityRoles
     * @return Person
     */
    public function setSecurityRoles(?array $securityRoles): Person
    {
        $this->securityRoles = $securityRoles;
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $dob;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDob(): ?\DateTimeImmutable
    {
        return $this->dob;
    }

    /**
     * Dob.
     *
     * @param \DateTimeImmutable|null $dob
     * @return Person
     */
    public function setDob(?\DateTimeImmutable $dob): Person
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=75, nullable=true)
     */
    private $email;

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     * @return Person
     */
    public function setEmail(?string $email): Person
    {
        $this->email = mb_substr($email, 0, 75);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=75,nullable=true)
     */
    private $emailAlternate;

    /**
     * @return null|string
     */
    public function getEmailAlternate(): ?string
    {
        return $this->emailAlternate;
    }

    /**
     * @param null|string $emailAlternate
     * @return Person
     */
    public function setEmailAlternate(?string $emailAlternate): Person
    {
        $this->emailAlternate = mb_substr($emailAlternate, 0, 75);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191, nullable=true)
     * @ASSERTLOCAL\ReactImage(
     *     maxSize = "750k",
     *     mimeTypes = {"image/jpg","image/gif","image/png","image/jpeg"},
     *     maxRatio = 0.84,
     *     minRatio = 0.7,
     *     minWidth = 240,
     *     minHeight = 320,
     *     maxWidth = 720,
     *     maxHeight = 960
     * )
     */
    private $image_240;

    /**
     * getImage240
     * @param bool $default
     * @return string|null
     */
    public function getImage240(bool $default = true): ?string
    {
        if (in_array($this->image_240, ['', null]) && $default)
            return ImageHelper::getRelativePath('/build/static/DefaultPerson.png');
        return $this->image_240;
    }

    /**
     * @param null|string $image_240
     * @return Person
     */
    public function setImage240(?string $image_240): Person
    {
        $this->image_240 = ImageHelper::getRelativePath($image_240);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=15,name="last_ip_address")
     */
    private $lastIPAddress = '';

    /**
     * @return null|string
     */
    public function getLastIPAddress(): ?string
    {
        return $this->lastIPAddress;
    }

    /**
     * @param null|string $lastIPAddress
     * @return Person
     */
    public function setLastIPAddress(?string $lastIPAddress): Person
    {
        $this->lastIPAddress = mb_substr($lastIPAddress, 0, 15);
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $lastTimestamp;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastTimestamp(): ?\DateTimeImmutable
    {
        return $this->lastTimestamp;
    }

    /**
     * @param \DateTimeImmutable|null $lastTimestamp
     */
    public function setLastTimestamp(?\DateTimeImmutable $lastTimestamp): void
    {
        $this->lastTimestamp = $lastTimestamp;
    }

    /**
     * @var string|null
     * @ORM\Column(length=15,nullable=true,name="last_fail_ip_address")
     */
    private $lastFailIPAddress;

    /**
     * @return null|string
     */
    public function getLastFailIPAddress(): ?string
    {
        return $this->lastFailIPAddress;
    }

    /**
     * @param null|string $lastFailIPAddress
     * @return Person
     */
    public function setLastFailIPAddress(?string $lastFailIPAddress): Person
    {
        $this->lastFailIPAddress = mb_substr($lastFailIPAddress, 0, 15);
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $lastFailTimestamp;

    /**
     * isLastFailTimestampTooOld
     * @param int $timeout
     * @return bool
     */
    public function isLastFailTimestampTooOld(int $timeout = 1200): bool
    {
        if (null === $this->getLastFailTimestamp() || $this->getLastFailTimestamp()->getTimestamp() < strtotime('-'.$timeout.' seconds'))
            return true;
        return false;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastFailTimestamp(): ?\DateTimeImmutable
    {
        return $this->lastFailTimestamp;
    }

    /**
     * @param \DateTimeImmutable|null $lastFailTimestamp
     */
    public function setLastFailTimestamp(?\DateTimeImmutable $lastFailTimestamp): void
    {
        $this->lastFailTimestamp = $lastFailTimestamp;
    }

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",nullable=true,options={"default": "0"})
     */
    private $failCount;

    /**
     * incFailCount
     * @return int
     */
    public function incFailCount(): int
    {
        $failCount = intval($this->failCount);
        $this->setFailCount(++$failCount);
        return $this->getFailCount();
    }

    /**
     * @return int|null
     */
    public function getFailCount(): int
    {
        return intval($this->failCount);
    }

    /**
     * @param int|null $failCount
     * @return Person
     */
    public function setFailCount(?int $failCount): Person
    {
        $this->failCount = $failCount;
        return $this;
    }

    /**
     * @var Address|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Address")
     * @ORM\JoinColumn(name="physical_address",referencedColumnName="id",nullable=true)
     */
    private $physicalAddress;

    /**
     * @return Address|null
     */
    public function getPhysicalAddress(): ?Address
    {
        return $this->physicalAddress;
    }

    /**
     * Address.
     *
     * @param Address|null $address
     * @return Person
     */
    public function setPhysicalAddress(?Address $address): Person
    {
        $this->physicalAddress = $address;
        return $this;
    }

    /**
     * @var Address|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Address")
     * @ORM\JoinColumn(name="postal_address",referencedColumnName="id",nullable=true)
     */
    private $postalAddress;

    /**
     * @return Address|null
     */
    public function getPostalAddress(): ?Address
    {
        return $this->postalAddress;
    }

    /**
     * PostalAddress.
     *
     * @param Address|null $postalAddress
     * @return Person
     */
    public function setPostalAddress(?Address $postalAddress): Person
    {
        $this->postalAddress = $postalAddress;
        return $this;
    }

    /**
     * @var Phone|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Phone")
     * @ORM\JoinColumn(name="personal_phone", referencedColumnName="id",nullable=true)
     */
    private $personalPhone;

    /**
     * @return Phone|null
     */
    public function getPersonalPhone(): ?Phone
    {
        return $this->personalPhone;
    }

    /**
     * PersonalPhone.
     *
     * @param Phone|null $personalPhone
     * @return Person
     */
    public function setPersonalPhone(?Phone $personalPhone): Person
    {
        $this->personalPhone = $personalPhone;
        return $this;
    }

    /**
     * @var Collection|null
     * @ORM\ManyToMany(targetEntity="App\Modules\People\Entity\Phone")
     * @ORM\JoinTable(name="PersonalPhone",
     *      joinColumns={@ORM\JoinColumn(name="person",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="phone",referencedColumnName="id")}
     *      )
     */
    private $additionalPhones;

    /**
     * @return Collection
     */
    public function getAdditionalPhones(): Collection
    {
        if (null === $this->additionalPhones)
            $this->additionalPhones = new ArrayCollection();

        if ($this->additionalPhones instanceof PersistentCollection)
            $this->additionalPhones->initialize();

        return $this->additionalPhones;
    }

    /**
     * AdditionalPhones.
     *
     * @param Collection|null $additionalPhones
     * @return Person
     */
    public function setAdditionalPhones(?Collection $additionalPhones): Person
    {
        $this->additionalPhones = $additionalPhones;
        return $this;
    }

    /**
     * addAdditionalPhone
     * @param Phone $phone
     * @return $this
     */
    public function addAdditionalPhone(Phone $phone): Person
    {
        if ($this->getAdditionalPhones()->contains($phone))
            return $this;

        $this->additionalPhones->add($phone);

        return $this;
    }

    /**
     * removeAdditionalPhone
     * @param Phone $phone
     * @return $this
     */
    public function removeAdditionalPhone(Phone $phone): Person
    {
        $this->getAdditionalPhones()->removeElement($phone);
        return $this;
    }


    public function getPhoneList(bool $includeFamily = false): array
    {
        $result = [];
        if ($this->getPersonalPhone())
        {
            $result[] = $this->getPersonalPhone()->__toString();
        }
        foreach($this->getAdditionalPhones() as $phone) {
            $result[] = $phone->__toString();
        }
        if ($includeFamily) {
            foreach (ProviderFactory::create(Phone::class)->getFamilyPhonesOfPerson($this) as $phone) {
                $result[] = $phone->__toString();
            }
        }
        return $result;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $website = '';

    /**
     * @return null|string
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param null|string $website
     * @return Person
     */
    public function setWebsite(?string $website): Person
    {
        $this->website = mb_substr($website, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=5)
     */
    private $languageFirst = '';

    /**
     * @return null|string
     */
    public function getLanguageFirst(): ?string
    {
        return $this->languageFirst;
    }

    /**
     * @param null|string $languageFirst
     * @return Person
     */
    public function setLanguageFirst(?string $languageFirst): Person
    {
        $this->languageFirst = mb_substr($languageFirst, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=5)
     */
    private $languageSecond = '';

    /**
     * @return null|string
     */
    public function getLanguageSecond(): ?string
    {
        return $this->languageSecond;
    }

    /**
     * @param null|string $languageSecond
     * @return Person
     */
    public function setLanguageSecond(?string $languageSecond): Person
    {
        $this->languageSecond = mb_substr($languageSecond, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=5)
     */
    private $languageThird = '';

    /**
     * @return null|string
     */
    public function getLanguageThird(): ?string
    {
        return $this->languageThird;
    }

    /**
     * @param null|string $languageThird
     * @return Person
     */
    public function setLanguageThird(?string $languageThird): Person
    {
        $this->languageThird = mb_substr($languageThird, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=3)
     * @ASSERT\Country(alpha3=true)
     */
    private $countryOfBirth = '';

    /**
     * @return null|string
     */
    public function getCountryOfBirth(): ?string
    {
        return $this->countryOfBirth;
    }

    /**
     * @param null|string $countryOfBirth
     * @return Person
     */
    public function setCountryOfBirth(?string $countryOfBirth): Person
    {
        $this->countryOfBirth = mb_substr($countryOfBirth, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191)
     * @ASSERTLOCAL\ReactFile(
     *     maxSize = "2048k",
     *     mimeTypes = {"image/*","application/pdf","application/x-pdf"}
     * )
     */
    private $birthCertificateScan = '';

    /**
     * @return null|string
     */
    public function getBirthCertificateScan(): ?string
    {
        return $this->birthCertificateScan;
    }

    /**
     * @param null|string $birthCertificateScan
     * @return Person
     */
    public function setBirthCertificateScan(?string $birthCertificateScan): Person
    {
        $this->birthCertificateScan = mb_substr($birthCertificateScan, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $ethnicity = '';

    /**
     * @var array
     */
    private static $ethnicityList = [
        'Australian Peoples',
        'New Zealand Peoples',
        'Melanesian and Papuan',
        'Micronesian',
        'Polynesian',
        'British',
        'Irish',
        'Western European',
        'Northern European',
        'Southern European',
        'South Eastern European',
        'Eastern European',
        'Arab',
        'Jewish',
        'Peoples of the Sudan',
        'Other North African and Middle Eastern',
        'Mainland South-East Asian',
        'Maritime South-East Asian',
        'Chinese Asian',
        'Other North-East Asian',
        'Southern Asian',
        'Central Asian',
        'North American',
        'South American',
        'Central American',
        'Caribbean Islander',
        'Central and West African',
        'Southern and East African'
    ];

    /**
     * getEthnicityList
     * @return array
     */
    public static function getEthnicityList(): array
    {
        if (($x = ProviderFactory::create(Setting::class)->getSettingByScopeAsArray('User Admin', 'ethnicity')) !== []) {
            return $x;
        }
        return self::$ethnicityList;
    }

    /**
     * @return null|string
     */
    public function getEthnicity(): ?string
    {
        return $this->ethnicity;
    }

    /**
     * @param null|string $ethnicity
     * @return Person
     */
    public function setEthnicity(?string $ethnicity): Person
    {
        $this->ethnicity = mb_substr($ethnicity, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=3)
     */
    private $citizenship1;

    /**
     * @return null|string
     */
    public function getCitizenship1(): ?string
    {
        return $this->citizenship1;
    }

    /**
     * @param null|string $citizenship1
     * @return Person
     */
    public function setCitizenship1(?string $citizenship1): Person
    {
        $this->citizenship1 = mb_substr($citizenship1, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=30, name="citizenship1_passport")
     */
    private $citizenship1Passport = '';

    /**
     * getCitizenship1Passport
     * @return string|null
     */
    public function getCitizenship1Passport(): ?string
    {
        return $this->citizenship1Passport;
    }

    /**
     * setCitizenship1Passport
     * @param string|null $citizenship1Passport
     * @return Person
     */
    public function setCitizenship1Passport(?string $citizenship1Passport): Person
    {
        $this->citizenship1Passport = mb_substr($citizenship1Passport, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191, name="citizenship1_passport_scan")
     * @ASSERTLOCAL\ReactFile(
     *     maxSize = "2048k",
     *     mimeTypes = {"image/*","application/pdf","application/x-pdf"}
     * )
     */
    private $citizenship1PassportScan = '';

    /**
     * @return null|string
     */
    public function getCitizenship1PassportScan(): ?string
    {
        return $this->citizenship1PassportScan;
    }

    /**
     * @param null|string $citizenship1PassportScan
     * @return Person
     */
    public function setCitizenship1PassportScan(?string $citizenship1PassportScan): Person
    {
        $this->citizenship1PassportScan = mb_substr($citizenship1PassportScan, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=3)
     */
    private $citizenship2;

    /**
     * @return null|string
     */
    public function getCitizenship2(): ?string
    {
        return $this->citizenship2;
    }

    /**
     * @param null|string $citizenship2
     * @return Person
     */
    public function setCitizenship2(?string $citizenship2): Person
    {
        $this->citizenship2 = mb_substr($citizenship2, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=30, name="citizenship2_passport")
     */
    private $citizenship2Passport = '';

    /**
     * @return null|string
     */
    public function getCitizenship2Passport(): ?string
    {
        return $this->citizenship2Passport;
    }

    /**
     * @param null|string $citizenship2Passport
     * @return Person
     */
    public function setCitizenship2Passport(?string $citizenship2Passport): Person
    {
        $this->citizenship2Passport = mb_substr($citizenship2Passport, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=30)
     */
    private $religion = '';

    /**
     * @return null|string
     */
    public function getReligion(): ?string
    {
        return $this->religion;
    }

    /**
     * @param null|string $religion
     * @return Person
     */
    public function setReligion(?string $religion): Person
    {
        $this->religion = mb_substr($religion, 0, 30);
        return $this;
    }

    /**
     * getReligionList
     * @return array
     */
    public static function getReligionList(): array
    {
        return ProviderFactory::create(Setting::class)->getSettingByScopeAsArray('User Admin', 'religions');
    }

    /**
     * @var string|null
     * @ORM\Column(length=30, name="national_card_number")
     */
    private $nationalIDCardNumber = '';

    /**
     * @return null|string
     */
    public function getNationalIDCardNumber(): ?string
    {
        return $this->nationalIDCardNumber;
    }

    /**
     * @param null|string $nationalIDCardNumber
     * @return Person
     */
    public function setNationalIDCardNumber(?string $nationalIDCardNumber): Person
    {
        $this->nationalIDCardNumber = mb_substr($nationalIDCardNumber, 0, 30);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191, name="national_card_scan")
     */
    private $nationalIDCardScan = '';

    /**
     * @return null|string
     */
    public function getNationalIDCardScan(): ?string
    {
        return $this->nationalIDCardScan;
    }

    /**
     * @param null|string $nationalIDCardScan
     * @return Person
     */
    public function setNationalIDCardScan(?string $nationalIDCardScan): Person
    {
        $this->nationalIDCardScan = mb_substr($nationalIDCardScan, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $residencyStatus = '';

    /**
     * @return null|string
     */
    public function getResidencyStatus(): ?string
    {
        return $this->residencyStatus;
    }

    /**
     * @param null|string $residencyStatus
     * @return Person
     */
    public function setResidencyStatus(?string $residencyStatus): Person
    {
        $this->residencyStatus = mb_substr($residencyStatus, 0, 191);
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(nullable=true, type="date_immutable")
     */
    private $visaExpiryDate;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getVisaExpiryDate(): ?\DateTimeImmutable
    {
        return $this->visaExpiryDate;
    }

    /**
     * VisaExpiryDate.
     *
     * @param \DateTimeImmutable|null $visaExpiryDate
     * @return Person
     */
    public function setVisaExpiryDate(?\DateTimeImmutable $visaExpiryDate): Person
    {
        $this->visaExpiryDate = $visaExpiryDate;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=90)
     */
    private $profession = '';

    /**
     * @return null|string
     */
    public function getProfession(): ?string
    {
        return $this->profession;
    }

    /**
     * @param null|string $profession
     * @return Person
     */
    public function setProfession(?string $profession): Person
    {
        $this->profession = mb_substr($profession, 0, 90);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=90)
     */
    private $employer = '';

    /**
     * @return null|string
     */
    public function getEmployer(): ?string
    {
        return $this->employer;
    }

    /**
     * @param null|string $employer
     * @return Person
     */
    public function setEmployer(?string $employer): Person
    {
        $this->employer = mb_substr($employer, 0, 90);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=90, name="jobTitle")
     */
    private $jobTitle = '';

    /**
     * @return null|string
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @param null|string $jobTitle
     * @return Person
     */
    public function setJobTitle(?string $jobTitle): Person
    {
        $this->jobTitle = mb_substr($jobTitle, 0, 90);
        return $this;
    }

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(nullable=true,name="emergency_contact1")
     */
    public $emergencyContact1;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(nullable=true,name="emergency_contact2")
     */
    public $emergencyContact2;

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
     * @return Person
     */
    public function setEmergencyContact1(?Person $emergencyContact1): Person
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
     * @return Person
     */
    public function setEmergencyContact2(?Person $emergencyContact2): Person
    {
        $this->emergencyContact2 = $emergencyContact2;
        return $this;
    }

    /**
     * @var House|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\House")
     * @ORM\JoinColumn(nullable=true, name="house", referencedColumnName="id")
     */
    private $house;

    /**
     * @return House|null
     */
    public function getHouse(): ?House
    {
        return $this->house;
    }

    /**
     * @param House|null $house
     * @return Person
     */
    public function setHouse(?House $house): Person
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $studentIdentifier;

    /**
     * @return null|string
     */
    public function getStudentIdentifier(): ?string
    {
        return $this->studentIdentifier;
    }

    /**
     * @param null|string $studentIdentifier
     * @return Person
     */
    public function setStudentIdentifier(?string $studentIdentifier): Person
    {
        $this->studentIdentifier = mb_substr($studentIdentifier, 0, 10) ?: null;
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $dateStart;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateStart(): ?\DateTimeImmutable
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTimeImmutable|null $dateStart
     * @return Person
     */
    public function setDateStart(?\DateTimeImmutable $dateStart): Person
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $dateEnd;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateEnd(): ?\DateTimeImmutable
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTimeImmutable|null $dateEnd
     * @return Person
     */
    public function setDateEnd(?\DateTimeImmutable $dateEnd): Person
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(nullable=true, name="class_of_academic_year", referencedColumnName="id")
     */
    private $academicYearClassOf;

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYearClassOf(): ?AcademicYear
    {
        return $this->academicYearClassOf;
    }

    /**
     * @param AcademicYear|null $academicYearClassOf
     * @return Person
     */
    public function setAcademicYearClassOf(?AcademicYear $academicYearClassOf): Person
    {
        $this->academicYearClassOf = $academicYearClassOf;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $lastSchool = '';

    /**
     * @return null|string
     */
    public function getLastSchool(): ?string
    {
        return $this->lastSchool;
    }

    /**
     * @param null|string $lastSchool
     * @return Person
     */
    public function setLastSchool(?string $lastSchool): Person
    {
        $this->lastSchool = mb_substr($lastSchool, 0, 100);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $nextSchool = '';

    /**
     * @return null|string
     */
    public function getNextSchool(): ?string
    {
        return $this->nextSchool;
    }

    /**
     * @param null|string $nextSchool
     * @return Person
     */
    public function setNextSchool(?string $nextSchool): Person
    {
        $this->nextSchool = mb_substr($nextSchool, 0, 100);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $departureReason = '';

    /**
     * @return null|string
     */
    public function getDepartureReason(): ?string
    {
        return $this->departureReason;
    }

    /**
     * @param null|string $departureReason
     * @return Person
     */
    public function setDepartureReason(?string $departureReason): Person
    {
        $this->departureReason = mb_substr($departureReason, 0, 50);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $transport;

    /**
     * @return null|string
     */
    public function getTransport(): ?string
    {
        return $this->transport;
    }

    /**
     * @param null|string $transport
     * @return Person
     */
    public function setTransport(?string $transport): Person
    {
        $this->transport = mb_substr($transport, 0, 50);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $transportNotes = '';

    /**
     * @return null|string
     */
    public function getTransportNotes(): ?string
    {
        return $this->transportNotes;
    }

    /**
     * @param null|string $transportNotes
     * @return Person
     */
    public function setTransportNotes(?string $transportNotes): Person
    {
        $this->transportNotes = $transportNotes;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $calendarFeedPersonal;

    /**
     * @return null|string
     */
    public function getCalendarFeedPersonal(): ?string
    {
        return $this->calendarFeedPersonal;
    }

    /**
     * @param null|string $calendarFeedPersonal
     * @return Person
     */
    public function setCalendarFeedPersonal(?string $calendarFeedPersonal): Person
    {
        $this->calendarFeedPersonal = $calendarFeedPersonal;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $viewCalendarSchool = 'N';

    /**
     * getViewCalendarSchool
     * @return string|null
     */
    public function getViewCalendarSchool(): ?string
    {
        return $this->viewCalendarSchool;
    }

    /**
     * @param null|string $viewCalendarSchool
     * @return Person
     */
    public function setViewCalendarSchool(?string $viewCalendarSchool): Person
    {
        $this->viewCalendarSchool = in_array($viewCalendarSchool, self::getBooleanList()) ? $viewCalendarSchool : 'Y';
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $viewCalendarPersonal = 'Y';

    /**
     * getViewCalendarPersonal
     * @return string|null
     */
    public function getViewCalendarPersonal(): ?string
    {
        return $this->viewCalendarPersonal;
    }

    /**
     * @param null|string $viewCalendarPersonal
     * @return Person
     */
    public function setViewCalendarPersonal(?string $viewCalendarPersonal): Person
    {
        $this->viewCalendarPersonal = in_array($viewCalendarPersonal, self::getBooleanList()) ? $viewCalendarPersonal : 'Y';
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     */
    private $viewCalendarSpaceBooking = 'N';

    /**
     * @return null|string
     */
    public function getViewCalendarSpaceBooking(): ?string
    {
        return $this->viewCalendarSpaceBooking;
    }

    /**
    /**
     * @param null|string $viewCalendarSpaceBooking
     * @return Person
     */
    public function setViewCalendarSpaceBooking(?string $viewCalendarSpaceBooking): Person
    {
        $this->viewCalendarSpaceBooking = in_array($viewCalendarSpaceBooking, self::getBooleanList()) ? $viewCalendarSpaceBooking : 'N';
        return $this;
    }

    /**
     * @var ApplicationForm|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\ApplicationForm")
     * @ORM\JoinColumn(name="application_form", referencedColumnName="id", nullable=true)
     */
    private $applicationForm;

    /**
     * @return ApplicationForm|null
     */
    public function getApplicationForm(): ?ApplicationForm
    {
        return $this->applicationForm;
    }

    /**
     * @param ApplicationForm|null $applicationForm
     * @return Person
     */
    public function setApplicationForm(?ApplicationForm $applicationForm): Person
    {
        $this->applicationForm = $applicationForm;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=20)
     */
    private $lockerNumber = '';

    /**
     * @return null|string
     */
    public function getLockerNumber(): ?string
    {
        return $this->lockerNumber;
    }

    /**
     * @param null|string $lockerNumber
     * @return Person
     */
    public function setLockerNumber(?string $lockerNumber): Person
    {
        $this->lockerNumber = mb_substr($lockerNumber, 0, 20);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=20)
     */
    private $vehicleRegistration = '';

    /**
     * @return null|string
     */
    public function getVehicleRegistration(): ?string
    {
        return $this->vehicleRegistration;
    }

    /**
     * @param null|string $vehicleRegistration
     * @return Person
     */
    public function setVehicleRegistration(?string $vehicleRegistration): Person
    {
        $this->vehicleRegistration = mb_substr($vehicleRegistration, 0, 20);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191)
     * @ASSERTLOCAL\ReactImage(
     *     mimeTypes = {"image/jpg","image/jpeg","image/png","image/gif"},
     *     maxSize = "1536k",
     *     maxRatio = 1.777,
     *     minRatio = 1.25,
     * )
     * 16/9, 800/640
     */
    private $personalBackground = '';

    /**
     * @return null|string
     */
    public function getPersonalBackground(): ?string
    {
        return $this->personalBackground;
    }

    /**
     * @param null|string $personalBackground
     * @return Person
     */
    public function setPersonalBackground(?string $personalBackground): Person
    {
        $this->personalBackground = mb_substr($personalBackground, 0, 191);
        return $this;
    }

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $messengerLastBubble;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getMessengerLastBubble(): ?\DateTimeImmutable
    {
        return $this->messengerLastBubble;
    }

    /**
     * @param \DateTimeImmutable|null $messengerLastBubble
     * @return Person
     */
    public function setMessengerLastBubble(?\DateTimeImmutable $messengerLastBubble): Person
    {
        $this->messengerLastBubble = $messengerLastBubble;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $privacy;

    /**
     * @return null|string
     */
    public function getPrivacy(): ?string
    {
        return $this->privacy;
    }

    /**
     * @param null|string $privacy
     * @return Person
     */
    public function setPrivacy(?string $privacy): Person
    {
        $this->privacy = $privacy;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true,options={"comment": "Student day type, as specified in the application form."})
     */
    private $dayType;

    /**
     * @return null|string
     */
    public function getDayType(): ?string
    {
        return $this->dayType;
    }

    /**
     * @param null|string $dayType
     * @return Person
     */
    public function setDayType(?string $dayType): Person
    {
        $this->dayType = mb_substr($dayType, 0, 191);
        return $this;
    }

    /**
     * @var Theme|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Theme")
     * @ORM\JoinColumn(name="personal_theme", referencedColumnName="id", nullable=true)
     */
    private $theme;

    /**
     * @return Theme|null
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * @param Theme|null $theme
     * @return Person
     */
    public function setTheme(?Theme $theme): Person
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @var I18n|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\I18n")
     * @ORM\JoinColumn(name="personal_i18n", referencedColumnName="id", nullable=true)
     */
    private $i18nPersonal;

    /**
     * @return I18n|null
     */
    public function getI18nPersonal(): ?I18n
    {
        return $this->i18nPersonal;
    }

    /**
     * @param I18n|null $i18nPersonal
     * @return Person
     */
    public function setI18nPersonal(?I18n $i18nPersonal): Person
    {
        $this->i18nPersonal = $i18nPersonal;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $studentAgreements;

    /**
     * @return null|string
     */
    public function getStudentAgreements(): ?string
    {
        return $this->studentAgreements;
    }

    /**
     * @param null|string $studentAgreements
     * @return Person
     */
    public function setStudentAgreements(?string $studentAgreements): Person
    {
        $this->studentAgreements = $studentAgreements;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=191, name="google_api_refresh_token")
     */
    private $googleAPIRefreshToken = '';

    /**
     * @return null|string
     */
    public function getGoogleAPIRefreshToken(): ?string
    {
        return $this->googleAPIRefreshToken;
    }

    /**
     * @param null|string $googleAPIRefreshToken
     * @return Person
     */
    public function setGoogleAPIRefreshToken(?string $googleAPIRefreshToken): Person
    {
        $this->googleAPIRefreshToken = mb_substr($googleAPIRefreshToken, 0, 191);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @ASSERT\Choice(callback="getBooleanList")
     */
    private $receiveNotificationEmails = 'Y';

    /**
     * @return bool
     */
    public function isReceiveNotificationEmails(): bool
    {
        return $this->getReceiveNotificationEmails() === 'Y';
    }

    /**
     * @return null|string
     */
    public function getReceiveNotificationEmails(): ?string
    {
        return $this->receiveNotificationEmails = self::checkBoolean($this->receiveNotificationEmails);
    }

    /**
     * @param null|string $receiveNotificationEmails
     * @return Person
     */
    public function setReceiveNotificationEmails(?string $receiveNotificationEmails): Person
    {
        $this->receiveNotificationEmails = self::checkBoolean($receiveNotificationEmails);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="array", options={"comment": "Serialised array of custom field values"}, nullable=true)
     * Gibbon does not support NULL for this field.
     */
    private $fields = [];

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
     * @var Staff|null
     * @ORM\OneToOne(targetEntity="App\Modules\Staff\Entity\Staff", mappedBy="person")
     */
    private $staff;

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
     * @param bool $add
     * @return Person
     */
    public function setStaff(?Staff $staff, bool $add = true): Person
    {
        if ($staff instanceof Staff && $add)
            $staff->setPerson($this, false);
        $this->staff = $staff;
        return $this;
    }

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\CourseClassPerson", mappedBy="person")
     */
    private $courseClassPerson;

    /**
     * getCourseClassPerson
     * @return Collection|null
     */
    public function getCourseClassPerson(): ?Collection
    {
        if (empty($this->courseClassPerson))
            $this->courseClassPerson = new ArrayCollection();

        if ($this->courseClassPerson instanceof PersistentCollection)
            $this->courseClassPerson->initialize();

        return $this->courseClassPerson;
    }

    /**
     * @param Collection|null $courseClassPerson
     * @return Person
     */
    public function setCourseClassPerson(?Collection $courseClassPerson): Person
    {
        $this->courseClassPerson = $courseClassPerson;
        return $this;
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
     * @var StudentEnrolment[]|Collection||null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\StudentEnrolment", mappedBy="person")
     */
    private $studentEnrolments;

    /**
     * getStudentEnrolments
     * @return Collection|null
     */
    public function getStudentEnrolments(): ?Collection
    {
        if (null === $this->studentEnrolments)
            $this->studentEnrolments = new ArrayCollection();

        if ($this->studentEnrolments instanceof PersistentCollection)
            $this->studentEnrolments->initialize();

        return $this->studentEnrolments;
    }

    /**
     * StudentEnrolments.
     *
     * @param StudentEnrolment|null $studentEnrolments
     * @return Person
     */
    public function setStudentEnrolments(?StudentEnrolment $studentEnrolments): Person
    {
        $this->studentEnrolments = $studentEnrolments;
        return $this;
    }

    /**
     * @var Collection|FamilyMember[]|null
     * @ORM\OneToMany(targetEntity="FamilyMember", mappedBy="person")
     */
    private $members;

    /**
     * getMembers
     * @return Collection
     */
    public function getMembers(): Collection
    {
        if (null === $this->members)
            $this->members = new ArrayCollection();

        if ($this->members instanceof PersistentCollection)
            $this->members->initialize();

        return $this->members;
    }

    /**
     * Members.
     *
     * @param FamilyMember|null $members
     * @return Person
     */
    public function setMembers(?FamilyMember $members): Person
    {
        $this->members = $members;
        return $this;
    }

    /**
     * @return Collection|FamilyMember[]|null
     */
    public function getAdults(): Collection
    {
        return $this->getMembers()->filter(function(FamilyMember $member) {
            if ($member instanceof FamilyMemberAdult)
                return $member;
        });
    }

    /**
     * @return Collection|FamilyMember[]|null
     */
    public function getChildren(): Collection
    {
        return $this->getMembers()->filter(function(FamilyMember $member) {
            if ($member instanceof FamilyMemberChild)
                return $member;
        });
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
        return $this->formatName(['style' => 'long', 'preferred' => false]);
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
     */
    public function toArray(?string $name = NULL): array
    {
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
            'student' => $this->isStudent(),
            'staff' => $this->isStaff(),
            'parent' => $this->isParent(),
        ];
    }

    /**
     * @var
     */
    private $family;

    /**
     * getFamily
     * @return Family|null
     */
    public function getFamily(): ?Family
    {
        if ($this->family instanceof Family)
            return $this->family;
        if ($this->getAdults()->count() > 0) {
            $adult = $this->getAdults()->first();
            if ($adult->getFamily() instanceof Family)
                return $this->family = $adult->getFamily();
        }
        if ($this->getChildren()->count() > 0) {
            $child = $this->getChildren()->first();
            if ($child->getFamily() instanceof Family)
                return $this->family = $child->getFamily();
        }
        $this->family = null;
        return $this->family;
    }

    /**
     * getFamilyName
     * @return string
     */
    public function getFamilyName(): string
    {
        return $this->getFamily() ? $this->getFamily()->getName() : '';
    }

    /**
     * getFamilyName
     * @return string
     */
    public function getFamilyId(): string
    {
        return $this->getFamily() ? $this->getFamily()->getName() : '';
    }

    /**
     * hasRole
     * @param string $role
     * @return bool
     * 10/06/2020 12:19
     */
    public function hasRole(string $role): bool
    {
        $roles = SecurityHelper::getHierarchy()->getReachableRoleNames($this->getSecurityRoles() ?: []);
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
        if ($this->getStaff() instanceof Staff)
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
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `title` CHAR(5) NOT NULL,
                    `surname` CHAR(60) NOT NULL,
                    `first_name` CHAR(60) NOT NULL,
                    `preferred_name` CHAR(60) NOT NULL,
                    `official_name` CHAR(150) NOT NULL,
                    `name_in_characters` CHAR(60) NOT NULL,
                    `gender` CHAR(16) NOT NULL DEFAULT 'Unspecified',
                    `username` CHAR(20) DEFAULT NULL,
                    `password` CHAR(191) DEFAULT NULL,
                    `password_force_reset` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Force user to reset password on next login.',
                    `status` CHAR(16) NOT NULL DEFAULT 'Full',
                    `can_login` CHAR(1) NOT NULL DEFAULT 'Y',
                    'security_roles` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                    `dob` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `email` CHAR(75) DEFAULT NULL,
                    `email_alternate` CHAR(75) DEFAULT NULL,
                    `image_240` CHAR(191) DEFAULT NULL,
                    `last_ip_address` CHAR(15) NOT NULL,
                    `last_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `last_fail_ip_address` CHAR(15) DEFAULT NULL,
                    `last_fail_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `fail_count` smallint DEFAULT NULL,
                    `physical_address` CHAR(36) DEFAULT NULL,
                    `postal_address` CHAR(36) DEFAULT NULL,
                    `personal_phone` CHAR(36) DEFAULT NULL,
                    `website` CHAR(191) NOT NULL,
                    `language_first` CHAR(5) NOT NULL,
                    `language_second` CHAR(5) NOT NULL,
                    `language_third` CHAR(5) NOT NULL,
                    `country_of_birth` CHAR(3) NOT NULL,
                    `birth_certificate_scan` CHAR(191) NOT NULL,
                    `ethnicity` CHAR(191) NOT NULL,
                    `citizenship1` CHAR(3) NOT NULL,
                    `citizenship1_passport` CHAR(30) NOT NULL,
                    `citizenship1_passport_scan` CHAR(191) NOT NULL,
                    `citizenship2` CHAR(3) NOT NULL,
                    `citizenship2_passport` CHAR(30) NOT NULL,
                    `religion` CHAR(30) NOT NULL,
                    `national_card_number` CHAR(30) NOT NULL,
                    `national_card_scan` CHAR(191) NOT NULL,
                    `residency_status` CHAR(191) NOT NULL,
                    `visa_expiry_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `profession` CHAR(90) NOT NULL,
                    `employer` CHAR(90) NOT NULL,
                    `jobTitle` CHAR(90) NOT NULL,
                    `emergency_contact1` CHAR(36) DEFAULT NULL,
                    `emergency_contact2` CHAR(36) DEFAULT NULL,
                    `student_identifier` CHAR(20) DEFAULT NULL,
                    `date_start` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `date_end` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `last_school` CHAR(100) NOT NULL,
                    `next_school` CHAR(100) NOT NULL,
                    `departure_reason` CHAR(50) NOT NULL,
                    `transport` CHAR(191) DEFAULT NULL,
                    `transport_notes` longtext DEFAULT NULL,
                    `calendar_feed_personal` CHAR(191) DEFAULT NULL,
                    `view_calendar_school` CHAR(1) NOT NULL DEFAULT 'Y',
                    `view_calendar_personal` CHAR(1) NOT NULL DEFAULT 'Y',
                    `view_calendar_space_booking` CHAR(1) NOT NULL DEFAULT 'N',
                    `locker_number` CHAR(20) NOT NULL,
                    `vehicle_registration` CHAR(20) NOT NULL,
                    `personal_background` CHAR(191) NOT NULL,
                    `messenger_last_bubble` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `privacy` longtext,
                    `day_type` CHAR(191) DEFAULT NULL COMMENT 'Student day type, as specified in the application form.',
                    `student_agreements` longtext,
                    `google_api_refresh_token` CHAR(191) NOT NULL,
                    `receive_notification_emails` CHAR(1) NOT NULL DEFAULT 'Y',
                    `fields` longtext COMMENT 'Serialised array of custom field values(DC2Type:array)',
                    `house` CHAR(36) DEFAULT NULL,
                    `class_of_academic_year` CHAR(36) DEFAULT NULL,
                    `application_form` CHAR(36) DEFAULT NULL,
                    `personal_theme` CHAR(36) DEFAULT NULL,
                    `personal_i18n` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `username` (`username`),
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
                "CREATE TABLE IF NOT EXISTS `__prefix__PersonalPhone` (
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
     * isStaff
     * @return bool
     * 10/06/2020 11:58
     */
    public function isStaff(): bool
    {
        return $this->hasRole('ROLE_STAFF');
    }

    /**
     * isStudent
     * @return bool
     * 10/06/2020 11:59
     */
    public function isStudent(): bool
    {
        return $this->hasRole('ROLE_STUDENT');
    }

    /**
     * isParent
     * @return bool
     * 10/06/2020 11:59
     */
    public function isParent(): bool
    {
        return $this->hasRole('ROLE_PARENT');
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
}

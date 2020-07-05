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
 * Date: 2/07/2020
 * Time: 11:38
 */
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Validator as AssertLocal;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonalDocumentation
 * @package App\Modules\People\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\PersonalDocumentationRepository")
 * @ORM\Table(name="PersonalDocumentation",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="person",columns={"person"})
 *  } 
 * )
 */
class PersonalDocumentation extends AbstractEntity
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
     * @var Person
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="personalDocumentation")
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private $languageFirst;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private $languageSecond;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private $languageThird;

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     * @Assert\Country(alpha3=true)
     */
    private $countryOfBirth;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     * @AssertLocal\ReactFile(
     *     maxSize = "2048k",
     *     mimeTypes = {"image/*","application/pdf","application/x-pdf"}
     * )
     */
    private $birthCertificateScan;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $ethnicity;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $dob;

    /**
     * @var string|null
     * @ORM\Column(length=191, nullable=true)
     * @AssertLocal\ReactImage(
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
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     */
    private $citizenship1;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="citizenship1_passport",nullable=true)
     */
    private $citizenship1Passport;

    /**
     * @var string|null
     * @ORM\Column(length=191,name="citizenship1_passport_scan",nullable=true)
     * @AssertLocal\ReactFile(
     *     maxSize = "2048k",
     *     mimeTypes = {"image/*","application/pdf","application/x-pdf"}
     * )
     */
    private $citizenship1PassportScan;

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     */
    private $citizenship2;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="citizenship2_passport",nullable=true)
     */
    private $citizenship2Passport;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private $religion;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="national_card_number",nullable=true)
     */
    private $nationalIDCardNumber;

    /**
     * @var string|null
     * @ORM\Column(length=191,name="national_card_scan",nullable=true)
     */
    private $nationalIDCardScan;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $residencyStatus;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(nullable=true, type="date_immutable")
     */
    private $visaExpiryDate;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return PersonalDocumentation
     */
    public function setId(?string $id): PersonalDocumentation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AbstractEntity
     */
    public function getPerson(): AbstractEntity
    {
        return $this->person;
    }

    /**
     * @param Person $person
     * @return PersonalDocumentation
     */
    public function setPerson(Person $person): PersonalDocumentation
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageFirst(): ?string
    {
        return $this->languageFirst;
    }

    /**
     * @param null|string $languageFirst
     * @return AbstractEntity
     */
    public function setLanguageFirst(?string $languageFirst): AbstractEntity
    {
        $this->languageFirst = mb_substr($languageFirst, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageSecond(): ?string
    {
        return $this->languageSecond;
    }

    /**
     * @param null|string $languageSecond
     * @return AbstractEntity
     */
    public function setLanguageSecond(?string $languageSecond): AbstractEntity
    {
        $this->languageSecond = mb_substr($languageSecond, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageThird(): ?string
    {
        return $this->languageThird;
    }

    /**
     * @param null|string $languageThird
     * @return AbstractEntity
     */
    public function setLanguageThird(?string $languageThird): AbstractEntity
    {
        $this->languageThird = mb_substr($languageThird, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCountryOfBirth(): ?string
    {
        return $this->countryOfBirth;
    }

    /**
     * @param null|string $countryOfBirth
     * @return AbstractEntity
     */
    public function setCountryOfBirth(?string $countryOfBirth): AbstractEntity
    {
        $this->countryOfBirth = mb_substr($countryOfBirth, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getBirthCertificateScan(): ?string
    {
        return $this->birthCertificateScan;
    }

    /**
     * @param null|string $birthCertificateScan
     * @return AbstractEntity
     */
    public function setBirthCertificateScan(?string $birthCertificateScan): AbstractEntity
    {
        $this->birthCertificateScan = mb_substr($birthCertificateScan, 0, 191);
        return $this;
    }

    /**
     * getEthnicityList
     * @return array
     */
    public static function getEthnicityList(): array
    {
        if (($x = SettingFactory::getSettingManager()->getSettingByScopeAsArray('User Admin', 'ethnicity')) !== []) {
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
     * @return AbstractEntity
     */
    public function setEthnicity(?string $ethnicity): AbstractEntity
    {
        $this->ethnicity = mb_substr($ethnicity, 0, 191);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCitizenship1(): ?string
    {
        return $this->citizenship1;
    }

    /**
     * @param null|string $citizenship1
     * @return AbstractEntity
     */
    public function setCitizenship1(?string $citizenship1): AbstractEntity
    {
        $this->citizenship1 = mb_substr($citizenship1, 0, 191);
        return $this;
    }

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
     * @return AbstractEntity
     */
    public function setCitizenship1Passport(?string $citizenship1Passport): AbstractEntity
    {
        $this->citizenship1Passport = mb_substr($citizenship1Passport, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCitizenship1PassportScan(): ?string
    {
        return $this->citizenship1PassportScan;
    }

    /**
     * @param null|string $citizenship1PassportScan
     * @return AbstractEntity
     */
    public function setCitizenship1PassportScan(?string $citizenship1PassportScan): AbstractEntity
    {
        $this->citizenship1PassportScan = mb_substr($citizenship1PassportScan, 0, 191);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCitizenship2(): ?string
    {
        return $this->citizenship2;
    }

    /**
     * @param null|string $citizenship2
     * @return AbstractEntity
     */
    public function setCitizenship2(?string $citizenship2): AbstractEntity
    {
        $this->citizenship2 = mb_substr($citizenship2, 0, 191);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCitizenship2Passport(): ?string
    {
        return $this->citizenship2Passport;
    }

    /**
     * @param null|string $citizenship2Passport
     * @return AbstractEntity
     */
    public function setCitizenship2Passport(?string $citizenship2Passport): AbstractEntity
    {
        $this->citizenship2Passport = mb_substr($citizenship2Passport, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getReligion(): ?string
    {
        return $this->religion;
    }

    /**
     * @param null|string $religion
     * @return AbstractEntity
     */
    public function setReligion(?string $religion): AbstractEntity
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
        return SettingFactory::getSettingManager()->getSettingByScopeAsArray('User Admin', 'religions');
    }

    /**
     * @return null|string
     */
    public function getNationalIDCardNumber(): ?string
    {
        return $this->nationalIDCardNumber;
    }

    /**
     * @param null|string $nationalIDCardNumber
     * @return AbstractEntity
     */
    public function setNationalIDCardNumber(?string $nationalIDCardNumber): AbstractEntity
    {
        $this->nationalIDCardNumber = mb_substr($nationalIDCardNumber, 0, 30);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNationalIDCardScan(): ?string
    {
        return $this->nationalIDCardScan;
    }

    /**
     * @param null|string $nationalIDCardScan
     * @return AbstractEntity
     */
    public function setNationalIDCardScan(?string $nationalIDCardScan): AbstractEntity
    {
        $this->nationalIDCardScan = mb_substr($nationalIDCardScan, 0, 191);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getResidencyStatus(): ?string
    {
        return $this->residencyStatus;
    }

    /**
     * @param null|string $residencyStatus
     * @return AbstractEntity
     */
    public function setResidencyStatus(?string $residencyStatus): AbstractEntity
    {
        $this->residencyStatus = mb_substr($residencyStatus, 0, 191);
        return $this;
    }

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
     * @return AbstractEntity
     */
    public function setVisaExpiryDate(?\DateTimeImmutable $visaExpiryDate): AbstractEntity
    {
        $this->visaExpiryDate = $visaExpiryDate;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDob(): ?\DateTimeImmutable
    {
        return $this->dob;
    }

    /**
     * @param \DateTimeImmutable|null $dob
     * @return PersonalDocumentation
     */
    public function setDob(?\DateTimeImmutable $dob): PersonalDocumentation
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage240(): ?string
    {
        return $this->image_240;
    }

    /**
     * @param string|null $image_240
     * @return PersonalDocumentation
     */
    public function setImage240(?string $image_240): PersonalDocumentation
    {
        $this->image_240 = $image_240;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * create
     * @return array|string[]
     * 4/07/2020 09:48
     */
    public function create(): array
    {
        return [
            "CREATE TABLE `__prefix__PersonalDocumentation` (
                `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                `person` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
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
                `dob` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                `image_240` varchar(191) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `person` (`person`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
        ];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/07/2020 09:48
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__PersonalDocumentation`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 4/07/2020 09:49
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
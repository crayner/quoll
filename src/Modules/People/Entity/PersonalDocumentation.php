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
use App\Validator as AssertLocal;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Intl\Languages;
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
    private ?string $id;

    /**
     * @var Person|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",cascade={"persist"})
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private ?Person $person;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private ?string $languageFirst;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private ?string $languageSecond;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private ?string $languageThird;

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     * @Assert\Country(alpha3=true)
     */
    private ?string $countryOfBirth;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     * @AssertLocal\ReactFile(
     *     maxSize = "2048k",
     *     mimeTypes = {"image/*","application/pdf","application/x-pdf"}
     * )
     */
    private ?string $birthCertificateScan;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private ?string $ethnicity;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private ?DateTimeImmutable $dob;

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
    private ?string $personalImage;

    /**
     * @var array
     */
    private static array $ethnicityList = [
        'OCEANIAN' => [
	        'Australian Peoples',
            'New Zealand Peoples',
            'Melanesian and Papuan',
            'Micronesian',
            'Polynesian'
        ],
        'NORTH-WEST EUROPEAN' => [
            'British',
            'Irish',
            'Western European',
            'Northern European',
        ],
        'SOUTHERN AND EASTERN EUROPEAN' => [
            'Southern European',
            'South Eastern European',
            'Eastern European',
        ],
        'NORTH AFRICAN AND MIDDLE EASTERN' => [
            'Arab',
            'Jewish',
            'Peoples of the Sudan',
            'Other North African and Middle Eastern',
        ],
        'SOUTH-EAST ASIAN' => [
            'Mainland South-East Asian',
            'Maritime South-East Asian'
        ],
        'NORTH-EAST ASIAN' => [
            'Chinese Asian',
            'Other North-East Asian',
        ],
        'SOUTHERN AND CENTRAL ASIAN' => [
            'Southern Asian',
            'Central Asian',
        ],
        'PEOPLES OF THE AMERICAS' => [
            'North American',
            'South American',
            'Central American',
            'Caribbean Islander',
        ],
        'SUB-SAHARAN AFRICAN' => [
            'Central and West African',
            'Southern and East African',
        ]
    ];

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     */
    private ?string $citizenship1;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="citizenship1_passport",nullable=true)
     */
    private ?string $citizenship1Passport;

    /**
     * @var string|null
     * @ORM\Column(length=191,name="citizenship1_passport_scan",nullable=true)
     * @AssertLocal\ReactFile(
     *     maxSize = "2048k",
     *     mimeTypes = {"image/*","application/pdf","application/x-pdf"}
     * )
     */
    private ?string $citizenship1PassportScan;

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     */
    private ?string $citizenship2;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="citizenship2_passport",nullable=true)
     */
    private ?string $citizenship2Passport;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private ?string $religion;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="national_card_number",nullable=true)
     */
    private ?string $nationalIDCardNumber;

    /**
     * @var string|null
     * @ORM\Column(length=191,name="national_card_scan",nullable=true)
     */
    private ?string $nationalIDCardScan;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private ?string $residencyStatus;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(nullable=true, type="date_immutable")
     */
    private ?DateTimeImmutable $visaExpiryDate;

    /**
     * PersonalDocumentation constructor.
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
        return isset($this->id) ? $this->id : null;
    }

    /**
     * @param string $id
     * @return PersonalDocumentation
     */
    public function setId(string $id): PersonalDocumentation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * getPerson
     *
     * 29/08/2020 12:18
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return isset($this->person) ? $this->person : null;
    }

    /**
     * @param Person|null $person
     * @return PersonalDocumentation
     */
    public function setPerson(?Person $person): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setLanguageFirst(?string $languageFirst): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setLanguageSecond(?string $languageSecond): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setLanguageThird(?string $languageThird): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setCountryOfBirth(?string $countryOfBirth): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setBirthCertificateScan(?string $birthCertificateScan): PersonalDocumentation
    {
        if ($birthCertificateScan === null) return $this;
        $this->birthCertificateScan = $birthCertificateScan;
        return $this;
    }

    /**
     * removeBirthCertificateScan
     * @return $this
     * 20/07/2020 12:25
     */
    public function removeBirthCertificateScan(): PersonalDocumentation
    {
        $this->birthCertificateScan = null;
        
        return $this;
    }

    /**
     * getEthnicityList
     * @return array
     */
    public static function getEthnicityList(): array
    {
        if (count($x = SettingFactory::getSettingManager()->get('People', 'ethnicity')) > 0) {
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
     * @return PersonalDocumentation
     */
    public function setEthnicity(?string $ethnicity): PersonalDocumentation
    {
        $this->ethnicity = mb_substr($ethnicity, 0, 191);
        return $this;
    }

    /**
     * getCitizenship1
     *
     * 29/08/2020 10:15
     * @return string|null
     */
    public function getCitizenship1(): ?string
    {
        return $this->citizenship1;
    }

    /**
     * @param null|string $citizenship1
     * @return PersonalDocumentation
     */
    public function setCitizenship1(?string $citizenship1): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setCitizenship1Passport(?string $citizenship1Passport): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setCitizenship1PassportScan(?string $citizenship1PassportScan): PersonalDocumentation
    {
        if (null === $citizenship1PassportScan) return $this;

        $this->citizenship1PassportScan = $citizenship1PassportScan;
        return $this;
    }

    /**
     * removeCitizenship1PassportScan
     * @return $this
     * 20/07/2020 13:42
     */
    public function removeCitizenship1PassportScan(): PersonalDocumentation
    {
        $this->citizenship1PassportScan = null;
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
     * @return PersonalDocumentation
     */
    public function setCitizenship2(?string $citizenship2): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setCitizenship2Passport(?string $citizenship2Passport): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setReligion(?string $religion): PersonalDocumentation
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
        return SettingFactory::getSettingManager()->get('People', 'religions', []);
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
     * @return PersonalDocumentation
     */
    public function setNationalIDCardNumber(?string $nationalIDCardNumber): PersonalDocumentation
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
     * @return PersonalDocumentation
     */
    public function setNationalIDCardScan(?string $nationalIDCardScan): PersonalDocumentation
    {
        if (null === $nationalIDCardScan) return $this;

        $this->nationalIDCardScan = $nationalIDCardScan;
        return $this;
    }

    /**
     * removeNationalIDCardScan
     *
     * 19/08/2020 16:32
     * @return $this
     */
    public function removeNationalIDCardScan(): PersonalDocumentation
    {
        $this->nationalIDCardScan = null;
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
     * @return PersonalDocumentation
     */
    public function setResidencyStatus(?string $residencyStatus): PersonalDocumentation
    {
        $this->residencyStatus = mb_substr($residencyStatus, 0, 191);
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getVisaExpiryDate(): ?DateTimeImmutable
    {
        return $this->visaExpiryDate;
    }

    /**
     * VisaExpiryDate.
     *
     * @param DateTimeImmutable|null $visaExpiryDate
     * @return PersonalDocumentation
     */
    public function setVisaExpiryDate(?DateTimeImmutable $visaExpiryDate): PersonalDocumentation
    {
        $this->visaExpiryDate = $visaExpiryDate;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDob(): ?DateTimeImmutable
    {
        return $this->dob;
    }

    /**
     * @param DateTimeImmutable|null $dob
     * @return PersonalDocumentation
     */
    public function setDob(?DateTimeImmutable $dob): PersonalDocumentation
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPersonalImage(): ?string
    {
        return $this->personalImage;
    }

    /**
     * @param string|null $personalImage
     * @return PersonalDocumentation
     */
    public function setPersonalImage(?string $personalImage): PersonalDocumentation
    {
        if ($personalImage === null) return $this;
        $this->personalImage = $personalImage;
        return $this;
    }

    /**
     * removePersonalImage
     * @return $this
     * 20/07/2020 13:39
     */
    public function removePersonalImage(): PersonalDocumentation
    {
        $this->personalImage = null;
        return $this;
    }

    /**
     * removePersonalImage
     * @return $this
     * 20/07/2020 13:39
     */
    public function removeIDCardImage(): PersonalDocumentation
    {
        $this->nationalIDCardScan = null;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
        return [];
    }

    /**
     * getLanguageList
     * @return array
     * 20/07/2020 11:42
     */
    public static function getLanguageList(): array
    {
        $languages = Languages::getNames();
        return array_flip($languages);
    }


    /**
     * Display an icon if this user's birthday is within the next week.
     *
     * @return string
     */
    public function birthdayIcon()
    {
        if (!$this->getDob() instanceof DateTimeImmutable)
            return '';

        try {
            $dob = new DateTimeImmutable(date('Y') . $this->getDob()->format('-m-d'));
        } catch (Exception $e) {
            return '';
        }
        $today = new DateTimeImmutable('now');
        if ($today->format('Ymd') > $dob->format('Ymd'))
            return '';

        $daysUntilNextBirthday = $today->diff($dob)->days;
        if ($daysUntilNextBirthday >= 8)
            return '';

        // HEY SHORTY IT'S YOUR BIRTHDAY! (or Close)
        $result['colour'] = 'text-pink-800';
        $result['params']['{name}'] = $this->getPerson()->getPreferredName();
        $result['params']['count'] = $daysUntilNextBirthday;
        if ($daysUntilNextBirthday > 0)
            $result['colour'] = 'text-gray-800';

        return $result;
    }

}

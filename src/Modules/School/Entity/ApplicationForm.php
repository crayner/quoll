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
 * Time: 11:39
 */
namespace App\Modules\School\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\Finance\Entity\Payment;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ApplicationForm
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\ApplicationFormRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="ApplicationForm",options={"auto_increment": 1})
 * @ORM\HasLifecycleCallbacks
 */
class ApplicationForm implements EntityInterface
{
    use BooleanList;
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(12) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=40, nullable=true, name="applicationFormHash")
     */
    private $applicationFormHash;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     */
    private $surname;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="firstName")
     */
    private $firstName;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="preferredName")
     */
    private $preferredName;

    /**
     * @var string|null
     * @ORM\Column(length=150, name="officialName")
     */
    private $officialName;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="nameInCharacters")
     */
    private $nameInCharacters;

    /**
     * @var string|null
     * @ORM\Column(length=12, options={"default": "Unspecified"}, nullable=true)
     */
    private $gender = 'Unspecified';

    /**
     * getGenderList
     * @return array
     */
    private static function getGenderList(){
        return Person::getGenderList();
    }

    /**
     * @var string|null
     * @ORM\Column(length=20, nullable=true)
     */
    private $username;

    /**
     * @var string|null
     * @ORM\Column(length=12, options={"default": "Pending"})
     */
    private $status = 'Pending';

    /**
     * @var array
     */
    private static $statusList = ['Pending','Waiting List','Accepted','Rejected','Withdrawn'];

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dob;

    /**
     * @var string|null
     * @ORM\Column(length=75, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="homeAddress", nullable=true)
     */
    private $homeAddress;

    /**
     * @var string|null
     * @ORM\Column(name="homeAddressDistrict", nullable=true)
     */
    private $homeAddressDistrict;

    /**
     * @var string|null
     * @ORM\Column(name="homeAddressCountry", nullable=true)
     */
    private $homeAddressCountry;

    /**
     * @var string
     * @ORM\Column(length=6, name="phone1Type")
     */
    private $phone1Type = '';

    /**
     * @var string
     * @ORM\Column(length=7, name="phone1CountryCode")
     */
    private $phone1CountryCode;

    /**
     * @var string
     * @ORM\Column(length=20)
     */
    private $phone1;

    /**
     * @var string
     * @ORM\Column(length=6, name="phone2Type")
     */
    private $phone2Type = '';

    /**
     * @var string
     * @ORM\Column(length=7, name="phone2CountryCode")
     */
    private $phone2CountryCode;

    /**
     * @var string
     * @ORM\Column(length=20)
     */
    private $phone2;

    /**
     * @var string|null
     * @ORM\Column(length=30,name="countryOfBirth")
     */
    private $countryOfBirth;

    /**
     * @var string|null
     * @ORM\Column(name="citizenship1")
     */
    private $citizenship1;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="citizenship1Passport")
     */
    private $citizenship1Passport;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="nationalIDCardNumber")
     */
    private $nationalIDCardNumber;

    /**
     * @var string|null
     * @ORM\Column(length=255, name="residencyStatus")
     */
    private $residencyStatus;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", name="visaExpiryDate",nullable=true)
     */
    private $visaExpiryDate;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year_entry",referencedColumnName="id", nullable=false)
     */
    private $academicYearEntry;

    /**
     * @var YearGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinColumn(name="year_group_entry",referencedColumnName="id", nullable=false)
     */
    private $yearGroupEntry;

    /**
     * @var string|null
     * @ORM\Column(name="dayType", nullable=true)
     */
    private $dayType;

    /**
     * @var string|null
     * @ORM\Column(name="referenceEmail", nullable=true, length=100)
     */
    private $referenceEmail;

    /**
     * @var string|null
     * @ORM\Column(name="schoolName1", length=50)
     */
    private $schoolName1;

    /**
     * @var string|null
     * @ORM\Column(name="schoolAddress1")
     */
    private $schoolAddress1;

    /**
     * @var string|null
     * @ORM\Column(name="schoolGrades1", length=20)
     */
    private $schoolGrades1;

    /**
     * @var string|null
     * @ORM\Column(name="schoolLanguage1", length=50)
     */
    private $schoolLanguage1;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="schoolDate1", type="date", nullable=true)
     */
    private $schoolDate1;

    /**
     * @var string|null
     * @ORM\Column(name="schoolName2", length=50)
     */
    private $schoolName2;

    /**
     * @var string|null
     * @ORM\Column(name="schoolAddress2")
     */
    private $schoolAddress2;

    /**
     * @var string|null
     * @ORM\Column(name="schoolGrades2", length=20)
     */
    private $schoolGrades2;

    /**
     * @var string|null
     * @ORM\Column(name="schoolLanguage2", length=50)
     */
    private $schoolLanguage2;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="schoolDate2", type="date", nullable=true)
     */
    private $schoolDate2;

    /**
     * @var string|null
     * @ORM\Column(name="siblingName1", length=50)
     */
    private $siblingName1;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="siblingDOB1", type="date", nullable=true)
     */
    private $siblingDOB1;

    /**
     * @var string|null
     * @ORM\Column(name="siblingSchool1", length=50)
     */
    private $siblingSchool1;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="siblingSchoolJoiningDate1", type="date", nullable=true)
     */
    private $siblingSchoolJoiningDate1;

    /**
     * @var string|null
     * @ORM\Column(name="siblingName2", length=50)
     */
    private $siblingName2;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="siblingDOB2", type="date", nullable=true)
     */
    private $siblingDOB2;

    /**
     * @var string|null
     * @ORM\Column(name="siblingSchool2", length=50)
     */
    private $siblingSchool2;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="siblingSchoolJoiningDate2", type="date", nullable=true)
     */
    private $siblingSchoolJoiningDate2;

    /**
     * @var string|null
     * @ORM\Column(name="siblingName3", length=50)
     */
    private $siblingName3;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="siblingDOB3", type="date", nullable=true)
     */
    private $siblingDOB3;

    /**
     * @var string|null
     * @ORM\Column(name="siblingSchool3", length=50)
     */
    private $siblingSchool3;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="siblingSchoolJoiningDate3", type="date", nullable=true)
     */
    private $siblingSchoolJoiningDate3;

    /**
     * @var string|null
     * @ORM\Column(name="languageHomePrimary", length=30)
     */
    private $languageHomePrimary;

    /**
     * @var string|null
     * @ORM\Column(name="languageHomeSecondary", length=30)
     */
    private $languageHomeSecondary;

    /**
     * @var string|null
     * @ORM\Column(name="languageFirst", length=30)
     */
    private $languageFirst;

    /**
     * @var string|null
     * @ORM\Column(name="languageSecond", length=30)
     */
    private $languageSecond;

    /**
     * @var string|null
     * @ORM\Column(name="languageThird", length=30)
     */
    private $languageThird;

    /**
     * @var string|null
     * @ORM\Column(name="medicalInformation", type="text")
     */
    private $medicalInformation;

    /**
     * @var string|null
     * @ORM\Column(length=1, nullable=true)
     */
    private $sen;

    /**
     * @var string|null
     * @ORM\Column(name="senDetails", type="text")
     */
    private $senDetails;

    /**
     * @var string|null
     * @ORM\Column(name="languageChoice", length=100, nullable=true)
     */
    private $languageChoice;

    /**
     * @var string|null
     * @ORM\Column(name="languageChoiceExperience", type="text", nullable=true)
     */
    private $languageChoiceExperience;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="scholarshipInterest", options={"default": "N"})
     */
    private $scholarshipInterest = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="scholarshipRequired", options={"default": "N"})
     */
    private $scholarshipRequired = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=7, options={"default": "Family"})
     */
    private $payment = 'Family';

    /**
     * @var array
     */
    private static $paymentList = ['Family', 'Company'];

    /**
     * @var string|null
     * @ORM\Column(name="companyName", length=100, nullable=true)
     */
    private $companyName;

    /**
     * @var string|null
     * @ORM\Column(name="companyContact", length=100, nullable=true)
     */
    private $companyContact;

    /**
     * @var string|null
     * @ORM\Column(name="companyAddress", length=255, nullable=true)
     */
    private $companyAddress;

    /**
     * @var string|null
     * @ORM\Column(name="companyEmail", type="text", nullable=true)
     */
    private $companyEmail;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="companyCCFamily", nullable=true, options={"comment": "When company is billed, should family receive a copy?"})
     */
    private $companyCCFamily;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="companyPhone", nullable=true)
     */
    private $companyPhone;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="companyAll", nullable=true)
     */
    private $companyAll;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="__prefix__FinanceFeeCategoryIDList", nullable=true)
     */
    private $financeFeeCategoryList;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="agreement", nullable=true)
     */
    private $agreement;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="parent1",referencedColumnName="id", nullable=true)
     */
    private $parent1;

    /**
     * @var string|null
     * @ORM\Column(length=5, name="parent1title", nullable=true)
     */
    private $parent1title;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="parent1surname", nullable=true)
     */
    private $parent1surname;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="parent1firstName", nullable=true)
     */
    private $parent1firstName;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="parent1preferredName", nullable=true)
     */
    private $parent1preferredName;

    /**
     * @var string|null
     * @ORM\Column(length=150, name="parent1officialName", nullable=true)
     */
    private $parent1officialName;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="parent1nameInCharacters", nullable=true)
     */
    private $parent1nameInCharacters;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="parent1gender", options={"default": "Unspecified"}, nullable=true))
     */
    private $parent1gender = 'Unspecified';

    /**
     * @var string|null
     * @ORM\Column(length=50, name="parent1relationship", nullable=true)
     */
    private $parent1relationship;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent1languageFirst", nullable=true)
     */
    private $parent1languageFirst;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent1languageSecond", nullable=true)
     */
    private $parent1languageSecond;

    /**
     * @var string|null
     * @ORM\Column(length=255, name="parent1citizenship1", nullable=true)
     */
    private $parent1citizenship1;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent1nationalIDCardNumber", nullable=true)
     */
    private $parent1nationalIDCardNumber;

    /**
     * @var string|null
     * @ORM\Column(length=255, name="parent1residencyStatus", nullable=true)
     */
    private $parent1residencyStatus;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", name="parent1visaExpiryDate", nullable=true)
     */
    private $parent1visaExpiryDate;

    /**
     * @var string|null
     * @ORM\Column(length=75, name="parent1email", nullable=true)
     */
    private $parent1email;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="parent1phone1Type", nullable=true)
     */
    private $parent1phone1Type;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="parent1phone1CountryCode", nullable=true)
     */
    private $parent1phone1CountryCode;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="parent1phone1", nullable=true)
     */
    private $parent1phone1;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="parent1phone2Type", nullable=true)
     */
    private $parent1phone2Type;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="parent1phone2CountryCode", nullable=true)
     */
    private $parent1phone2CountryCode;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="parent1phone2", nullable=true)
     */
    private $parent1phone2;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent1profession", nullable=true)
     */
    private $parent1profession;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent1employer", nullable=true)
     */
    private $parent1employer;

    /**
     * @var string|null
     * @ORM\Column(length=5, name="parent2title", nullable=true)
     */
    private $parent2title;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="parent2surname", nullable=true)
     */
    private $parent2surname;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="parent2firstName", nullable=true)
     */
    private $parent2firstName;

    /**
     * @var string|null
     * @ORM\Column(length=60, name="parent2preferredName", nullable=true)
     */
    private $parent2preferredName;

    /**
     * @var string|null
     * @ORM\Column(length=150, name="parent2officialName", nullable=true)
     */
    private $parent2officialName;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="parent2nameInCharacters", nullable=true)
     */
    private $parent2nameInCharacters;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="parent2gender", options={"default": "Unspecified"}, nullable=true)
     */
    private $parent2gender = 'Unspecified';

    /**
     * @var string|null
     * @ORM\Column(length=50, name="parent2relationship", nullable=true)
     */
    private $parent2relationship;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent2languageFirst", nullable=true)
     */
    private $parent2languageFirst;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent2languageSecond", nullable=true)
     */
    private $parent2languageSecond;

    /**
     * @var string|null
     * @ORM\Column(length=255, name="parent2citizenship1", nullable=true)
     */
    private $parent2citizenship1;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent2nationalIDCardNumber", nullable=true)
     */
    private $parent2nationalIDCardNumber;

    /**
     * @var string|null
     * @ORM\Column(length=255, name="parent2residencyStatus", nullable=true)
     */
    private $parent2residencyStatus;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", name="parent2visaExpiryDate", nullable=true)
     */
    private $parent2visaExpiryDate;

    /**
     * @var string|null
     * @ORM\Column(length=75, name="parent2email", nullable=true)
     */
    private $parent2email;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="parent2phone1Type", nullable=true)
     */
    private $parent2phone1Type;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="parent2phone1CountryCode", nullable=true)
     */
    private $parent2phone1CountryCode;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="parent2phone1", nullable=true)
     */
    private $parent2phone1;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="parent2phone2Type", nullable=true)
     */
    private $parent2phone2Type;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="parent2phone2CountryCode", nullable=true)
     */
    private $parent2phone2CountryCode;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="parent2phone2", nullable=true)
     */
    private $parent2phone2;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent2profession", nullable=true)
     */
    private $parent2profession;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="parent2employer", nullable=true)
     */
    private $parent2employer;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", name="timestamp", nullable=true)
     */
    private $timestamp;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", name="priority", columnDefinition="INT(1)", options={"default": "0"})
     */
    private $priority = 0;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $milestones;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", name="dateStart", nullable=true)
     */
    private $dateStart;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\RollGroup")
     * @ORM\JoinColumn(name="roll_group", referencedColumnName="id", nullable=true)
     */
    private $rollGroup;

    /**
     * @var Family|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Family")
     * @ORM\JoinColumn(name="family", referencedColumnName="id", nullable=true)
     */
    private $family;

    /**
     * @var string|null
     * @ORM\Column(name="howDidYouHear", nullable=true)
     */
    private $howDidYouHear;

    /**
     * @var string|null
     * @ORM\Column(name="howDidYouHearMore", nullable=true)
     */
    private $howDidYouHearMore;

    /**
     * @var string|null
     * @ORM\Column(name="paymentMade", length=10, options={"default": "N"})
     */
    private $paymentMade = 'N';

    /**
     * @var array
     */
    private static $paymentMadeList = ['N', 'Y', 'Exemption'];

    /**
     * @var Payment|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Finance\Entity\Payment")
     * @ORM\JoinColumn(name="payment_record", referencedColumnName="id", nullable=true)
     */
    private $paymentRecord;

    /**
     * @var string|null
     * @ORM\Column(name="studentID", nullable=true, length=10)
     */
    private $studentID;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $privacy;

    /**
     * @var string|null
     * @ORM\Column(type="text", options={"comment": "Serialised array of custom field values"})
     */
    private $fields;

    /**
     * @var string|null
     * @ORM\Column(type="text", options={"comment": "Serialised array of custom field values"})
     */
    private $parent1fields;

    /**
     * @var string|null
     * @ORM\Column(type="text", options={"comment": "Serialised array of custom field values"})
     */
    private $parent2fields;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return ApplicationForm
     */
    public function setId(?int $id): ApplicationForm
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApplicationFormHash(): ?string
    {
        return $this->applicationFormHash;
    }

    /**
     * @param string|null $applicationFormHash
     * @return ApplicationForm
     */
    public function setApplicationFormHash(?string $applicationFormHash): ApplicationForm
    {
        $this->applicationFormHash = $applicationFormHash;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string|null $surname
     * @return ApplicationForm
     */
    public function setSurname(?string $surname): ApplicationForm
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     * @return ApplicationForm
     */
    public function setFirstName(?string $firstName): ApplicationForm
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreferredName(): ?string
    {
        return $this->preferredName;
    }

    /**
     * @param string|null $preferredName
     * @return ApplicationForm
     */
    public function setPreferredName(?string $preferredName): ApplicationForm
    {
        $this->preferredName = $preferredName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    /**
     * @param string|null $officialName
     * @return ApplicationForm
     */
    public function setOfficialName(?string $officialName): ApplicationForm
    {
        $this->officialName = $officialName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameInCharacters(): ?string
    {
        return $this->nameInCharacters;
    }

    /**
     * @param string|null $nameInCharacters
     * @return ApplicationForm
     */
    public function setNameInCharacters(?string $nameInCharacters): ApplicationForm
    {
        $this->nameInCharacters = $nameInCharacters;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     * @return ApplicationForm
     */
    public function setGender(?string $gender): ApplicationForm
    {
        $this->gender = in_array($gender, self::getGenderList()) ? $gender : 'Unspecified';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     * @return ApplicationForm
     */
    public function setUsername(?string $username): ApplicationForm
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return ApplicationForm
     */
    public function setStatus(?string $status): ApplicationForm
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Pending';
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDob(): ?\DateTime
    {
        return $this->dob;
    }

    /**
     * @param \DateTime|null $dob
     * @return ApplicationForm
     */
    public function setDob(?\DateTime $dob): ApplicationForm
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return ApplicationForm
     */
    public function setEmail(?string $email): ApplicationForm
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeAddress(): ?string
    {
        return $this->homeAddress;
    }

    /**
     * @param string|null $homeAddress
     * @return ApplicationForm
     */
    public function setHomeAddress(?string $homeAddress): ApplicationForm
    {
        $this->homeAddress = $homeAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeAddressDistrict(): ?string
    {
        return $this->homeAddressDistrict;
    }

    /**
     * @param string|null $homeAddressDistrict
     * @return ApplicationForm
     */
    public function setHomeAddressDistrict(?string $homeAddressDistrict): ApplicationForm
    {
        $this->homeAddressDistrict = $homeAddressDistrict;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeAddressCountry(): ?string
    {
        return $this->homeAddressCountry;
    }

    /**
     * @param string|null $homeAddressCountry
     * @return ApplicationForm
     */
    public function setHomeAddressCountry(?string $homeAddressCountry): ApplicationForm
    {
        $this->homeAddressCountry = $homeAddressCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone1Type(): string
    {
        return $this->phone1Type;
    }

    /**
     * @param string $phone1Type
     * @return ApplicationForm
     */
    public function setPhone1Type(string $phone1Type): ApplicationForm
    {
        $this->phone1Type = in_array($phone1Type, self::getPhoneTypeList()) ? $phone1Type : '';
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone1CountryCode(): string
    {
        return $this->phone1CountryCode;
    }

    /**
     * @param string $phone1CountryCode
     * @return ApplicationForm
     */
    public function setPhone1CountryCode(string $phone1CountryCode): ApplicationForm
    {
        $this->phone1CountryCode = $phone1CountryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone1(): string
    {
        return $this->phone1;
    }

    /**
     * @param string $phone1
     * @return ApplicationForm
     */
    public function setPhone1(string $phone1): ApplicationForm
    {
        $this->phone1 = $phone1;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone2Type(): string
    {
        return $this->phone2Type;
    }

    /**
     * @param string $phone2Type
     * @return ApplicationForm
     */
    public function setPhone2Type(string $phone2Type): ApplicationForm
    {
        $this->phone2Type = in_array($phone2Type, self::getPhoneTypeList()) ? $phone2Type : '' ;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone2CountryCode(): string
    {
        return $this->phone2CountryCode;
    }

    /**
     * @param string $phone2CountryCode
     * @return ApplicationForm
     */
    public function setPhone2CountryCode(string $phone2CountryCode): ApplicationForm
    {
        $this->phone2CountryCode = $phone2CountryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone2(): string
    {
        return $this->phone2;
    }

    /**
     * @param string $phone2
     * @return ApplicationForm
     */
    public function setPhone2(string $phone2): ApplicationForm
    {
        $this->phone2 = $phone2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryOfBirth(): ?string
    {
        return $this->countryOfBirth;
    }

    /**
     * @param string|null $countryOfBirth
     * @return ApplicationForm
     */
    public function setCountryOfBirth(?string $countryOfBirth): ApplicationForm
    {
        $this->countryOfBirth = $countryOfBirth;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCitizenship1(): ?string
    {
        return $this->citizenship1;
    }

    /**
     * @param string|null $citizenship1
     * @return ApplicationForm
     */
    public function setCitizenship1(?string $citizenship1): ApplicationForm
    {
        $this->citizenship1 = $citizenship1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCitizenship1Passport(): ?string
    {
        return $this->citizenship1Passport;
    }

    /**
     * @param string|null $citizenship1Passport
     * @return ApplicationForm
     */
    public function setCitizenship1Passport(?string $citizenship1Passport): ApplicationForm
    {
        $this->citizenship1Passport = $citizenship1Passport;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNationalIDCardNumber(): ?string
    {
        return $this->nationalIDCardNumber;
    }

    /**
     * @param string|null $nationalIDCardNumber
     * @return ApplicationForm
     */
    public function setNationalIDCardNumber(?string $nationalIDCardNumber): ApplicationForm
    {
        $this->nationalIDCardNumber = $nationalIDCardNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResidencyStatus(): ?string
    {
        return $this->residencyStatus;
    }

    /**
     * @param string|null $residencyStatus
     * @return ApplicationForm
     */
    public function setResidencyStatus(?string $residencyStatus): ApplicationForm
    {
        $this->residencyStatus = $residencyStatus;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getVisaExpiryDate(): ?\DateTime
    {
        return $this->visaExpiryDate;
    }

    /**
     * @param \DateTime|null $visaExpiryDate
     * @return ApplicationForm
     */
    public function setVisaExpiryDate(?\DateTime $visaExpiryDate): ApplicationForm
    {
        $this->visaExpiryDate = $visaExpiryDate;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYearEntry(): ?AcademicYear
    {
        return $this->academicYearEntry;
    }

    /**
     * @param AcademicYear|null $academicYearEntry
     * @return ApplicationForm
     */
    public function setAcademicYearEntry(?AcademicYear $academicYearEntry): ApplicationForm
    {
        $this->academicYearEntry = $academicYearEntry;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getYearGroupEntry(): ?AcademicYear
    {
        return $this->yearGroupEntry;
    }

    /**
     * @param AcademicYear|null $yearGroupEntry
     * @return ApplicationForm
     */
    public function setYearGroupEntry(?AcademicYear $yearGroupEntry): ApplicationForm
    {
        $this->yearGroupEntry = $yearGroupEntry;
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
     * @return ApplicationForm
     */
    public function setDayType(?string $dayType): ApplicationForm
    {
        $this->dayType = $dayType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReferenceEmail(): ?string
    {
        return $this->referenceEmail;
    }

    /**
     * @param string|null $referenceEmail
     * @return ApplicationForm
     */
    public function setReferenceEmail(?string $referenceEmail): ApplicationForm
    {
        $this->referenceEmail = $referenceEmail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolName1(): ?string
    {
        return $this->schoolName1;
    }

    /**
     * @param string|null $schoolName1
     * @return ApplicationForm
     */
    public function setSchoolName1(?string $schoolName1): ApplicationForm
    {
        $this->schoolName1 = $schoolName1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolAddress1(): ?string
    {
        return $this->schoolAddress1;
    }

    /**
     * @param string|null $schoolAddress1
     * @return ApplicationForm
     */
    public function setSchoolAddress1(?string $schoolAddress1): ApplicationForm
    {
        $this->schoolAddress1 = $schoolAddress1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolGrades1(): ?string
    {
        return $this->schoolGrades1;
    }

    /**
     * @param string|null $schoolGrades1
     * @return ApplicationForm
     */
    public function setSchoolGrades1(?string $schoolGrades1): ApplicationForm
    {
        $this->schoolGrades1 = $schoolGrades1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolLanguage1(): ?string
    {
        return $this->schoolLanguage1;
    }

    /**
     * @param string|null $schoolLanguage1
     * @return ApplicationForm
     */
    public function setSchoolLanguage1(?string $schoolLanguage1): ApplicationForm
    {
        $this->schoolLanguage1 = $schoolLanguage1;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSchoolDate1(): ?\DateTime
    {
        return $this->schoolDate1;
    }

    /**
     * @param \DateTime|null $schoolDate1
     * @return ApplicationForm
     */
    public function setSchoolDate1(?\DateTime $schoolDate1): ApplicationForm
    {
        $this->schoolDate1 = $schoolDate1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolName2(): ?string
    {
        return $this->schoolName2;
    }

    /**
     * @param string|null $schoolName2
     * @return ApplicationForm
     */
    public function setSchoolName2(?string $schoolName2): ApplicationForm
    {
        $this->schoolName2 = $schoolName2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolAddress2(): ?string
    {
        return $this->schoolAddress2;
    }

    /**
     * @param string|null $schoolAddress2
     * @return ApplicationForm
     */
    public function setSchoolAddress2(?string $schoolAddress2): ApplicationForm
    {
        $this->schoolAddress2 = $schoolAddress2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolGrades2(): ?string
    {
        return $this->schoolGrades2;
    }

    /**
     * @param string|null $schoolGrades2
     * @return ApplicationForm
     */
    public function setSchoolGrades2(?string $schoolGrades2): ApplicationForm
    {
        $this->schoolGrades2 = $schoolGrades2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSchoolLanguage2(): ?string
    {
        return $this->schoolLanguage2;
    }

    /**
     * @param string|null $schoolLanguage2
     * @return ApplicationForm
     */
    public function setSchoolLanguage2(?string $schoolLanguage2): ApplicationForm
    {
        $this->schoolLanguage2 = $schoolLanguage2;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSchoolDate2(): ?\DateTime
    {
        return $this->schoolDate2;
    }

    /**
     * @param \DateTime|null $schoolDate2
     * @return ApplicationForm
     */
    public function setSchoolDate2(?\DateTime $schoolDate2): ApplicationForm
    {
        $this->schoolDate2 = $schoolDate2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiblingName1(): ?string
    {
        return $this->siblingName1;
    }

    /**
     * @param string|null $siblingName1
     * @return ApplicationForm
     */
    public function setSiblingName1(?string $siblingName1): ApplicationForm
    {
        $this->siblingName1 = $siblingName1;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSiblingDOB1(): ?\DateTime
    {
        return $this->siblingDOB1;
    }

    /**
     * @param \DateTime|null $siblingDOB1
     * @return ApplicationForm
     */
    public function setSiblingDOB1(?\DateTime $siblingDOB1): ApplicationForm
    {
        $this->siblingDOB1 = $siblingDOB1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiblingSchool1(): ?string
    {
        return $this->siblingSchool1;
    }

    /**
     * @param string|null $siblingSchool1
     * @return ApplicationForm
     */
    public function setSiblingSchool1(?string $siblingSchool1): ApplicationForm
    {
        $this->siblingSchool1 = $siblingSchool1;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSiblingSchoolJoiningDate1(): ?\DateTime
    {
        return $this->siblingSchoolJoiningDate1;
    }

    /**
     * @param \DateTime|null $siblingSchoolJoiningDate1
     * @return ApplicationForm
     */
    public function setSiblingSchoolJoiningDate1(?\DateTime $siblingSchoolJoiningDate1): ApplicationForm
    {
        $this->siblingSchoolJoiningDate1 = $siblingSchoolJoiningDate1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiblingName2(): ?string
    {
        return $this->siblingName2;
    }

    /**
     * @param string|null $siblingName2
     * @return ApplicationForm
     */
    public function setSiblingName2(?string $siblingName2): ApplicationForm
    {
        $this->siblingName2 = $siblingName2;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSiblingDOB2(): ?\DateTime
    {
        return $this->siblingDOB2;
    }

    /**
     * @param \DateTime|null $siblingDOB2
     * @return ApplicationForm
     */
    public function setSiblingDOB2(?\DateTime $siblingDOB2): ApplicationForm
    {
        $this->siblingDOB2 = $siblingDOB2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiblingSchool2(): ?string
    {
        return $this->siblingSchool2;
    }

    /**
     * @param string|null $siblingSchool2
     * @return ApplicationForm
     */
    public function setSiblingSchool2(?string $siblingSchool2): ApplicationForm
    {
        $this->siblingSchool2 = $siblingSchool2;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSiblingSchoolJoiningDate2(): ?\DateTime
    {
        return $this->siblingSchoolJoiningDate2;
    }

    /**
     * @param \DateTime|null $siblingSchoolJoiningDate2
     * @return ApplicationForm
     */
    public function setSiblingSchoolJoiningDate2(?\DateTime $siblingSchoolJoiningDate2): ApplicationForm
    {
        $this->siblingSchoolJoiningDate2 = $siblingSchoolJoiningDate2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiblingName3(): ?string
    {
        return $this->siblingName3;
    }

    /**
     * @param string|null $siblingName3
     * @return ApplicationForm
     */
    public function setSiblingName3(?string $siblingName3): ApplicationForm
    {
        $this->siblingName3 = $siblingName3;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSiblingDOB3(): ?\DateTime
    {
        return $this->siblingDOB3;
    }

    /**
     * @param \DateTime|null $siblingDOB3
     * @return ApplicationForm
     */
    public function setSiblingDOB3(?\DateTime $siblingDOB3): ApplicationForm
    {
        $this->siblingDOB3 = $siblingDOB3;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiblingSchool3(): ?string
    {
        return $this->siblingSchool3;
    }

    /**
     * @param string|null $siblingSchool3
     * @return ApplicationForm
     */
    public function setSiblingSchool3(?string $siblingSchool3): ApplicationForm
    {
        $this->siblingSchool3 = $siblingSchool3;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSiblingSchoolJoiningDate3(): ?\DateTime
    {
        return $this->siblingSchoolJoiningDate3;
    }

    /**
     * @param \DateTime|null $siblingSchoolJoiningDate3
     * @return ApplicationForm
     */
    public function setSiblingSchoolJoiningDate3(?\DateTime $siblingSchoolJoiningDate3): ApplicationForm
    {
        $this->siblingSchoolJoiningDate3 = $siblingSchoolJoiningDate3;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageHomePrimary(): ?string
    {
        return $this->languageHomePrimary;
    }

    /**
     * @param string|null $languageHomePrimary
     * @return ApplicationForm
     */
    public function setLanguageHomePrimary(?string $languageHomePrimary): ApplicationForm
    {
        $this->languageHomePrimary = $languageHomePrimary;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageHomeSecondary(): ?string
    {
        return $this->languageHomeSecondary;
    }

    /**
     * @param string|null $languageHomeSecondary
     * @return ApplicationForm
     */
    public function setLanguageHomeSecondary(?string $languageHomeSecondary): ApplicationForm
    {
        $this->languageHomeSecondary = $languageHomeSecondary;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageFirst(): ?string
    {
        return $this->languageFirst;
    }

    /**
     * @param string|null $languageFirst
     * @return ApplicationForm
     */
    public function setLanguageFirst(?string $languageFirst): ApplicationForm
    {
        $this->languageFirst = $languageFirst;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageSecond(): ?string
    {
        return $this->languageSecond;
    }

    /**
     * @param string|null $languageSecond
     * @return ApplicationForm
     */
    public function setLanguageSecond(?string $languageSecond): ApplicationForm
    {
        $this->languageSecond = $languageSecond;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageThird(): ?string
    {
        return $this->languageThird;
    }

    /**
     * @param string|null $languageThird
     * @return ApplicationForm
     */
    public function setLanguageThird(?string $languageThird): ApplicationForm
    {
        $this->languageThird = $languageThird;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMedicalInformation(): ?string
    {
        return $this->medicalInformation;
    }

    /**
     * @param string|null $medicalInformation
     * @return ApplicationForm
     */
    public function setMedicalInformation(?string $medicalInformation): ApplicationForm
    {
        $this->medicalInformation = $medicalInformation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSen(): ?string
    {
        return $this->sen;
    }

    /**
     * @param string|null $sen
     * @return ApplicationForm
     */
    public function setSen(?string $sen): ApplicationForm
    {
        $this->sen = $sen;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenDetails(): ?string
    {
        return $this->senDetails;
    }

    /**
     * @param string|null $senDetails
     * @return ApplicationForm
     */
    public function setSenDetails(?string $senDetails): ApplicationForm
    {
        $this->senDetails = $senDetails;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageChoice(): ?string
    {
        return $this->languageChoice;
    }

    /**
     * @param string|null $languageChoice
     * @return ApplicationForm
     */
    public function setLanguageChoice(?string $languageChoice): ApplicationForm
    {
        $this->languageChoice = $languageChoice;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageChoiceExperience(): ?string
    {
        return $this->languageChoiceExperience;
    }

    /**
     * @param string|null $languageChoiceExperience
     * @return ApplicationForm
     */
    public function setLanguageChoiceExperience(?string $languageChoiceExperience): ApplicationForm
    {
        $this->languageChoiceExperience = $languageChoiceExperience;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScholarshipInterest(): ?string
    {
        return $this->scholarshipInterest;
    }

    /**
     * @param string|null $scholarshipInterest
     * @return ApplicationForm
     */
    public function setScholarshipInterest(?string $scholarshipInterest): ApplicationForm
    {
        $this->scholarshipInterest = $scholarshipInterest;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScholarshipRequired(): ?string
    {
        return $this->scholarshipRequired;
    }

    /**
     * @param string|null $scholarshipRequired
     * @return ApplicationForm
     */
    public function setScholarshipRequired(?string $scholarshipRequired): ApplicationForm
    {
        $this->scholarshipRequired = $scholarshipRequired;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayment(): ?string
    {
        return $this->payment;
    }

    /**
     * @param string|null $payment
     * @return ApplicationForm
     */
    public function setPayment(?string $payment): ApplicationForm
    {
        $this->payment = in_array($payment, self::getPaymentList()) ? $payment : 'Family';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return ApplicationForm
     */
    public function setCompanyName(?string $companyName): ApplicationForm
    {
        $this->companyName = $companyName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyContact(): ?string
    {
        return $this->companyContact;
    }

    /**
     * @param string|null $companyContact
     * @return ApplicationForm
     */
    public function setCompanyContact(?string $companyContact): ApplicationForm
    {
        $this->companyContact = $companyContact;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    /**
     * @param string|null $companyAddress
     * @return ApplicationForm
     */
    public function setCompanyAddress(?string $companyAddress): ApplicationForm
    {
        $this->companyAddress = $companyAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    /**
     * @param string|null $companyEmail
     * @return ApplicationForm
     */
    public function setCompanyEmail(?string $companyEmail): ApplicationForm
    {
        $this->companyEmail = $companyEmail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyCCFamily(): ?string
    {
        return $this->companyCCFamily;
    }

    /**
     * @param string|null $companyCCFamily
     * @return ApplicationForm
     */
    public function setCompanyCCFamily(?string $companyCCFamily): ApplicationForm
    {
        $this->companyCCFamily = $companyCCFamily;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    /**
     * @param string|null $companyPhone
     * @return ApplicationForm
     */
    public function setCompanyPhone(?string $companyPhone): ApplicationForm
    {
        $this->companyPhone = $companyPhone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyAll(): ?string
    {
        return $this->companyAll;
    }

    /**
     * @param string|null $companyAll
     * @return ApplicationForm
     */
    public function setCompanyAll(?string $companyAll): ApplicationForm
    {
        $this->companyAll = $companyAll;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFinanceFeeCategoryList(): ?string
    {
        return $this->financeFeeCategoryList;
    }

    /**
     * @param string|null $financeFeeCategoryList
     * @return ApplicationForm
     */
    public function setFinanceFeeCategoryList(?string $financeFeeCategoryList): ApplicationForm
    {
        $this->financeFeeCategoryList = $financeFeeCategoryList;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAgreement(): ?string
    {
        return $this->agreement;
    }

    /**
     * @param string|null $agreement
     * @return ApplicationForm
     */
    public function setAgreement(?string $agreement): ApplicationForm
    {
        $this->agreement = $agreement;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getParent1(): ?Person
    {
        return $this->parent1;
    }

    /**
     * @param Person|null $parent1
     * @return ApplicationForm
     */
    public function setParent1(?Person $parent1): ApplicationForm
    {
        $this->parent1 = $parent1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1Title(): ?string
    {
        return $this->parent1title;
    }

    /**
     * @param string|null $parent1title
     * @return ApplicationForm
     */
    public function setParent1Title(?string $parent1title): ApplicationForm
    {
        $this->parent1title = $parent1title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1Surname(): ?string
    {
        return $this->parent1surname;
    }

    /**
     * @param string|null $parent1surname
     * @return ApplicationForm
     */
    public function setParent1Surname(?string $parent1surname): ApplicationForm
    {
        $this->parent1surname = $parent1surname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1FirstName(): ?string
    {
        return $this->parent1firstName;
    }

    /**
     * @param string|null $parent1firstName
     * @return ApplicationForm
     */
    public function setParent1FirstName(?string $parent1firstName): ApplicationForm
    {
        $this->parent1firstName = $parent1firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1preferredName(): ?string
    {
        return $this->parent1preferredName;
    }

    /**
     * @param string|null $parent1preferredName
     * @return ApplicationForm
     */
    public function setParent1preferredName(?string $parent1preferredName): ApplicationForm
    {
        $this->parent1preferredName = $parent1preferredName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1officialName(): ?string
    {
        return $this->parent1officialName;
    }

    /**
     * @param string|null $parent1officialName
     * @return ApplicationForm
     */
    public function setParent1officialName(?string $parent1officialName): ApplicationForm
    {
        $this->parent1officialName = $parent1officialName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1nameInCharacters(): ?string
    {
        return $this->parent1nameInCharacters;
    }

    /**
     * @param string|null $parent1nameInCharacters
     * @return ApplicationForm
     */
    public function setParent1nameInCharacters(?string $parent1nameInCharacters): ApplicationForm
    {
        $this->parent1nameInCharacters = $parent1nameInCharacters;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1Gender(): ?string
    {
        return $this->parent1gender;
    }

    /**
     * @param string|null $parent1gender
     * @return ApplicationForm
     */
    public function setParent1Gender(?string $parent1gender): ApplicationForm
    {
        $this->parent1gender = in_array($parent1gender, self::getGenderList()) ? $parent1gender : 'Unspecified';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1relationship(): ?string
    {
        return $this->parent1relationship;
    }

    /**
     * @param string|null $parent1relationship
     * @return ApplicationForm
     */
    public function setParent1relationship(?string $parent1relationship): ApplicationForm
    {
        $this->parent1relationship = $parent1relationship;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1languageFirst(): ?string
    {
        return $this->parent1languageFirst;
    }

    /**
     * @param string|null $parent1languageFirst
     * @return ApplicationForm
     */
    public function setParent1languageFirst(?string $parent1languageFirst): ApplicationForm
    {
        $this->parent1languageFirst = $parent1languageFirst;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1languageSecond(): ?string
    {
        return $this->parent1languageSecond;
    }

    /**
     * @param string|null $parent1languageSecond
     * @return ApplicationForm
     */
    public function setParent1languageSecond(?string $parent1languageSecond): ApplicationForm
    {
        $this->parent1languageSecond = $parent1languageSecond;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1citizenship1(): ?string
    {
        return $this->parent1citizenship1;
    }

    /**
     * @param string|null $parent1citizenship1
     * @return ApplicationForm
     */
    public function setParent1citizenship1(?string $parent1citizenship1): ApplicationForm
    {
        $this->parent1citizenship1 = $parent1citizenship1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1nationalIDCardNumber(): ?string
    {
        return $this->parent1nationalIDCardNumber;
    }

    /**
     * @param string|null $parent1nationalIDCardNumber
     * @return ApplicationForm
     */
    public function setParent1nationalIDCardNumber(?string $parent1nationalIDCardNumber): ApplicationForm
    {
        $this->parent1nationalIDCardNumber = $parent1nationalIDCardNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1residencyStatus(): ?string
    {
        return $this->parent1residencyStatus;
    }

    /**
     * @param string|null $parent1residencyStatus
     * @return ApplicationForm
     */
    public function setParent1residencyStatus(?string $parent1residencyStatus): ApplicationForm
    {
        $this->parent1residencyStatus = $parent1residencyStatus;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getParent1visaExpiryDate(): ?\DateTime
    {
        return $this->parent1visaExpiryDate;
    }

    /**
     * @param \DateTime|null $parent1visaExpiryDate
     * @return ApplicationForm
     */
    public function setParent1visaExpiryDate(?\DateTime $parent1visaExpiryDate): ApplicationForm
    {
        $this->parent1visaExpiryDate = $parent1visaExpiryDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1email(): ?string
    {
        return $this->parent1email;
    }

    /**
     * @param string|null $parent1email
     * @return ApplicationForm
     */
    public function setParent1email(?string $parent1email): ApplicationForm
    {
        $this->parent1email = $parent1email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1phone1Type(): ?string
    {
        return $this->parent1phone1Type;
    }

    /**
     * @param string|null $parent1phone1Type
     * @return ApplicationForm
     */
    public function setParent1phone1Type(?string $parent1phone1Type): ApplicationForm
    {
        $this->parent1phone1Type = $parent1phone1Type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1phone1CountryCode(): ?string
    {
        return $this->parent1phone1CountryCode;
    }

    /**
     * @param string|null $parent1phone1CountryCode
     * @return ApplicationForm
     */
    public function setParent1phone1CountryCode(?string $parent1phone1CountryCode): ApplicationForm
    {
        $this->parent1phone1CountryCode = $parent1phone1CountryCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1phone1(): ?string
    {
        return $this->parent1phone1;
    }

    /**
     * @param string|null $parent1phone1
     * @return ApplicationForm
     */
    public function setParent1phone1(?string $parent1phone1): ApplicationForm
    {
        $this->parent1phone1 = $parent1phone1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1phone2Type(): ?string
    {
        return $this->parent1phone2Type;
    }

    /**
     * @param string|null $parent1phone2Type
     * @return ApplicationForm
     */
    public function setParent1phone2Type(?string $parent1phone2Type): ApplicationForm
    {
        $this->parent1phone2Type = $parent1phone2Type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1phone2CountryCode(): ?string
    {
        return $this->parent1phone2CountryCode;
    }

    /**
     * @param string|null $parent1phone2CountryCode
     * @return ApplicationForm
     */
    public function setParent1phone2CountryCode(?string $parent1phone2CountryCode): ApplicationForm
    {
        $this->parent1phone2CountryCode = $parent1phone2CountryCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1phone2(): ?string
    {
        return $this->parent1phone2;
    }

    /**
     * @param string|null $parent1phone2
     * @return ApplicationForm
     */
    public function setParent1phone2(?string $parent1phone2): ApplicationForm
    {
        $this->parent1phone2 = $parent1phone2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1profession(): ?string
    {
        return $this->parent1profession;
    }

    /**
     * @param string|null $parent1profession
     * @return ApplicationForm
     */
    public function setParent1profession(?string $parent1profession): ApplicationForm
    {
        $this->parent1profession = $parent1profession;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1employer(): ?string
    {
        return $this->parent1employer;
    }

    /**
     * @param string|null $parent1employer
     * @return ApplicationForm
     */
    public function setParent1employer(?string $parent1employer): ApplicationForm
    {
        $this->parent1employer = $parent1employer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2Title(): ?string
    {
        return $this->parent2title;
    }

    /**
     * @param string|null $parent2title
     * @return ApplicationForm
     */
    public function setParent2Title(?string $parent2title): ApplicationForm
    {
        $this->parent2title = $parent2title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2Surname(): ?string
    {
        return $this->parent2surname;
    }

    /**
     * @param string|null $parent2surname
     * @return ApplicationForm
     */
    public function setParent2Surname(?string $parent2surname): ApplicationForm
    {
        $this->parent2surname = $parent2surname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2FirstName(): ?string
    {
        return $this->parent2firstName;
    }

    /**
     * @param string|null $parent2firstName
     * @return ApplicationForm
     */
    public function setParent2FirstName(?string $parent2firstName): ApplicationForm
    {
        $this->parent2firstName = $parent2firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2preferredName(): ?string
    {
        return $this->parent2preferredName;
    }

    /**
     * @param string|null $parent2preferredName
     * @return ApplicationForm
     */
    public function setParent2preferredName(?string $parent2preferredName): ApplicationForm
    {
        $this->parent2preferredName = $parent2preferredName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2officialName(): ?string
    {
        return $this->parent2officialName;
    }

    /**
     * @param string|null $parent2officialName
     * @return ApplicationForm
     */
    public function setParent2officialName(?string $parent2officialName): ApplicationForm
    {
        $this->parent2officialName = $parent2officialName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2nameInCharacters(): ?string
    {
        return $this->parent2nameInCharacters;
    }

    /**
     * @param string|null $parent2nameInCharacters
     * @return ApplicationForm
     */
    public function setParent2nameInCharacters(?string $parent2nameInCharacters): ApplicationForm
    {
        $this->parent2nameInCharacters = $parent2nameInCharacters;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2Gender(): ?string
    {
        return $this->parent2gender;
    }

    /**
     * @param string|null $parent2gender
     * @return ApplicationForm
     */
    public function setParent2Gender(?string $parent2gender): ApplicationForm
    {
        $this->parent2gender = in_array($parent2gender, self::getGenderList()) ? $parent2gender : 'Unspecified';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2relationship(): ?string
    {
        return $this->parent2relationship;
    }

    /**
     * @param string|null $parent2relationship
     * @return ApplicationForm
     */
    public function setParent2relationship(?string $parent2relationship): ApplicationForm
    {
        $this->parent2relationship = $parent2relationship;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2languageFirst(): ?string
    {
        return $this->parent2languageFirst;
    }

    /**
     * @param string|null $parent2languageFirst
     * @return ApplicationForm
     */
    public function setParent2languageFirst(?string $parent2languageFirst): ApplicationForm
    {
        $this->parent2languageFirst = $parent2languageFirst;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2languageSecond(): ?string
    {
        return $this->parent2languageSecond;
    }

    /**
     * @param string|null $parent2languageSecond
     * @return ApplicationForm
     */
    public function setParent2languageSecond(?string $parent2languageSecond): ApplicationForm
    {
        $this->parent2languageSecond = $parent2languageSecond;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2citizenship1(): ?string
    {
        return $this->parent2citizenship1;
    }

    /**
     * @param string|null $parent2citizenship1
     * @return ApplicationForm
     */
    public function setParent2citizenship1(?string $parent2citizenship1): ApplicationForm
    {
        $this->parent2citizenship1 = $parent2citizenship1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2nationalIDCardNumber(): ?string
    {
        return $this->parent2nationalIDCardNumber;
    }

    /**
     * @param string|null $parent2nationalIDCardNumber
     * @return ApplicationForm
     */
    public function setParent2nationalIDCardNumber(?string $parent2nationalIDCardNumber): ApplicationForm
    {
        $this->parent2nationalIDCardNumber = $parent2nationalIDCardNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2residencyStatus(): ?string
    {
        return $this->parent2residencyStatus;
    }

    /**
     * @param string|null $parent2residencyStatus
     * @return ApplicationForm
     */
    public function setParent2residencyStatus(?string $parent2residencyStatus): ApplicationForm
    {
        $this->parent2residencyStatus = $parent2residencyStatus;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getParent2visaExpiryDate(): ?\DateTime
    {
        return $this->parent2visaExpiryDate;
    }

    /**
     * @param \DateTime|null $parent2visaExpiryDate
     * @return ApplicationForm
     */
    public function setParent2visaExpiryDate(?\DateTime $parent2visaExpiryDate): ApplicationForm
    {
        $this->parent2visaExpiryDate = $parent2visaExpiryDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2email(): ?string
    {
        return $this->parent2email;
    }

    /**
     * @param string|null $parent2email
     * @return ApplicationForm
     */
    public function setParent2email(?string $parent2email): ApplicationForm
    {
        $this->parent2email = $parent2email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2phone1Type(): ?string
    {
        return $this->parent2phone1Type;
    }

    /**
     * @param string|null $parent2phone1Type
     * @return ApplicationForm
     */
    public function setParent2phone1Type(?string $parent2phone1Type): ApplicationForm
    {
        $this->parent2phone1Type = $parent2phone1Type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2phone1CountryCode(): ?string
    {
        return $this->parent2phone1CountryCode;
    }

    /**
     * @param string|null $parent2phone1CountryCode
     * @return ApplicationForm
     */
    public function setParent2phone1CountryCode(?string $parent2phone1CountryCode): ApplicationForm
    {
        $this->parent2phone1CountryCode = $parent2phone1CountryCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2phone1(): ?string
    {
        return $this->parent2phone1;
    }

    /**
     * @param string|null $parent2phone1
     * @return ApplicationForm
     */
    public function setParent2phone1(?string $parent2phone1): ApplicationForm
    {
        $this->parent2phone1 = $parent2phone1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2phone2Type(): ?string
    {
        return $this->parent2phone2Type;
    }

    /**
     * @param string|null $parent2phone2Type
     * @return ApplicationForm
     */
    public function setParent2phone2Type(?string $parent2phone2Type): ApplicationForm
    {
        $this->parent2phone2Type = $parent2phone2Type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2phone2CountryCode(): ?string
    {
        return $this->parent2phone2CountryCode;
    }

    /**
     * @param string|null $parent2phone2CountryCode
     * @return ApplicationForm
     */
    public function setParent2phone2CountryCode(?string $parent2phone2CountryCode): ApplicationForm
    {
        $this->parent2phone2CountryCode = $parent2phone2CountryCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2phone2(): ?string
    {
        return $this->parent2phone2;
    }

    /**
     * @param string|null $parent2phone2
     * @return ApplicationForm
     */
    public function setParent2phone2(?string $parent2phone2): ApplicationForm
    {
        $this->parent2phone2 = $parent2phone2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2profession(): ?string
    {
        return $this->parent2profession;
    }

    /**
     * @param string|null $parent2profession
     * @return ApplicationForm
     */
    public function setParent2profession(?string $parent2profession): ApplicationForm
    {
        $this->parent2profession = $parent2profession;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2employer(): ?string
    {
        return $this->parent2employer;
    }

    /**
     * @param string|null $parent2employer
     * @return ApplicationForm
     */
    public function setParent2employer(?string $parent2employer): ApplicationForm
    {
        $this->parent2employer = $parent2employer;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime|null $timestamp
     * @return ApplicationForm
     */
    public function setTimestamp(?\DateTime $timestamp): ApplicationForm
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int|null $priority
     * @return ApplicationForm
     */
    public function setPriority(?int $priority): ApplicationForm
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMilestones(): ?string
    {
        return $this->milestones;
    }

    /**
     * @param string|null $milestones
     * @return ApplicationForm
     */
    public function setMilestones(?string $milestones): ApplicationForm
    {
        $this->milestones = $milestones;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     * @return ApplicationForm
     */
    public function setNotes(?string $notes): ApplicationForm
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateStart(): ?\DateTime
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime|null $dateStart
     * @return ApplicationForm
     */
    public function setDateStart(?\DateTime $dateStart): ApplicationForm
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return $this->rollGroup;
    }

    /**
     * @param RollGroup|null $rollGroup
     * @return ApplicationForm
     */
    public function setRollGroup(?RollGroup $rollGroup): ApplicationForm
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * @return Family|null
     */
    public function getFamily(): ?Family
    {
        return $this->family;
    }

    /**
     * @param Family|null $family
     * @return ApplicationForm
     */
    public function setFamily(?Family $family): ApplicationForm
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHowDidYouHear(): ?string
    {
        return $this->howDidYouHear;
    }

    /**
     * @param string|null $howDidYouHear
     * @return ApplicationForm
     */
    public function setHowDidYouHear(?string $howDidYouHear): ApplicationForm
    {
        $this->howDidYouHear = $howDidYouHear;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHowDidYouHearMore(): ?string
    {
        return $this->howDidYouHearMore;
    }

    /**
     * @param string|null $howDidYouHearMore
     * @return ApplicationForm
     */
    public function setHowDidYouHearMore(?string $howDidYouHearMore): ApplicationForm
    {
        $this->howDidYouHearMore = $howDidYouHearMore;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentMade(): ?string
    {
        return $this->paymentMade;
    }

    /**
     * @param string|null $paymentMade
     * @return ApplicationForm
     */
    public function setPaymentMade(?string $paymentMade): ApplicationForm
    {
        $this->paymentMade = in_array($paymentMade, self::getPaymentMadeList()) ? $paymentMade : 'N';
        return $this;
    }

    /**
     * @return Payment|null
     */
    public function getPaymentRecord(): ?Payment
    {
        return $this->paymentRecord;
    }

    /**
     * @param Payment|null $paymentRecord
     * @return ApplicationForm
     */
    public function setPaymentRecord(?Payment $paymentRecord): ApplicationForm
    {
        $this->paymentRecord = $paymentRecord;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStudentID(): ?string
    {
        return $this->studentID;
    }

    /**
     * @param string|null $studentID
     * @return ApplicationForm
     */
    public function setStudentID(?string $studentID): ApplicationForm
    {
        $this->studentID = $studentID;
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
     * @return ApplicationForm
     */
    public function setPrivacy(?string $privacy): ApplicationForm
    {
        $this->privacy = $privacy;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFields(): ?string
    {
        return $this->fields;
    }

    /**
     * @param string|null $fields
     * @return ApplicationForm
     */
    public function setFields(?string $fields): ApplicationForm
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent1fields(): ?string
    {
        return $this->parent1fields;
    }

    /**
     * @param string|null $parent1fields
     * @return ApplicationForm
     */
    public function setParent1fields(?string $parent1fields): ApplicationForm
    {
        $this->parent1fields = $parent1fields;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent2fields(): ?string
    {
        return $this->parent2fields;
    }

    /**
     * @param string|null $parent2fields
     * @return ApplicationForm
     */
    public function setParent2fields(?string $parent2fields): ApplicationForm
    {
        $this->parent2fields = $parent2fields;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @return array
     */
    public static function getPhoneTypeList(): array
    {
        return Person::getPhoneTypeList();
    }

    /**
     * @return array
     */
    public static function getPaymentList(): array
    {
        return self::$paymentList;
    }

    /**
     * @return array
     */
    public static function getPaymentMadeList(): array
    {
        return self::$paymentMadeList;
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
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__ApplicationForm` (
                    `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `applicationFormHash` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `surname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                    `firstName` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                    `preferredName` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                    `officialName` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
                    `nameInCharacters` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                    `gender` varchar(12) COLLATE utf8_unicode_ci DEFAULT 'Unspecified',
                    `username` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `status` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
                    `dob` date DEFAULT NULL,
                    `email` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `homeAddress` longtext COLLATE utf8_unicode_ci,
                    `homeAddressDistrict` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `homeAddressCountry` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `phone1Type` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
                    `phone1CountryCode` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
                    `phone1` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                    `phone2Type` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
                    `phone2CountryCode` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
                    `phone2` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                    `countryOfBirth` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `citizenship1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `citizenship1Passport` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `nationalIDCardNumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `residencyStatus` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `visaExpiryDate` date DEFAULT NULL,
                    `dayType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `referenceEmail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `schoolName1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolAddress1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolGrades1` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolLanguage1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolDate1` date DEFAULT NULL,
                    `schoolName2` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolAddress2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolGrades2` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolLanguage2` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `schoolDate2` date DEFAULT NULL,
                    `siblingName1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `siblingDOB1` date DEFAULT NULL,
                    `siblingSchool1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `siblingSchoolJoiningDate1` date DEFAULT NULL,
                    `siblingName2` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `siblingDOB2` date DEFAULT NULL,
                    `siblingSchool2` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `siblingSchoolJoiningDate2` date DEFAULT NULL,
                    `siblingName3` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `siblingDOB3` date DEFAULT NULL,
                    `siblingSchool3` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `siblingSchoolJoiningDate3` date DEFAULT NULL,
                    `languageHomePrimary` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `languageHomeSecondary` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `languageFirst` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `languageSecond` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `languageThird` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `medicalInformation` longtext COLLATE utf8_unicode_ci NOT NULL,
                    `sen` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `senDetails` longtext COLLATE utf8_unicode_ci NOT NULL,
                    `languageChoice` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `languageChoiceExperience` longtext COLLATE utf8_unicode_ci,
                    `scholarshipInterest` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `scholarshipRequired` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `payment` varchar(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Family',
                    `companyName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `companyContact` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `companyAddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `companyEmail` longtext COLLATE utf8_unicode_ci,
                    `companyCCFamily` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'When company is billed, should family receive a copy?',
                    `companyPhone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `companyAll` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `__prefix__FinanceFeeCategoryIDList` longtext COLLATE utf8_unicode_ci,
                    `agreement` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1title` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1surname` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1firstName` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1preferredName` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1officialName` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1nameInCharacters` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1gender` varchar(12) COLLATE utf8_unicode_ci DEFAULT 'Unspecified',
                    `parent1relationship` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1languageFirst` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1languageSecond` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1citizenship1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1nationalIDCardNumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1residencyStatus` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1visaExpiryDate` date DEFAULT NULL,
                    `parent1email` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1phone1Type` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1phone1CountryCode` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1phone1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1phone2Type` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1phone2CountryCode` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1phone2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1profession` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent1employer` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2title` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2surname` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2firstName` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2preferredName` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2officialName` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2nameInCharacters` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2gender` varchar(12) COLLATE utf8_unicode_ci DEFAULT 'Unspecified',
                    `parent2relationship` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2languageFirst` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2languageSecond` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2citizenship1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2nationalIDCardNumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2residencyStatus` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2visaExpiryDate` date DEFAULT NULL,
                    `parent2email` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2phone1Type` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2phone1CountryCode` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2phone1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2phone2Type` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2phone2CountryCode` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2phone2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2profession` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `parent2employer` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `timestamp` datetime DEFAULT NULL,
                    `priority` int(1) DEFAULT NULL,
                    `milestones` longtext COLLATE utf8_unicode_ci NOT NULL,
                    `notes` longtext COLLATE utf8_unicode_ci NOT NULL,
                    `dateStart` date DEFAULT NULL,
                    `howDidYouHear` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `howDidYouHearMore` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `paymentMade` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `studentID` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `privacy` longtext COLLATE utf8_unicode_ci,
                    `fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Serialised array of custom field values',
                    `parent1fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Serialised array of custom field values',
                    `parent2fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Serialised array of custom field values',
                    `academic_year_entry` int(3) UNSIGNED DEFAULT NULL,
                    `year_group_entry` int(3) UNSIGNED DEFAULT NULL,
                    `parent1` int(10) UNSIGNED DEFAULT NULL,
                    `roll_group` int(5) UNSIGNED DEFAULT NULL,
                    `family` int(7) UNSIGNED DEFAULT NULL,
                    `payment_record` int(14) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `IDX_A309B59CF9B7736F` (`academic_year_entry`),
                    KEY `IDX_A309B59C9DE35FD8` (`year_group_entry`),
                    KEY `IDX_A309B59C7DF7AB4B` (`parent1`),
                    KEY `IDX_A309B59CA85AE4EC` (`roll_group`),
                    KEY `IDX_A309B59C51F0BB1F` (`family`),
                    KEY `IDX_A309B59CA0F353A3` (`payment_record`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__ApplicationForm`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year_entry`) REFERENCES `__prefix__AcademicYear` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`year_group_entry`) REFERENCES `__prefix__YearGroup` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`parent1`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`roll_group`) REFERENCES `__prefix__RollGroup` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`family`) REFERENCES `__prefix__Family` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`payment_record`) REFERENCES `__prefix__Payment` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }

}
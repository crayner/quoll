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
namespace App\Modules\Medical\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonMedical
 * @package App\Modules\Medical\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Medical\Repository\PersonMedicalRepository")
 * @ORM\Table(name="PersonMedical",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="student", columns={"student"})})
 * @UniqueEntity("student")
 */
class PersonMedical extends AbstractEntity
{
    const VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",unique=true)
     */
    private $student;

    /**
     * @var string
     * @ORM\Column(length=3,nullable=true)
     * @Assert\Choice(callback="getBloodTypeList")
     */
    private $bloodType;

    /**
     * @var array
     */
    private static $bloodTypeList = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'];

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     */
    private $longTermMedication = 'N';

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $longTermMedicationDetails;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $lastTetanusDate;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * @var Collection|PersonMedicalCondition[]
     * @ORM\OneToMany(targetEntity="PersonMedicalCondition", mappedBy="personMedical")
     * @ORM\JoinColumn(name="gibbonPersonMedicalID", referencedColumnName="gibbonPersonMedicalID", nullable=false)
     */
    private $personMedicalConditions;

    /**
     * PersonMedical constructor.
     */
    public function __construct()
    {
        $this->setPersonMedicalConditions(new ArrayCollection());
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
     * @return PersonMedical
     */
    public function setId(?string $id): PersonMedical
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getStudent(): ?Person
    {
        return $this->student;
    }

    /**
     * @param Person|null $student
     * @return PersonMedical
     */
    public function setStudent(?Person $student): PersonMedical
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return string
     */
    public function getBloodType(): string
    {
        return $this->bloodType;
    }

    /**
     * @param string $bloodType
     * @return PersonMedical
     */
    public function setBloodType(string $bloodType): PersonMedical
    {
        $this->bloodType = in_array($bloodType, self::getBloodTypeList()) ? $bloodType : '';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLongTermMedication(): ?string
    {
        return $this->longTermMedication;
    }

    /**
     * @param string|null $longTermMedication
     * @return PersonMedical
     */
    public function setLongTermMedication(?string $longTermMedication): PersonMedical
    {
        $this->longTermMedication = self::checkBoolean($longTermMedication, '');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLongTermMedicationDetails(): ?string
    {
        return $this->longTermMedicationDetails;
    }

    /**
     * @param string|null $longTermMedicationDetails
     * @return PersonMedical
     */
    public function setLongTermMedicationDetails(?string $longTermMedicationDetails): PersonMedical
    {
        $this->longTermMedicationDetails = $longTermMedicationDetails;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastTetanusDate(): ?\DateTimeImmutable
    {
        return $this->lastTetanusDate;
    }

    /**
     * @param \DateTimeImmutable|null $lastTetanusDate
     * @return PersonMedical
     */
    public function setLastTetanusDate(?\DateTimeImmutable $lastTetanusDate): PersonMedical
    {
        $this->lastTetanusDate = $lastTetanusDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return PersonMedical
     */
    public function setComment(?string $comment): PersonMedical
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return array
     */
    public static function getBloodTypeList(): array
    {
        return self::$bloodTypeList;
    }

    /**
     * @return PersonMedicalCondition[]|Collection
     */
    public function getPersonMedicalConditions()
    {
        return $this->personMedicalConditions;
    }

    /**
     * PersonMedicalConditions.
     *
     * @param PersonMedicalCondition[]|Collection $personMedicalConditions
     * @return PersonMedical
     */
    public function setPersonMedicalConditions($personMedicalConditions)
    {
        $this->personMedicalConditions = $personMedicalConditions;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * create
     * @return array|string[]
     * 22/06/2020 10:47
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__PersonMedical` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `student` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `blood_type` varchar(3) CHARACTER SET utf8mb4 DEFAULT NULL,
                    `long_term_medication` varchar(1) NOT NULL DEFAULT 'N',
                    `long_term_medication_details` longtext,
                    `comment` longtext,
                    `last_tetanus_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `student` (`student`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 22/06/2020 10:49
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__PersonMedical`
                    ADD CONSTRAINT FOREIGN KEY (`student`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 22/06/2020 10:49
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}

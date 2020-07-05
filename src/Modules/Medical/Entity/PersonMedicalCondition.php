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
use App\Modules\School\Entity\AlertLevel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class PersonMedicalCondition
 * @package App\Modules\Medical\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Medical\Repository\PersonMedicalConditionRepository")
 * @ORM\Table(name="PersonMedicalCondition",
 *     indexes={@ORM\Index(name="person_medical", columns={"person_medical"}),
 *     @ORM\Index(name="alert_level", columns={"alert_level"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="person_condition_level_trigger", columns={"person_medical","name","alert_level","triggers"})})
 * @UniqueEntity({"personMedical","name","alertLevel","triggers"})
 */
class PersonMedicalCondition extends AbstractEntity
{
    const VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var PersonMedical|null
     * @ORM\ManyToOne(targetEntity="PersonMedical", inversedBy="personMedicalConditions")
     * @ORM\JoinColumn(name="person_medical",referencedColumnName="id")
     */
    private $personMedical;

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var AlertLevel|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AlertLevel")
     * @ORM\JoinColumn(name="alert_level",referencedColumnName="id")
     */
    private $alertLevel;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $triggers;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $reaction;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $response;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $medication;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $lastEpisode;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $lastEpisodeTreatment;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return PersonMedicalCondition
     */
    public function setId(?string $id): PersonMedicalCondition
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return PersonMedical|null
     */
    public function getPersonMedical(): ?PersonMedical
    {
        return $this->personMedical;
    }

    /**
     * @param PersonMedical|null $personMedical
     * @return PersonMedicalCondition
     */
    public function setPersonMedical(?PersonMedical $personMedical): PersonMedicalCondition
    {
        $this->personMedical = $personMedical;
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
     * @return PersonMedicalCondition
     */
    public function setName(?string $name): PersonMedicalCondition
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return AlertLevel|null
     */
    public function getAlertLevel(): ?AlertLevel
    {
        return $this->alertLevel;
    }

    /**
     * @param AlertLevel|null $alertLevel
     * @return PersonMedicalCondition
     */
    public function setAlertLevel(?AlertLevel $alertLevel): PersonMedicalCondition
    {
        $this->alertLevel = $alertLevel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTriggers(): ?string
    {
        return $this->triggers;
    }

    /**
     * @param string|null $triggers
     * @return PersonMedicalCondition
     */
    public function setTriggers(?string $triggers): PersonMedicalCondition
    {
        $this->triggers = $triggers;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReaction(): ?string
    {
        return $this->reaction;
    }

    /**
     * @param string|null $reaction
     * @return PersonMedicalCondition
     */
    public function setReaction(?string $reaction): PersonMedicalCondition
    {
        $this->reaction = $reaction;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param string|null $response
     * @return PersonMedicalCondition
     */
    public function setResponse(?string $response): PersonMedicalCondition
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMedication(): ?string
    {
        return $this->medication;
    }

    /**
     * @param string|null $medication
     * @return PersonMedicalCondition
     */
    public function setMedication(?string $medication): PersonMedicalCondition
    {
        $this->medication = $medication;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastEpisode(): ?\DateTime
    {
        return $this->lastEpisode;
    }

    /**
     * @param \DateTime|null $lastEpisode
     * @return PersonMedicalCondition
     */
    public function setLastEpisode(?\DateTime $lastEpisode): PersonMedicalCondition
    {
        $this->lastEpisode = $lastEpisode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastEpisodeTreatment(): ?string
    {
        return $this->lastEpisodeTreatment;
    }

    /**
     * @param string|null $lastEpisodeTreatment
     * @return PersonMedicalCondition
     */
    public function setLastEpisodeTreatment(?string $lastEpisodeTreatment): PersonMedicalCondition
    {
        $this->lastEpisodeTreatment = $lastEpisodeTreatment;
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
     * @return PersonMedicalCondition
     */
    public function setComment(?string $comment): PersonMedicalCondition
    {
        $this->comment = $comment;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * create
     * @return array|string[]
     * 22/06/2020 10:55
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__PersonMedicalCondition` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person_medical` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `alert_level` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(100) NOT NULL,
                    `triggers` varchar(191) DEFAULT NULL,
                    `reaction` varchar(191) DEFAULT NULL,
                    `response` varchar(191) DEFAULT NULL,
                    `medication` varchar(191) DEFAULT NULL,
                    `last_episode` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `last_episode_treatment` varchar(191) DEFAULT NULL,
                    `comment` longtext,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `person_condition_level_trigger` (`person_medical`,`name`,`alert_level`,`triggers`),
                    KEY `person_medical` (`person_medical`),
                    KEY `alert_level` (`alert_level`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 22/06/2020 10:56
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__PersonMedicalCondition`
                    ADD CONSTRAINT FOREIGN KEY (`person_medical`) REFERENCES `__prefix__PersonMedical` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`alert_level`) REFERENCES `__prefix__AlertLevel` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 22/06/2020 10:56
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}

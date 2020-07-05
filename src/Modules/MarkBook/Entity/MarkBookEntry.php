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
namespace App\Modules\MarkBook\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use App\Modules\People\Util\UserHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MarkBookEntry
 * @package App\Modules\MarkBook\Entity
 * @ORM\Entity(repositoryClass="App\Modules\MarkBook\Repository\MarkBookEntryRepository")
 * @ORM\Table(name="MarkBookEntry", 
 *     indexes={@ORM\Index(name="student", columns={"student"}), 
 *     @ORM\Index(name="mark_book_column", columns={"mark_book_column"}),
 *     @ORM\Index(name="modifier", columns={"modifier"})}, 
 *     uniqueConstraints={@ORM\UniqueConstraint(name="mark_book_column_student", columns={"mark_book_column","student"})})
 * @UniqueEntity({"markBookColumn","student"})
 * @ORM\HasLifecycleCallbacks()
 */
class MarkBookEntry extends AbstractEntity
{
    CONST VERSION = '1.0.00';
    
    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var MarkBookColumn|null
     * @ORM\ManyToOne(targetEntity="MarkBookColumn")
     * @ORM\JoinColumn(name="mark_book_column",referencedColumnName="id")
     */
    private $markBookColumn;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="student",referencedColumnName="id")
     */
    private $student;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $modifiedAssessment = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=10,nullable=true)
     */
    private $attainmentValue;

    /**
     * @var string|null
     * @ORM\Column(length=10,nullable=true)
     */
    private $attainmentValueRaw;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $attainmentDescriptor;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"comment": "'P' denotes that student has exceed their personal target","default": "N"})
     * @Assert\Choice(callback="getAttainmentConcernList")
     */
    private $attainmentConcern = 'N';

    /**
     * @var array 
     */
    private static $attainmentConcernList = ['N', 'Y', 'P'];

    /**
     * @var string|null
     * @ORM\Column(length=10,nullable=true)
     */
    private $effortValue;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $effortDescriptor;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $effortConcern = 'N';

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $response;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="modifier",referencedColumnName="id")
     */
    private $modifier;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return MarkBookEntry
     */
    public function setId(?string $id): MarkBookEntry
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return MarkBookColumn|null
     */
    public function getMarkBookColumn(): ?MarkBookColumn
    {
        return $this->markBookColumn;
    }

    /**
     * @param MarkBookColumn|null $markBookColumn
     * @return MarkBookEntry
     */
    public function setMarkBookColumn(?MarkBookColumn $markBookColumn): MarkBookEntry
    {
        $this->markBookColumn = $markBookColumn;
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
     * @return MarkBookEntry
     */
    public function setStudent(?Person $student): MarkBookEntry
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModifiedAssessment(): ?string
    {
        return $this->modifiedAssessment;
    }

    /**
     * @param string|null $modifiedAssessment
     * @return MarkBookEntry
     */
    public function setModifiedAssessment(?string $modifiedAssessment): MarkBookEntry
    {
        $this->modifiedAssessment = self::checkBoolean($modifiedAssessment, 'N');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttainmentValue(): ?string
    {
        return $this->attainmentValue;
    }

    /**
     * @param string|null $attainmentValue
     * @return MarkBookEntry
     */
    public function setAttainmentValue(?string $attainmentValue): MarkBookEntry
    {
        $this->attainmentValue = $attainmentValue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttainmentValueRaw(): ?string
    {
        return $this->attainmentValueRaw;
    }

    /**
     * @param string|null $attainmentValueRaw
     * @return MarkBookEntry
     */
    public function setAttainmentValueRaw(?string $attainmentValueRaw): MarkBookEntry
    {
        $this->attainmentValueRaw = $attainmentValueRaw;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttainmentDescriptor(): ?string
    {
        return $this->attainmentDescriptor;
    }

    /**
     * @param string|null $attainmentDescriptor
     * @return MarkBookEntry
     */
    public function setAttainmentDescriptor(?string $attainmentDescriptor): MarkBookEntry
    {
        $this->attainmentDescriptor = $attainmentDescriptor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttainmentConcern(): ?string
    {
        return $this->attainmentConcern;
    }

    /**
     * @param string|null $attainmentConcern
     * @return MarkBookEntry
     */
    public function setAttainmentConcern(?string $attainmentConcern): MarkBookEntry
    {
        $this->attainmentConcern = in_array($attainmentConcern, self::getAttainmentConcernList()) ? $attainmentConcern : 'N' ;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEffortValue(): ?string
    {
        return $this->effortValue;
    }

    /**
     * @param string|null $effortValue
     * @return MarkBookEntry
     */
    public function setEffortValue(?string $effortValue): MarkBookEntry
    {
        $this->effortValue = $effortValue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEffortDescriptor(): ?string
    {
        return $this->effortDescriptor;
    }

    /**
     * @param string|null $effortDescriptor
     * @return MarkBookEntry
     */
    public function setEffortDescriptor(?string $effortDescriptor): MarkBookEntry
    {
        $this->effortDescriptor = $effortDescriptor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEffortConcern(): ?string
    {
        return $this->effortConcern;
    }

    /**
     * @param string|null $effortConcern
     * @return MarkBookEntry
     */
    public function setEffortConcern(?string $effortConcern): MarkBookEntry
    {
        $this->effortConcern = self::checkBoolean($effortConcern, 'N');
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
     * @return MarkBookEntry
     */
    public function setComment(?string $comment): MarkBookEntry
    {
        $this->comment = $comment;
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
     * @return MarkBookEntry
     */
    public function setResponse(?string $response): MarkBookEntry
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getModifier(): ?Person
    {
        return $this->modifier;
    }

    /**
     * @param Person|null $modifier
     * @return MarkBookEntry
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setModifier(?Person $modifier): MarkBookEntry
    {
        if (null === $modifier)
            $modifier = UserHelper::getCurrentUser();
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @return array
     */
    public static function getAttainmentConcernList(): array
    {
        return self::$attainmentConcernList;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * create
     * @return array|string[]
     * 21/06/2020 09:23
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__MarkBookEntry` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `mark_book_column` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `student` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `modifier` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `comment` longtext,
                    `response` varchar(191) DEFAULT NULL,
                    `modified_assessment` varchar(1) NOT NULL DEFAULT 'N',
                    `attainment_value` varchar(10) DEFAULT NULL,
                    `attainment_value_raw` varchar(10) DEFAULT NULL,
                    `attainment_descriptor` varchar(100) DEFAULT NULL,
                    `attainment_concern` varchar(1) NOT NULL DEFAULT 'N' COMMENT '''P'' denotes that student has exceed their personal target',
                    `effort_value` varchar(10) DEFAULT NULL,
                    `effort_descriptor` varchar(100) DEFAULT NULL,
                    `effort_concern` varchar(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `mark_book_column_student` (`mark_book_column`,`student`),
                    KEY `student` (`student`),
                    KEY `mark_book_column` (`mark_book_column`),
                    KEY `modifier` (`modifier`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 21/06/2020 09:23
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__MarkBookEntry`
                    ADD CONSTRAINT FOREIGN KEY (`modifier`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`mark_book_column`) REFERENCES `__prefix__MarkBookColumn` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`student`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 21/06/2020 09:23
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
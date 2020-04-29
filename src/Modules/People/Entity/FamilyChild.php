<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\People\Entity;

use App\Manager\EntityInterface;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\People\Util\StudentHelper;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyChild
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyChildRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="FamilyChild",
 *     indexes={@ORM\Index(name="family", columns={"family"}),@ORM\Index(name="person", columns={"person"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="family_member", columns={"family","person"})})
 * @UniqueEntity(fields={"family","person"}, errorPath="person")
 */
class FamilyChild implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer",columnDefinition="INT(8) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Family|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Family")
     * @ORM\JoinColumn(name="family",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $family;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="children")
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * @var Collection|FamilyRelationship[]
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\FamilyRelationship",mappedBy="child",orphanRemoval=true)
     */
    private $relationships;

    /**
     * FamilyChild constructor.
     * @param Family|null $family
     */
    public function __construct(?Family $family = null)
    {
        $this->family = $family;
        $this->relationships = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return FamilyChild
     */
    public function setId(?int $id): FamilyChild
    {
        $this->id = $id;
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
     * @return FamilyChild
     */
    public function setFamily(?Family $family): FamilyChild
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return FamilyChild
     */
    public function setPerson(?Person $person): FamilyChild
    {
        $this->person = $person;
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
     * @return FamilyChild
     */
    public function setComment(?string $comment): FamilyChild
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return Collection|FamilyRelationship[]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Relationships.
     *
     * @param Collection|FamilyRelationship[] $relationships
     * @return FamilyChild
     */
    public function setRelationships($relationships)
    {
        $this->relationships = $relationships;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        if ($this->getFamily() && $this->getPerson())
            return $this->getFamily()->getName() . ': ' . $this->getPerson()->formatName();
        if ($this->getFamily())
            return $this->getFamily()->getName() . ': UunKnown ' . $this->getId();
        if ($this->getPerson())
            return 'Unknown : ' . $this->getPerson()->formatName() . ' ' . $this->getId();
        return 'No Idea';
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        $person = $this->getPerson();

        return [
            'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getImage240()),
            'fullName' => $person->formatName(['style' => 'long', 'preferredName' => false]),
            'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
            'roll' => StudentHelper::getCurrentRollGroup($person),
            'comment' => $this->getComment(),
            'family_id' => $this->getFamily()->getId(),
            'child_id' => $this->getId(),
            'person_id' => $this->getPerson()->getId(),
            'id' => $this->getId(),
        ];
    }

    /**
     * isEqualTo
     * @param FamilyAdult $adult
     * @return bool
     */
    public function isEqualTo(FamilyChild $child): bool
    {
        if($this->getPerson() === null || $child->getPerson() === null || $this->getFamily() === null || $child->getFamily() === null)
            return false;
        if (!$child->getPerson()->isEqualTo($this->getPerson()))
            return false;
        if (!$child->getFamily()->isEqualTo($this->getFamily()))
            return false;
        return true;
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__FamilyChild` (
                    `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `comment` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `family` int(7) UNSIGNED DEFAULT NULL,
                    `person` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `family_member` (`family`,`person`),
                    KEY `family` (`family`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FamilyChild`
                    ADD CONSTRAINT FOREIGN KEY (`family`) REFERENCES `__prefix__Family` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return "";
    }
}
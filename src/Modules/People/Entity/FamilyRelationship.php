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

use App\Manager\AbstractEntity;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyRelationship
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyRelationshipRepository")
 * @ORM\Table(name="FamilyRelationship",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="FamilyMemberAdultChild", columns={"family","adult","child"})})
 * @UniqueEntity({"family","adult","child"})
 */
class FamilyRelationship extends AbstractEntity
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
     * @var Family|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Family")
     * @ORM\JoinColumn(name="family", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $family;

    /**
     * @var FamilyMemberAdult|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\FamilyMemberAdult",inversedBy="relationships")
     * @ORM\JoinColumn(name="adult",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $adult;

    /**
     * @var FamilyMemberChild|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\FamilyMemberChild",inversedBy="relationships")
     * @ORM\JoinColumn(name="child",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $child;

    /**
     * @var string|null
     * @ORM\Column(length=31,nullable=true)
     * @Assert\Choice(callback="getRelationshipList")
     */
    private $relationship;

    /**
     * @var array
     */
    private static $relationshipList = [
        'Mother',
        'Father',
        'Step-Mother',
        'Step-Father',
        'Adoptive Parent',
        'Guardian',
        'Grandmother',
        'Grandfather',
        'Aunt',
        'Uncle',
        'Nanny/Helper',
        'Other',
    ];

    /**
     * FamilyRelationship constructor.
     * @param Family|null $family
     * @param FamilyMember|null $adult
     * @param FamilyMember|null $child
     */
    public function __construct(?Family $family = null, ?FamilyMember $adult = null, ?FamilyMember $child = null)
    {
        $this->family = $family;
        $this->adult = $adult;
        $this->child = $child;
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
     * @return FamilyRelationship
     */
    public function setId(?string $id): FamilyRelationship
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
     * @return FamilyRelationship
     */
    public function setFamily(?Family $family): FamilyRelationship
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return FamilyMemberAdult|null
     */
    public function getAdult(): ?FamilyMemberAdult
    {
        return $this->adult;
    }

    /**
     * Adult.
     *
     * @param FamilyMemberAdult|null $adult
     * @return FamilyRelationship
     */
    public function setAdult(?FamilyMemberAdult $adult): FamilyRelationship
    {
        $this->adult = $adult;
        return $this;
    }

    /**
     * @return FamilyMemberChild|null
     */
    public function getChild(): ?FamilyMemberChild
    {
        return $this->child;
    }

    /**
     * Child.
     *
     * @param FamilyMemberChild|null $child
     * @return FamilyRelationship
     */
    public function setChild(?FamilyMemberChild $child): FamilyRelationship
    {
        $this->child = $child;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    /**
     * @param string|null $relationship
     * @return FamilyRelationship
     */
    public function setRelationship(?string $relationship): FamilyRelationship
    {
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * @return array
     */
    public static function getRelationshipList(): array
    {
        return self::$relationshipList;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFamily()->__toString() . ': ' . $this->getAdult()->getPerson()->formatName(['style' => 'formal']) . ' is ' . $this->getRelationship() . ' of ' . $this->getChild()->getPerson()->formatName(['style' => 'long']);
    }

    /**
     * isEqualTo
     * @param FamilyRelationship $relationship
     * @return bool
     */
    public function isEqualTo(FamilyRelationship $relationship): bool
    {
        if (!$relationship->getFamily()->isEqualTo($this->getFamily()))
            return false;
        if (!$relationship->getAdult()->isEqualTo($this->getAdult()))
            return false;
        if (!$relationship->getChild()->isEqualTo($this->getChild()))
            return false;
        return true;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'form')
        {
            TranslationHelper::setDomain('People');
            dump($this);
            return [
                'parent' => TranslationHelper::translate('{name} is the', ['{name}' => $this->getAdult()->getPerson()->formatName(['style'=> 'formal'])]),
                'child' => TranslationHelper::translate('of {name}', ['{name}' => $this->getChild()->getPerson()->formatName(['title' => false, 'preferredName' => false])]),
            ];
        }
        return [];
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__FamilyRelationship` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `relationship` CHAR(50) NOT NULL,
                    `family` CHAR(36) DEFAULT NULL,
                    `adult` CHAR(36) DEFAULT NULL,
                    `child` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `family` (`family`),
                    KEY `adult` (`adult`),
                    KEY `student` (`child`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FamilyRelationship`
                    ADD CONSTRAINT FOREIGN KEY (`family`) REFERENCES `__prefix__Family` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`adult`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`child`) REFERENCES `__prefix__Person` (`id`);";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

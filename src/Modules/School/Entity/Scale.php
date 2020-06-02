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
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Manager\Traits\BooleanList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Scale
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\ScaleRepository")
 * @ORM\Table(name="Scale",
 *     indexes={@ORM\Index(name="lowest_acceptable",columns={"lowest_acceptable"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation",columns={"abbreviation"}),
 * })
 * @UniqueEntity(fields={"name"})
 * @UniqueEntity(fields={"abbreviation"})
 */
class Scale extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=40)
     * @Assert\NotBlank()
     * @Assert\Length(max=40)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=5, name="abbreviation")
     * @Assert\NotBlank()
     * @Assert\Length(max=5)
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(length=50,)
     * @Assert\Length(max=50)
     */
    private $usageInfo;

    /**
     * @var ScaleGrade|null
     * @ORM\OneToOne(targetEntity="ScaleGrade")
     * @ORM\JoinColumn(name="lowest_acceptable", referencedColumnName="id", nullable=true)
     */
    private $lowestAcceptable;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $numericOnly = 'N';

    /**
     * @var Collection|ScaleGrade[]|null
     * @ORM\OneToMany(targetEntity="ScaleGrade", mappedBy="scale")
     */
    private $scaleGrades;

    /**
     * Scale constructor.
     */
    public function __construct()
    {
        $this->scaleGrades = new ArrayCollection();
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
     * @return Scale
     */
    public function setId(?string $id): Scale
    {
        $this->id = $id;
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
     * @return Scale
     */
    public function setName(?string $name): Scale
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @param string|null $abbreviation
     * @return Scale
     */
    public function setAbbreviation(?string $abbreviation): Scale
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsageInfo(): ?string
    {
        return $this->usageInfo;
    }

    /**
     * @param string|null $usageInfo
     * @return Scale
     */
    public function setUsageInfo(?string $usageInfo): Scale
    {
        $this->usageInfo = $usageInfo;
        return $this;
    }

    /**
     * @return ScaleGrade|null
     */
    public function getLowestAcceptable(): ?ScaleGrade
    {
        return $this->lowestAcceptable;
    }

    /**
     * LowestAcceptable.
     *
     * @param ScaleGrade|null $lowestAcceptable
     * @return Scale
     */
    public function setLowestAcceptable(?ScaleGrade $lowestAcceptable): Scale
    {
        $this->lowestAcceptable = $lowestAcceptable;
        return $this;
    }

    /**
     * isActive
     * @return bool
     */
    public function isActive(): bool
    {
        return$this->getActive() === 'Y';
    }

    /**
     * getActive
     * @return string
     */
    public function getActive(): string
    {
        return self::checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return Scale
     */
    public function setActive(?string $active): Scale
    {
        $this->active = self::checkBoolean($active);
        return $this;
    }

    /**
     * isNumericOnly
     * @return bool
     */
    public function isNumericOnly(): bool
    {
        return $this->getNumericOnly() === 'Y';
    }

    /**
     * getNumericOnly
     * @return string
     */
    public function getNumericOnly(): string
    {
        return self::checkBoolean($this->numericOnly);
    }

    /**
     * @param string|null $numericOnly
     * @return Scale
     */
    public function setNumericOnly(?string $numericOnly): Scale
    {
        $this->numericOnly = self::checkBoolean($numericOnly, 'N');
        return $this;
    }

    /**
     * @return ScaleGrade[]|Collection|null
     */
    public function getScaleGrades()
    {
        if (null === $this->scaleGrades) {
            $this->scaleGrades = new ArrayCollection();
        }

        if ($this->scaleGrades instanceof PersistentCollection) {
            $this->scaleGrades->initialize();
        }

        return $this->scaleGrades;
    }

    /**
     * @param ScaleGrade[]|Collection|null $scaleGrades
     * @return Scale
     */
    public function setScaleGrades(?Collection $scaleGrades)
    {
        $this->scaleGrades = $scaleGrades;
        return $this;
    }

    /**
     * getLastGradeSequence
     * @return int
     * 2/06/2020 10:02
     */
    public function getLastGradeSequence(): int
    {
        return $this->getScaleGrades()->count();
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('. $this->getAbbreviation() .')';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'usage' => $this->getUsageInfo(),
            'abbr' => $this->getAbbreviation(),
            'isActive' => $this->isActive(),
            'active' => $this->isActive() ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages'),
            'numeric' => $this->isNumericOnly() ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages'),
            'canDelete' => ProviderFactory::create(Scale::class)->canDelete($this),
        ];
    }

    public function getScaleId(): ?int
    {
        return $this->getId();
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Scale` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(40) NOT NULL,
                    `abbreviation` CHAR(5) NOT NULL,
                    `usage_info` CHAR(50) NOT NULL,
                    `lowest_acceptable` CHAR(36) DEFAULT NULL,
                    `active` CHAR(1) NOT NULL DEFAULT 'Y',
                    `numeric_only` CHAR(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `abbreviation` (`abbreviation`),
                    KEY `lowestAcceptable` (`lowest_acceptable`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Scale`
                    ADD CONSTRAINT FOREIGN KEY (`lowest_acceptable`) REFERENCES `__prefix__ScaleGrade` (`id`);';
    }

    public function coreData(): array
    {
        return Yaml::parse("
-
  name: 'International Baccalaureate'
  abbreviation: 'IB'
  usageInfo: '7 (highest) to 1 (lowest)'
  active: 'Y'
  numericOnly: 'Y'
-
  name: 'International Baccalaureate EE'
  abbreviation: 'IBEE'
  usageInfo: 'A (highest) to E (lowest)'
  active: 'Y'
  numericOnly: 'N'
-
  name: 'United Kingdom GCSE/iGCSE'
  abbreviation: 'GCSE'
  usageInfo: 'A* (highest) to U (lowest)'
  active: 'Y'
  numericOnly: 'N'
-
  name: 'Percentage'
  abbreviation: '%'
  usageInfo: '100 (highest) to  (lowest)'
  active: 'Y'
  numericOnly: 'Y'
-
  name: 'Full Letter Grade'
  abbreviation: 'FLG'
  usageInfo: 'A+ (highest) to F (lowest)'
  active: 'N'
  numericOnly: 'N'
-
  name: 'Simple Letter Grade'
  abbreviation: 'SLG'
  usageInfo: 'A (highest) to F (lowest)'
  active: 'N'
  numericOnly: 'N'
-
  name: 'International College HK'
  abbreviation: 'ICHK'
  usageInfo: '7 (highest) to 1 (lowest)'
  active: 'Y'
  numericOnly: 'Y'
-
  name: 'Completion'
  abbreviation: 'Comp'
  usageInfo: 'Has task has been completed?'
  active: 'Y'
  numericOnly: 'N'
-
  name: 'Cognitive Abilities Test'
  abbreviation: 'CAT'
  usageInfo: '140 (highest) to 60 (lowest)'
  active: 'Y'
  numericOnly: 'Y'
-
  name: 'UK National Curriculum KS3'
  abbreviation: 'KS3'
  usageInfo: '8A (highest) to B3 (lowest)'
  active: 'Y'
  numericOnly: 'N'
-
  name: 'United Kingdom GCSE/iGCSE Predicted'
  abbreviation: 'GPrd'
  usageInfo: '8A (highest) to B3 (lowest)'
  active: 'Y'
  numericOnly: 'N'
-
  name: 'IB Diploma (Subject)'
  abbreviation: 'IBDS'
  usageInfo: '7 (highest) to 1 (lowest)'
  active: 'Y'
  numericOnly: 'Y'
-
  name: 'IB Diploma (Total)'
  abbreviation: 'IBDT'
  usageInfo: '45 (highest) to '
  active: 'Y'
  numericOnly: 'Y'
-
  name: 'UK National Curriculum KS3 Simplified'
  abbreviation: 'KS3S'
  usageInfo: 'Level 8 (highest) to Level 3 (lowest)'
  active: 'Y'
  numericOnly: 'N'
");
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * coreDataLinks
     * @return mixed
     */
    public function coreDataLinks()
    {
        return Yaml::parse("
-
    findBy: 
        abbreviation: 'IBEE'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: E }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: '%'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: '50%' }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: 'FLG'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 'E-' }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: 'SLG'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 'D' }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: 'ICHK'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 4 }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: 'Comp'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 'Complete' }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: 'CAT'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 101 }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: 'KS3'
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: '4C' }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: GPrd
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 'F' }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: IBDS
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 3 }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: IBDT
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 23 }
    target: lowestAcceptable
-
    findBy: 
        abbreviation: KS3S
    source: 
        table: App\Modules\School\Entity\ScaleGrade
        findBy: { scale: use_target_entity, value: 4 }
    target: lowestAcceptable
");
    }
}

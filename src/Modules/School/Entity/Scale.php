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

use App\Manager\EntityInterface;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Manager\Traits\BooleanList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Scale
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\ScaleRepository")
 * @ORM\Table(name="Scale",
 *     indexes={@ORM\Index(name="lowestAcceptable",columns={"lowest_acceptable"})})
 */
class Scale implements EntityInterface
{
    CONST VERSION = '20200401';

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
     * @ORM\Column(length=50,name="usage_info")
     * @Assert\Length(max=50)
     */
    private $usage;

    /**
     * @var ScaleGrade|null
     * @ORM\OneToOne(targetEntity="App\Modules\School\Entity\ScaleGrade")
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
     * @ORM\Column(length=1, options={"default": "N"},name="is_numeric")
     * @Assert\Choice(callback="getBooleanList")
     */
    private $numeric = 'N';

    /**
     * @var ScaleGrade|null
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
    public function getUsage(): ?string
    {
        return $this->usage;
    }

    /**
     * @param string|null $usage
     * @return Scale
     */
    public function setUsage(?string $usage): Scale
    {
        $this->usage = $usage;
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
     * isNumeric
     * @return bool
     */
    public function isNumeric(): bool
    {
        return $this->getNumeric() === 'Y';
    }

    /**
     * getNumeric
     * @return string
     */
    public function getNumeric(): string
    {
        return self::checkBoolean($this->numeric);
    }

    /**
     * @param string|null $numeric
     * @return Scale
     */
    public function setNumeric(?string $numeric): Scale
    {
        $this->numeric = self::checkBoolean($numeric, 'N');
        return $this;
    }

    /**
     * @return Scale|null
     */
    public function getScaleGrades(): ?Scale
    {
        return $this->scaleGrades;
    }

    /**
     * ScaleGrades.
     *
     * @param Scale|null $scaleGrades
     * @return Scale
     */
    public function setScaleGrades(?Scale $scaleGrades): Scale
    {
        $this->scaleGrades = $scaleGrades;
        return $this;
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
            'usage' => $this->getUsage(),
            'abbr' => $this->getAbbreviation(),
            'isActive' => $this->isActive(),
            'active' => $this->isActive() ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages'),
            'numeric' => $this->isNumeric() ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages'),
            'canDelete' => ProviderFactory::create(Scale::class)->canDelete($this),
        ];
    }

    public function getScaleId(): ?int
    {
        return $this->getId();
    }

    public function create(): string
    {
        return "CREATE TABLE `__prefix__Scale` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(40) NOT NULL,
                    `abbreviation` CHAR(5) NOT NULL,
                    `usage_info` CHAR(50) NOT NULL,
                    `lowest_acceptable` CHAR(36) DEFAULT NULL,
                    `active` CHAR(1) NOT NULL DEFAULT 'Y',
                    `is_numeric` CHAR(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`id`),
                    KEY `lowestAcceptable` (`lowest_acceptable`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Scale`
                    ADD CONSTRAINT FOREIGN KEY (`lowest_acceptable`) REFERENCES `__prefix__ScaleGrade` (`id`);';
    }

    public function coreData(): string
    {
        return "INSERT INTO `__prefix__Scale` (`name`, `abbreviation`, `usage_info`, `lowest_acceptable`, `active`, `is_numeric`) VALUES
('International Baccalaureate', 'IB', '7 (highest) to 1 (lowest)', NULL, 'Y', 'Y'),
('International Baccalaureate EE', 'IBEE', 'A (highest) to E (lowest)', NULL, 'Y', 'N'),
('United Kingdom GCSE/iGCSE', 'GCSE', 'A* (highest) to U (lowest)', NULL, 'Y', 'N'),
('Percentage', '%', '100 (highest) to  (lowest)', NULL, 'Y', 'Y'),
('Full Letter Grade', 'FLG', 'A+ (highest) to F (lowest)', NULL, 'N', 'N'),
('Simple Letter Grade', 'SLG', 'A (highest) to F (lowest)', NULL, 'N', 'N'),
('International College HK', 'ICHK', '7 (highest) to 1 (lowest)', NULL, 'Y', 'Y'),
('Completion', 'Comp', 'Has task has been completed?', NULL, 'Y', 'N'),
('Cognitive Abilities Test', 'CAT', '140 (highest) to 60 (lowest)', NULL, 'Y', 'Y'),
('UK National Curriculum KS3', 'KS3', '8A (highest) to B3 (lowest)',  NULL, 'Y', 'N'),
('United Kingdom GCSE/iGCSE Predicted', 'GPrd', '8A (highest) to B3 (lowest)', NULL, 'Y', 'N'),
('IB Diploma (Subject)', 'IBDS', '7 (highest) to 1 (lowest)', NULL, 'Y', 'Y'),
('IB Diploma (Total)', 'IBDT', '45 (highest) to ', NULL, 'Y', 'Y'),
('UK National Curriculum KS3 Simplified', 'KS3S', 'Level 8 (highest) to Level 3 (lowest)', NULL, 'Y', 'N');";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

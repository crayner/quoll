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
 * @ORM\Table(options={"auto_increment": 1}, name="Scale",
 *     indexes={@ORM\Index(name="lowestAcceptable",columns={"lowest_acceptable"})})
 */
class Scale implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="smallint", columnDefinition="INT(5) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @ORM\Column(length=5, name="nameShort")
     * @Assert\NotBlank()
     * @Assert\Length(max=5)
     */
    private $nameShort;

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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Scale
     */
    public function setId(?int $id): Scale
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
    public function getNameShort(): ?string
    {
        return $this->nameShort;
    }

    /**
     * @param string|null $nameShort
     * @return Scale
     */
    public function setNameShort(?string $nameShort): Scale
    {
        $this->nameShort = $nameShort;
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
        return $this->getName() . ' ('. $this->getNameShort() .')';
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
            'abbr' => $this->getNameShort(),
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
        return 'CREATE TABLE `__prefix__Scale` (
                    `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameShort` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `usage_info` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `lowest_acceptable` int(7) UNSIGNED DEFAULT NULL,
                    `active` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
                    `is_numeric` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'N\',
                    PRIMARY KEY (`id`),
                    KEY `lowestAcceptable` (`lowest_acceptable`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Scale`
                    ADD CONSTRAINT FOREIGN KEY (`lowest_acceptable`) REFERENCES `__prefix__ScaleGrade` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return 'INSERT INTO `__prefix__Scale` (`id`, `name`, `nameShort`, `usage_info`, `lowest_acceptable`, `active`, `is_numeric`) VALUES
(1, \'International Baccalaureate\', \'IB\', \'7 (highest) to 1 (lowest)\', NULL, \'Y\', \'Y\'),
(2, \'International Baccalaureate EE\', \'IBEE\', \'A (highest) to E (lowest)\', 12, \'Y\', \'N\'),
(3, \'United Kingdom GCSE/iGCSE\', \'GCSE\', \'A* (highest) to U (lowest)\', NULL, \'Y\', \'N\'),
(4, \'Percentage\', \'%\', \'100 (highest) to  (lowest)\', 72, \'Y\', \'Y\'),
(5, \'Full Letter Grade\', \'FLG\', \'A+ (highest) to F (lowest)\', 137, \'N\', \'N\'),
(6, \'Simple Letter Grade\', \'SLG\', \'A (highest) to F (lowest)\', 142, \'N\', \'N\'),
(7, \'International College HK\', \'ICHK\', \'7 (highest) to 1 (lowest)\', 148, \'Y\', \'Y\'),
(8, \'Completion\', \'Comp\', \'Has task has been completed?\', 152, \'Y\', \'N\'),
(9, \'Cognitive Abilities Test\', \'CAT\', \'140 (highest) to 60 (lowest)\', 202, \'Y\', \'Y\'),
(10, \'UK National Curriculum KS3\', \'KS3\', \'8A (highest) to B3 (lowest)\', 256, \'Y\', \'N\'),
(11, \'United Kingdom GCSE/iGCSE Predicted\', \'GPrd\', \'8A (highest) to B3 (lowest)\', 268, \'Y\', \'N\'),
(12, \'IB Diploma (Subject)\', \'IBDS\', \'7 (highest) to 1 (lowest)\', 276, \'Y\', \'Y\'),
(13, \'IB Diploma (Total)\', \'IBDT\', \'45 (highest) to \', 301, \'Y\', \'Y\'),
(14, \'UK National Curriculum KS3 Simplified\', \'KS3S\', \'Level 8 (highest) to Level 3 (lowest)\', 328, \'Y\', \'N\');';
    }
}
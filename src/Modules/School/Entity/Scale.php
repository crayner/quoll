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
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Provider\ProviderFactory;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
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
 * @UniqueEntity("name")
 * @UniqueEntity("abbreviation")
 */
class Scale extends AbstractEntity
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
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $active = true;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $numericOnly = false;

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
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool|null $active
     * @return Scale
     */
    public function setActive(?bool $active): Scale
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isNumericOnly(): bool
    {
        return (bool)$this->numericOnly;
    }

    /**
     * @param bool|null $numericOnly
     * @return Scale
     */
    public function setNumericOnly(?bool $numericOnly): Scale
    {
        $this->numericOnly = (bool)$numericOnly;
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
            'active' => StringHelper::getYesNo($this->isActive()),
            'numeric' => StringHelper::getYesNo($this->isNumericOnly()),
            'canDelete' => ProviderFactory::create(Scale::class)->canDelete($this),
        ];
    }

    /**
     * coreData
     * @return array
     * 5/07/2020 08:48
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/ScaleCoreData.yaml'));
    }
}

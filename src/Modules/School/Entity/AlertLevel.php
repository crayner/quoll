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
use App\Validator AS Validator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AlertLevel
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\AlertLevelRepository")
 * @ORM\Table(name="AlertLevel")
 */
class AlertLevel extends AbstractEntity
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
     * @ORM\Column(length=50)
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="colour", options={"comment": "RGB Hex"})
     * @Validator\Colour(enforceType="hex")
     */
    private $colour;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="colour_bg", options={"comment": "RGB Hex"})
     * @Validator\Colour(enforceType="hex")
     */
    private $colourBG;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1,max=999)
     */
    private $priority;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return AlertLevel
     */
    public function setId(?string $id): AlertLevel
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
     * @return AlertLevel
     */
    public function setName(?string $name): AlertLevel
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
     * @return AlertLevel
     */
    public function setAbbreviation(?string $abbreviation): AlertLevel
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColour(): ?string
    {
        if (!strpos($this->colour, '#') === 0 && strlen($this->colour) > 0)
            $this->colour = '#' . $this->colour;
        return $this->colour;
    }

    /**
     * @param string|null $colour
     * @return AlertLevel
     */
    public function setColour(?string $colour): AlertLevel
    {
        $this->colour = $colour;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColourBG(): ?string
    {
        if (!strpos($this->colourBG, '#') === 0 && strlen($this->colourBG) > 0)
            $this->colourBG = '#' . $this->colourBG;
        return $this->colourBG;
    }

    /**
     * @param string|null $colourBG
     * @return AlertLevel
     */
    public function setColourBG(?string $colourBG): AlertLevel
    {
        $this->colourBG = $colourBG;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return AlertLevel
     */
    public function setDescription(?string $description): AlertLevel
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return AlertLevel
     */
    public function setPriority(int $priority): AlertLevel
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 9/06/2020 15:03
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array
     * 9/06/2020 15:03
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__AlertLevel` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `name` VARCHAR(50) NOT NULL, 
                    `abbreviation` VARCHAR(4) NOT NULL, 
                    `colour` VARCHAR(7) NOT NULL COMMENT 'RGB Hex', 
                    `colour_bg` VARCHAR(7) NOT NULL COMMENT 'RGB Hex', 
                    `description` LONGTEXT NOT NULL, 
                    `priority` SMALLINT NOT NULL, 
                    PRIMARY KEY(`id`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 9/06/2020 15:03
     */
    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * getVersion
     * @return string
     * 9/06/2020 14:52
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * coreData
     * @return array
     * 9/06/2020 15:08
     */
    public function coreData(): array
    {
        return Yaml::parse("
-
  name: 'High'
  nameShort: 'H'
  colour: '#8b0000'
  colour_bg: '#FFB6C1'
  description: 'Highest level of severity, requiring intense and immediate readiness, action, individual support or differentiation.'
  sortOrder: 3
-
  name: 'Medium'
  nameShort: 'M'
  colour: '#e69500'
  colour_bg: '#FFDB99'
  description: 'Moderate severity, requiring intermediate level of readiness, action, individual support or differentiation.'
  sortOrder: 2
-
  name: 'Low'
  nameShort: 'L'
  colour: '#d0d000'
  colour_bg: '#ffffad'
  description: 'Low severity, requiring little to no readiness, action, individual support or differentiation.'
  sortOrder: 1
");
    }
}

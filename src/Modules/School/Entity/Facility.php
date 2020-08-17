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
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Facility
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\FacilityRepository")
 * @ORM\Table(name="Facility",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @UniqueEntity({"name"})
 */
class Facility extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id = null;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max="30")
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     * @Assert\Choice(callback="getTypeList")
     * @Assert\NotBlank()
     */
    private ?string $type;

    /**
     * @var int|null
     * @ORM\Column(type="smallint",nullable=true)
     * @Assert\Range(min=0,max=99999)
     */
    private ?int $capacity;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $computer = false;

    /**
     * @var int
     * @ORM\Column(type="smallint",options={"default": 0})
     * @Assert\Range(min=0,max=999)
     */
    private int $studentComputers = 0;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $projector = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $tv = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $dvd = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $hifi = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $speakers = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $iwb = false;

    /**
     * @var string|null
     * @ORM\Column(length=5,nullable=true)
     */
    private ?string $phoneInt;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private ?string $phoneExt;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $comment;

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
     * @return Facility
     */
    public function setId(?string $id): Facility
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
     * @return Facility
     */
    public function setName(?string $name): Facility
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Facility
     */
    public function setType(?string $type): Facility
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getCapacity(): int
    {
        return intval($this->capacity);
    }

    /**
     * @param int $capacity
     * @return Facility
     */
    public function setCapacity(int $capacity): Facility
    {
        $this->capacity = $capacity;
        return $this;
    }

    /**
     * @return bool
     */
    public function isComputer(): bool
    {
        return $this->computer;
    }

    /**
     * @param bool $computer
     * @return Facility
     */
    public function setComputer(bool $computer): Facility
    {
        $this->computer = $computer;
        return $this;
    }

    /**
     * @return int
     */
    public function getStudentComputers(): int
    {
        return $this->studentComputers;
    }

    /**
     * @param int $studentComputers
     * @return Facility
     */
    public function setStudentComputers(int $studentComputers): Facility
    {
        $this->studentComputers = $studentComputers;
        return $this;
    }

    /**
     * @return bool
     */
    public function isProjector(): bool
    {
        return $this->projector;
    }

    /**
     * @param bool $projector
     * @return Facility
     */
    public function setProjector(bool $projector): Facility
    {
        $this->projector = $projector;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTv(): bool
    {
        return $this->tv;
    }

    /**
     * @param bool $tv
     * @return Facility
     */
    public function setTv(bool $tv): Facility
    {
        $this->tv = $tv;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDvd(): bool
    {
        return $this->dvd;
    }

    /**
     * @param bool $dvd
     * @return Facility
     */
    public function setDvd(bool $dvd): Facility
    {
        $this->dvd = $dvd;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHifi(): bool
    {
        return $this->hifi;
    }

    /**
     * @param bool $hifi
     * @return Facility
     */
    public function setHifi(bool $hifi): Facility
    {
        $this->hifi = $hifi;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSpeakers(): bool
    {
        return $this->speakers;
    }

    /**
     * @param bool $speakers
     * @return Facility
     */
    public function setSpeakers(bool $speakers): Facility
    {
        $this->speakers = $speakers;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIwb(): bool
    {
        return $this->iwb;
    }

    /**
     * @param bool $iwb
     * @return Facility
     */
    public function setIwb(bool $iwb): Facility
    {
        $this->iwb = $iwb;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoneInt(): ?string
    {
        return $this->phoneInt;
    }

    /**
     * @param string|null $phoneInt
     * @return Facility
     */
    public function setPhoneInt(?string $phoneInt): Facility
    {
        $this->phoneInt = $phoneInt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoneExt(): ?string
    {
        return $this->phoneExt;
    }

    /**
     * @param string|null $phoneExt
     * @return Facility
     */
    public function setPhoneExt(?string $phoneExt): Facility
    {
        $this->phoneExt = $phoneExt;
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
     * @return Facility
     */
    public function setComment(?string $comment): Facility
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * getTypeList
     * @return array
     */
    public static function getTypeList(): array
    {
        $x = SettingFactory::getSettingManager()->get('School Admin', 'facilityTypes');
        asort($x);
        $result = [];
        foreach($x as $name) {
            $result[$name] = $name;
        }
        return $result;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName().' ('.$this->getCapacity().')';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'canDelete' => $this->canDelete(),
            'capacity' => $this->getCapacity(),
            'facilities' => $this->getFacilityDetails(),
        ];
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(Facility::class)->canDelete($this);
    }

    /**
     * getFacilityDetails
     * @return string
     */
    public function getFacilityDetails(): string
    {
        $result = [];
        if ($this->isComputer())
            $result[] = TranslationHelper::translate('Teaching computer', [], 'School');
        if ($this->getStudentComputers() > 0)
            $result[] = TranslationHelper::translate('Student computers', ['count' => $this->getStudentComputers()], 'School');
        if ($this->isProjector())
            $result[] = TranslationHelper::translate('Projector', [], 'School');
        if ($this->isTv())
            $result[] = TranslationHelper::translate('TV', [], 'School');
        if ($this->isDvd())
            $result[] = TranslationHelper::translate('DVD Player', [], 'School');
        if ($this->isHifi())
            $result[] = TranslationHelper::translate('Hifi', [], 'School');
        if ($this->isSpeakers())
            $result[] = TranslationHelper::translate('Speakers', [], 'School');
        if ($this->isIwb())
            $result[] = TranslationHelper::translate('Interactive White Board', [], 'School');
        if (!empty($this->getPhoneInt()))
            $result[] = TranslationHelper::translate('Extension Number {number}', ['{number}' => $this->getPhoneInt()], 'School');
        if (!empty($this->getPhoneExt()))
            $result[] = TranslationHelper::translate('Phone Number {number}', ['{number}' => $this->getPhoneExt()], 'School');
        return implode("\n<br/>", $result);
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Facility` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(30) NOT NULL,
                    `type` CHAR(50) NOT NULL,
                    `capacity` smallint DEFAULT NULL,
                    `computer` CHAR(1) NOT NULL,
                    `student_computers` smallint DEFAULT NULL,
                    `projector` CHAR(1) NOT NULL,
                    `tv` CHAR(1) NOT NULL,
                    `dvd` CHAR(1) NOT NULL,
                    `hifi` CHAR(1) NOT NULL,
                    `speakers` CHAR(1) NOT NULL,
                    `iwb` CHAR(1) NOT NULL,
                    `phone_int` CHAR(5) DEFAULT NULL,
                    `phone_ext` CHAR(20) DEFAULT NULL,
                    `comment` longtext,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints() : string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

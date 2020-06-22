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
namespace App\Modules\Curriculum\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Unit
 * @package App\Modules\Curriculum\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Curriculum\Repository\UnitRepository")
 * @ORM\Table(name="Unit",
 *     indexes={@ORM\Index(name="course",columns={"course"}),
 *     @ORM\Index(name="creator",columns={"creator"}),
 *     @ORM\Index(name="modifier",columns={"modifier"})})
 */
class Unit extends AbstractEntity
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
     * @var Course|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Curriculum\Entity\Course")
     * @ORM\JoinColumn(name="course",referencedColumnName="id",nullable=false)
     */
    private $course;

    /**
     * @var string|null
     * @ORM\Column(length=40)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $tags;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"comment": "Should this unit be included in curriculum maps and other summaries?", "default": "Y"})
     */
    private $map = 'Y';

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", options={"default": "0"})
     * @Assert\Range(min=0,max=99)
     */
    private $ordering = 0;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $attachment;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @var string|null
     * @ORM\Column(length=50, nullable=true)
     */
    private $license;

    /**
     * @var string|null
     * @ORM\Column(length=1,nullable=true)
     */
    private $sharedPublic;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator", referencedColumnName="id", nullable=false)
     */
    private $creator;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="modifier", referencedColumnName="id", nullable=false)
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
     * @return Unit
     */
    public function setId(?string $id): Unit
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Course|null
     */
    public function getCourse(): ?Course
    {
        return $this->course;
    }

    /**
     * @param Course|null $course
     * @return Unit
     */
    public function setCourse(?Course $course): Unit
    {
        $this->course = $course;
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
     * @return Unit
     */
    public function setName(?string $name): Unit
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return Unit
     */
    public function setActive(?string $active): Unit
    {
        $this->active = self::checkBoolean($active);
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
     * @return Unit
     */
    public function setDescription(?string $description): Unit
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTags(): ?string
    {
        return $this->tags;
    }

    /**
     * @param string|null $tags
     * @return Unit
     */
    public function setTags(?string $tags): Unit
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMap(): ?string
    {
        return $this->map;
    }

    /**
     * @param string|null $map
     * @return Unit
     */
    public function setMap(?string $map): Unit
    {
        $this->map = self::checkBoolean($map);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    /**
     * @param int|null $ordering
     * @return Unit
     */
    public function setOrdering(?int $ordering): Unit
    {
        $this->ordering = $ordering;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    /**
     * @param string|null $attachment
     * @return Unit
     */
    public function setAttachment(?string $attachment): Unit
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * @param string|null $details
     * @return Unit
     */
    public function setDetails(?string $details): Unit
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLicense(): ?string
    {
        return $this->license;
    }

    /**
     * @param string|null $license
     * @return Unit
     */
    public function setLicense(?string $license): Unit
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSharedPublic(): ?string
    {
        return $this->sharedPublic;
    }

    /**
     * @param string|null $sharedPublic
     * @return Unit
     */
    public function setSharedPublic(?string $sharedPublic): Unit
    {
        $this->sharedPublic = self::checkBoolean($sharedPublic, null);
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getCreator(): ?Person
    {
        return $this->creator;
    }

    /**
     * @param Person|null $creator
     * @return Unit
     */
    public function setCreator(?Person $creator): Unit
    {
        $this->creator = $creator;
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
     * @return Unit
     */
    public function setModifier(?Person $modifier): Unit
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 21/06/2020 08:49
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Unit` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `course` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `modifier` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(40) NOT NULL,
                    `active` varchar(1) NOT NULL DEFAULT 'Y',
                    `description` longtext NOT NULL,
                    `tags` longtext NOT NULL,
                    `map` varchar(1) NOT NULL DEFAULT 'Y' COMMENT 'Should this unit be included in curriculum maps and other summaries?',
                    `ordering` smallint(6) NOT NULL DEFAULT '0',
                    `attachment` varchar(191) NOT NULL,
                    `details` longtext NOT NULL,
                    `license` varchar(50) DEFAULT NULL,
                    `shared_public` varchar(1) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `course` (`course`),
                    KEY `creator` (`creator`),
                    KEY `modifier` (`modifier`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 21/06/2020 08:53
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Unit`
                    ADD CONSTRAINT FOREIGN KEY (`course`) REFERENCES `__prefix__Course` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`modifier`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 21/06/2020 08:47
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
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
use App\Modules\Department\Entity\Department;
use App\Modules\Assess\Entity\Scale;
use App\Modules\School\Entity\YearGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class Rubric
 * @package App\Modules\Curriculum\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Curriculum\Repository\RubricRepository")
 * @ORM\Table(name="Rubric",
 *     indexes={@ORM\Index(name="department",columns={"department"}),
 *     @ORM\Index(name="scale",columns={"scale"}),
 *     @ORM\Index(name="creator",columns={"creator"})
 * })
 */
class Rubric extends AbstractEntity
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
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $category;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     */
    private $active;

    /**
     * @var string|null
     * @ORM\Column(length=10)
     */
    private $scope;

    /**
     * @var array
     */
    private static $scopeList = ['School', 'Learning Area'];

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Department\Entity\Department")
     * @ORM\JoinColumn(name="department", referencedColumnName="id")
     */
    private $department;

    /**
     * @var Collection|YearGroup[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinTable(name="RubricYearGroup",
     *      joinColumns={@ORM\JoinColumn(name="rubric",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="year_group",referencedColumnName="id")}
     *      )
     */
    private $yearGroups;

    /**
     * @var Scale|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Assess\Entity\Scale")
     * @ORM\JoinColumn(name="scale", referencedColumnName="id")
     */
    private $scale;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator", referencedColumnName="id")
     */
    private $creator;

    /**
     * Rubric constructor.
     */
    public function __construct()
    {
        $this->setYearGroups(new ArrayCollection());
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Rubric
     */
    public function setId(?string $id): Rubric
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
     * @return Rubric
     */
    public function setName(?string $name): Rubric
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return Rubric
     */
    public function setCategory(?string $category): Rubric
    {
        $this->category = $category;
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
     * @return Rubric
     */
    public function setDescription(?string $description): Rubric
    {
        $this->description = $description;
        return $this;
    }

    /**
     * isActive
     * @return bool
     * 1/06/2020 12:14
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return self::checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return Rubric
     */
    public function setActive(?string $active): Rubric
    {
        $this->active = self::checkBoolean($active);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string|null $scope
     * @return Rubric
     */
    public function setScope(?string $scope): Rubric
    {
        $this->scope = in_array($scope, self::getScopeList()) ? $scope : '';
        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * @param Department|null $department
     * @return Rubric
     */
    public function setDepartment(?Department $department): Rubric
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return YearGroup[]|Collection|null
     */
    public function getYearGroups(): Collection
    {
        if (null === $this->yearGroups) {
            $this->yearGroups = new ArrayCollection();
        }

        if ($this->yearGroups instanceof PersistentCollection) {
            $this->yearGroups->initialize();
        }

        return $this->yearGroups;
    }

    /**
     * @param YearGroup[]|Collection|null $yearGroups
     * @return Rubric
     */
    public function setYearGroups(?Collection $yearGroups)
    {
        $this->yearGroups = $yearGroups;
        return $this;
    }

    /**
     * addYearGroup
     * @param YearGroup $yearGroup
     * @return $this
     * 1/06/2020 13:02
     */
    public function addYearGroup(YearGroup $yearGroup): Rubric
    {
        if ($this->getYearGroups()->contains($yearGroup)) {
            return $this;
        }
        $this->yearGroups->add($yearGroup);

        return $this;
    }

    /**
     * @return Scale|null
     */
    public function getScale(): ?Scale
    {
        return $this->scale;
    }

    /**
     * @param Scale|null $scale
     * @return Rubric
     */
    public function setScale(?Scale $scale): Rubric
    {
        $this->scale = $scale;
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
     * @return Rubric
     */
    public function setCreator(?Person $creator): Rubric
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return array
     */
    public static function getScopeList(): array
    {
        return self::$scopeList;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 1/06/2020 12:42
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array
     * 1/06/2020 12:42
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Rubric` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `department` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `scale` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(50) NOT NULL,
                    `category` varchar(50) NOT NULL,
                    `description` longtext NOT NULL,
                    `active` varchar(1) NOT NULL,
                    `scope` varchar(10) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `department` (`department`),
                    KEY `scale` (`scale`),
                    KEY `creator` (`creator`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            "CREATE TABLE `__prefix__RubricYearGroup` (
                `rubric` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                `year_group` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                PRIMARY KEY (`rubric`,`year_group`),
                KEY `rubric` (`rubric`),
                KEY `year_group` (`year_group`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 1/06/2020 12:45
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Rubric`
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`scale`) REFERENCES `__prefix__Scale` (`id`);
                 ALTER TABLE `__prefix__RubricYearGroup`
                    ADD CONSTRAINT FOREIGN KEY (`rubric`) REFERENCES `__prefix__Rubric` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`year_group`) REFERENCES `__prefix__YearGroup` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 1/06/2020 12:47
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
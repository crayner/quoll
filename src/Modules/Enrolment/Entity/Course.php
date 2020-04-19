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

namespace App\Modules\Enrolment\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\Department;
use App\Modules\School\Validator as Validator;
use App\Util\EntityHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Course
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseRepository")
 * @ORM\Table(
 *     options={"auto_increment": 1},
 *     name="Course",
 *     indexes={
 *      @ORM\Index(name="academicYear",columns={"academic_year"})},
 *     uniqueConstraints={
 *      @ORM\UniqueConstraint(name="name_year",columns={"academic_year","name"}),
 *      @ORM\UniqueConstraint(name="name_short_year",columns={"academic_year","nameShort"})})
 * @UniqueEntity({"academicYear", "name"})
 * @UniqueEntity({"academicYear", "nameShort"})
 */
class Course implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer", columnDefinition="INT(8) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id", nullable=false)
     */
    private $academicYear;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Department")
     * @ORM\JoinColumn(name="department",referencedColumnName="id",nullable=true)
     */
    private $department;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="nameShort")
     * @Assert\NotBlank
     */
    private $nameShort;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"comment": "Should this course be included in curriculum maps and other summaries?", "default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $map = 'Y';

    /**
     * @var string|null
     * @ORM\Column(name="year_group_list")
     * @Validator\YearGroupList()
     */
    private $yearGroupList;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", name="order_by", columnDefinition="INT(3)", nullable=true)
     */
    private $orderBy;

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\CourseClass", mappedBy="course")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $courseClasses;

    /**
     * Course constructor.
     */
    public function __construct()
    {
        $this->courseClasses = new ArrayCollection();
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
     * @return Course
     */
    public function setId(?int $id): Course
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @param AcademicYear|null $academicYear
     * @return Course
     */
    public function setAcademicYear(?AcademicYear $academicYear): Course
    {
        $this->academicYear = $academicYear;
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
     * @return Course
     */
    public function setDepartment(?Department $department): Course
    {
        $this->department = $department;
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
     * @return Course
     */
    public function setName(?string $name): Course
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
     * @return Course
     */
    public function setNameShort(?string $nameShort): Course
    {
        $this->nameShort = $nameShort;
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
     * @return Course
     */
    public function setDescription(?string $description): Course
    {
        $this->description = $description;
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
     * @return Course
     */
    public function setMap(?string $map): Course
    {
        $this->map = self::checkBoolean($map, 'Y');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getYearGroupList(): ?string
    {
        return $this->yearGroupList;
    }

    /**
     * @param string|null $yearGroupList
     * @return Course
     */
    public function setYearGroupList(?string $yearGroupList): Course
    {
        $this->yearGroupList = $yearGroupList;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    /**
     * @param int|null $orderBy
     * @return Course
     */
    public function setOrderBy(?int $orderBy): Course
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * getCourseClasses
     * @return Collection
     */
    public function getCourseClasses(): Collection
    {
        if (empty($this->courseClasses))
            $this->courseClasses = new ArrayCollection();

        if ($this->courseClasses instanceof PersistentCollection)
            $this->courseClasses->initialize();

        return $this->courseClasses;
    }

    /**
     * @param Collection|null $courseClasses
     * @return Course
     */
    public function setCourseClasses(?Collection $courseClasses): Course
    {
        $this->courseClasses = $courseClasses ?: new ArrayCollection();
        return $this;
    }

    /**
     * __toArray
     * @param array $ignore
     * @return array
     */
    public function __toArray(array $ignore = []): array
    {
        return EntityHelper::__toArray(Course::class, $this, $ignore);
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('. $this->getNameShort(). ')';
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

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__Course` (
                    `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameShort` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `description` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `map` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\' COMMENT \'Should this course be included in curriculum maps and other summaries?\',
                    `year_group_list` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:simple_array)\',
                    `order_by` int(3) DEFAULT NULL,
                    `academic_year` int(3) UNSIGNED DEFAULT NULL,
                    `department` int(4) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name_year` (`academic_year`,`name`) USING BTREE,
                    UNIQUE KEY `name_short_year` (`academic_year`,`nameShort`) USING BTREE,
                    KEY `department` (`department`),
                    KEY `academic_year` (`academic_year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Course`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}
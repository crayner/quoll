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
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DaysOfWeek
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\DaysOfWeekRepository")
 * @ORM\Table(name="DaysOfWeek",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation",columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="sort_order",columns={"sort_order"}) })
 * @UniqueEntity({"name"})
 * @UniqueEntity({"abbreviation"})
 * @todo Days of the Week Validator
 */
class DaysOfWeek extends AbstractEntity
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
     * @var string
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     * @Assert\Length(max=10)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=4)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $abbreviation;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=7)
     */
    private $sortOrder;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private $schoolDay = true;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolOpen;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolStart;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolEnd;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="time_immutable",nullable=true)
     */
    private $schoolClose;

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
     * @return DaysOfWeek
     */
    public function setId(?string $id): DaysOfWeek
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
     * @param string $name
     * @return DaysOfWeek
     */
    public function setName(string $name): DaysOfWeek
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
     * @param string $abbreviation
     * @return DaysOfWeek
     */
    public function setAbbreviation(string $abbreviation): DaysOfWeek
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int|null $sortOrder
     * @return DaysOfWeek
     */
    public function setSortOrder(?int $sortOrder): DaysOfWeek
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * isSchoolDay
     * @return bool
     */
    public function isSchoolDay(): bool
    {
        return (bool)$this->schoolDay;
    }

    /**
     * setSchoolDay
     * @param bool|null $schoolDay
     * @return DaysOfWeek
     * 5/08/2020 16:01
     */
    public function setSchoolDay(?bool $schoolDay): DaysOfWeek
    {
        $this->schoolDay = (bool)$schoolDay;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSchoolOpen(): ?DateTimeImmutable
    {
        return $this->schoolOpen;
    }

    /**
     * SchoolOpen.
     *
     * @param DateTimeImmutable|null $schoolOpen
     * @return DaysOfWeek
     */
    public function setSchoolOpen(?DateTimeImmutable $schoolOpen): DaysOfWeek
    {
        $this->schoolOpen = $schoolOpen;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSchoolStart(): ?DateTimeImmutable
    {
        return $this->schoolStart;
    }

    /**
     * SchoolStart.
     *
     * @param DateTimeImmutable|null $schoolStart
     * @return DaysOfWeek
     */
    public function setSchoolStart(?DateTimeImmutable $schoolStart): DaysOfWeek
    {
        $this->schoolStart = $schoolStart;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSchoolEnd(): ?DateTimeImmutable
    {
        return $this->schoolEnd;
    }

    /**
     * SchoolEnd.
     *
     * @param DateTimeImmutable|null $schoolEnd
     * @return DaysOfWeek
     */
    public function setSchoolEnd(?DateTimeImmutable $schoolEnd): DaysOfWeek
    {
        $this->schoolEnd = $schoolEnd;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSchoolClose(): ?DateTimeImmutable
    {
        return $this->schoolClose;
    }

    /**
     * SchoolClose.
     *
     * @param DateTimeImmutable|null $schoolClose
     * @return DaysOfWeek
     */
    public function setSchoolClose(?DateTimeImmutable $schoolClose): DaysOfWeek
    {
        $this->schoolClose = $schoolClose;
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
     * coreData
     * @return array
     * 4/07/2020 12:39
     */
    public function coreData(): array 
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/DaysOfWeekCoreData.yaml'));
    }
}
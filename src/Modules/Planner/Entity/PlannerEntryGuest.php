<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Planner\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PlannerEntryGuest
 * @package App\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Planner\Repository\PlannerEntryGuestRepository")
 * @ORM\Table(name="PlannerEntryGuest",
 *     indexes={@ORM\Index(name="planner_entry",columns={"planner_entry"}),
 *     @ORM\Index(name="person",columns={"person"})})
 */
class PlannerEntryGuest extends AbstractEntity
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
     * @var PlannerEntry|null
     * @ORM\ManyToOne(targetEntity="PlannerEntry",inversedBy="plannerEntryGuests")
     * @ORM\JoinColumn(name="planner_entry",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $plannerEntry;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=16)
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getRoleList")
     */
    private $role;

    /**
     * @var array 
     */
    private static $roleList = ['Guest Student','Guest Teacher','Guest Assistant','Guest Technician','Guest Parent','Other Guest'];

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return PlannerEntryGuest
     */
    public function setId(?string $id): PlannerEntryGuest
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return PlannerEntry|null
     */
    public function getPlannerEntry(): ?PlannerEntry
    {
        return $this->plannerEntry;
    }

    /**
     * @param PlannerEntry|null $plannerEntry
     * @return PlannerEntryGuest
     */
    public function setPlannerEntry(?PlannerEntry $plannerEntry): PlannerEntryGuest
    {
        $this->plannerEntry = $plannerEntry;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return PlannerEntryGuest
     */
    public function setPerson(?Person $person): PlannerEntryGuest
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     * @return PlannerEntryGuest
     */
    public function setRole(?string $role): PlannerEntryGuest
    {
        $this->role = in_array($role, self::getRoleList()) ? $role : null ;
        return $this;
    }

    /**
     * @return array
     */
    public static function getRoleList(): array
    {
        return self::$roleList;
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
     * 21/06/2020 09:45
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__PlannerEntryGuest` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `planner_entry` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `role` varchar(16) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `planner_entry` (`planner_entry`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 21/06/2020 09:49
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__PlannerEntryGuest`
  ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
  ADD CONSTRAINT FOREIGN KEY (`planner_entry`) REFERENCES `__prefix__PlannerEntry` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 21/06/2020 09:49
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
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
namespace App\Modules\Planner\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PlannerEntryStudentHomework
 * @package App\Modules\Planner\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Planner\Repository\PlannerEntryStudentHomeworkRepository")
 * @ORM\Table(name="PlannerEntryStudentHomework",
 *     indexes={@ORM\Index(name="planner_entry", columns={"planner_entry"}),
 *     @ORM\Index(name="person", columns={"person"})})
 */
class PlannerEntryStudentHomework extends AbstractEntity
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
     * @var PlannerEntryGuest|null
     * @ORM\ManyToOne(targetEntity="PlannerEntry", inversedBy="studentHomeworkEntries")
     * @ORM\JoinColumn(name="planner_entry",referencedColumnName="id")
     */
    private $plannerEntry;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     */
    private $person;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable")
     */
    private $homeworkDueDateTime;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $homeworkDetails;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     */
    private $homeworkComplete = 'N';

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return PlannerEntryStudentHomework
     */
    public function setId(?string $id): PlannerEntryStudentHomework
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
     * @return PlannerEntryStudentHomework
     */
    public function setPlannerEntry(?PlannerEntry $plannerEntry): PlannerEntryStudentHomework
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
     * @return PlannerEntryStudentHomework
     */
    public function setPerson(?Person $person): PlannerEntryStudentHomework
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getHomeworkDueDateTime(): ?\DateTimeImmutable
    {
        return $this->homeworkDueDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $homeworkDueDateTime
     * @return PlannerEntryStudentHomework
     */
    public function setHomeworkDueDateTime(?\DateTimeImmutable $homeworkDueDateTime): PlannerEntryStudentHomework
    {
        $this->homeworkDueDateTime = $homeworkDueDateTime;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkDetails(): ?string
    {
        return $this->homeworkDetails;
    }

    /**
     * @param string|null $homeworkDetails
     * @return PlannerEntryStudentHomework
     */
    public function setHomeworkDetails(?string $homeworkDetails): PlannerEntryStudentHomework
    {
        $this->homeworkDetails = $homeworkDetails;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeworkComplete(): ?string
    {
        return $this->homeworkComplete;
    }

    /**
     * @param string|null $homeworkComplete
     * @return PlannerEntryStudentHomework
     */
    public function setHomeworkComplete(?string $homeworkComplete): PlannerEntryStudentHomework
    {
        $this->homeworkComplete = self::checkBoolean($homeworkComplete, 'N');
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
     * 21/06/2020 09:54
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__PlannerEntryStudentHomework` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `planner_entry` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `homework_due_date_time` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `homework_details` longtext NOT NULL,
                    `homework_complete` varchar(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`id`),
                    KEY `planner_entry` (`planner_entry`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 21/06/2020 09:55
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__PlannerEntryStudentHomework`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`planner_entry`) REFERENCES `__prefix__PlannerEntry` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 21/06/2020 09:53
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
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
 * Date: 5/12/2018
 * Time: 16:56
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class timetableDayDate
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="TimetableDayDateRepository")
 * @ORM\Table(name="TimetableDayDate",
 *     indexes={@ORM\Index(name="timetable_day", columns={"timetable_day"})})
 */
class TimetableDayDate extends AbstractEntity
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
     * @var TimetableDay|null
     * @ORM\ManyToOne(targetEntity="TimetableDay",inversedBy="timetableDayDates")
     * @ORM\JoinColumn(name="timetable_day",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $timetableDay;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return TimetableDayDate
     */
    public function setId(?string $id): TimetableDayDate
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TimetableDay|null
     */
    public function getTimetableDay(): ?TimetableDay
    {
        return $this->timetableDay;
    }

    /**
     * @param TimetableDay|null $timetableDay
     * @return TimetableDayDate
     */
    public function setTimetableDay(?TimetableDay $timetableDay): TimetableDayDate
    {
        $this->timetableDay = $timetableDay;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     * @return TimetableDayDate
     */
    public function setDate(?\DateTime $date): TimetableDayDate
    {
        $this->date = $date;
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

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__timetableDayDate` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `date` date NOT NULL,
                    `timetable_day` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `timetable_day` (`timetable_day`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__timetableDayDate`
                    ADD CONSTRAINT FOREIGN KEY (`timetable_day`) REFERENCES `__prefix__timetableDay` (`id`);";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

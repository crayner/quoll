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
 * Class TTDayDate
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTDayDateRepository")
 * @ORM\Table(name="TTDayDate",
 *     indexes={@ORM\Index(name="timetable_day", columns={"timetable_day"})})
 */
class TTDayDate extends AbstractEntity
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
     * @var TTDay|null
     * @ORM\ManyToOne(targetEntity="TTDay", inversedBy="timetableDayDates")
     * @ORM\JoinColumn(name="timetable_day", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $TTDay;

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
     * @return TTDayDate
     */
    public function setId(?string $id): TTDayDate
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TTDay|null
     */
    public function getTTDay(): ?TTDay
    {
        return $this->TTDay;
    }

    /**
     * @param TTDay|null $TTDay
     * @return TTDayDate
     */
    public function setTTDay(?TTDay $TTDay): TTDayDate
    {
        $this->TTDay = $TTDay;
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
     * @return TTDayDate
     */
    public function setDate(?\DateTime $date): TTDayDate
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
        return ["CREATE TABLE `__prefix__TTDayDate` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `date` date NOT NULL,
                    `timetable_day` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `timetable_day` (`timetable_day`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__TTDayDate`
                    ADD CONSTRAINT FOREIGN KEY (`timetable_day`) REFERENCES `__prefix__TTDay` (`id`);";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

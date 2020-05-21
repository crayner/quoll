<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:56
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TTDayDate
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTDayDateRepository")
 * @ORM\Table(name="TTDayDate",
 *     indexes={@ORM\Index(name="timetable_day", columns={"timetable_day"})})
 */
class TTDayDate implements EntityInterface
{
    CONST VERSION = '20200401';

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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return TTDayDate
     */
    public function setId(?int $id): TTDayDate
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

    public function create(): string
    {
        return "CREATE TABLE IF NOT EXISTS `__prefix__TTDayDate` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `date` date NOT NULL,
                    `timetable_day` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `timetable_day` (`timetable_day`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__TTDayDate`
                    ADD CONSTRAINT FOREIGN KEY (`timetable_day`) REFERENCES `__prefix__TTDay` (`id`);";
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

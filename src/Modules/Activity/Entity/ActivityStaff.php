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
namespace App\Modules\Activity\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ActivityStaff
 * @package App\Modules\Activity\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Activity\Repository\ActivityStaffRepository")
 * @ORM\Table(name="ActivityStaff",
 *     indexes={
 *          @ORM\Index(name="activity", columns={"activity"}),
 *          @ORM\Index(name="person", columns={"person"})
 *     })
 */
class ActivityStaff implements EntityInterface
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
     * @var Activity|null
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="staff")
     * @ORM\JoinColumn(name="activity",referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $activity;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string
     * @ORM\Column(length=9, options={"default": "Organiser"})
     * @Assert\Choice(callback="getRoleList")
     */
    private $role = 'Organiser';

    /**
     * @var array
     */
    private static $roleList = ['Organiser', 'Coach', 'Assistant', 'Other'];

    /**
     * @return array
     */
    public static function getRoleList(): array
    {
        return self::$roleList;
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
     * @return ActivityStaff
     */
    public function setId(?string $id): ActivityStaff
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Activity|null
     */
    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity|null $activity
     * @return ActivityStaff
     */
    public function setActivity(?Activity $activity): ActivityStaff
    {
        $this->activity = $activity;
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
     * @return ActivityStaff
     */
    public function setPerson(?Person $person): ActivityStaff
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return ActivityStaff
     */
    public function setRole(string $role): ActivityStaff
    {
        $this->role = in_array($role, self::getRoleList()) ? $role : 'Organiser';
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
     * 4/06/2020 09:43
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__ActivityStaff` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `activity` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `role` varchar(9) NOT NULL DEFAULT 'Organiser',
                    PRIMARY KEY (`id`),
                    KEY `activity` (`activity`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/06/2020 09:43
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__ActivityStaff`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`activity`) REFERENCES `__prefix__Activity` (`id`);";
    }

    /**
     * coreData
     * @return array
     * 4/06/2020 09:43
     */
    public function coreData(): array
    {
        return '';
    }

    /**
     * getVersion
     * @return string
     * 4/06/2020 09:44
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}

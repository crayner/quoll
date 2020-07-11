<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/07/2020
 * Time: 08:46
 */
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Modules\System\Entity\I18n;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ParentContact
 * @package App\Modules\People\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\ParentContactRepository")
 * @ORM\Table(name="ParentContact",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person",columns={"person"})
 *  },
 *  indexes={
 *      @ORM\Index(name="locale",columns={"locale"})
 *  }
 * )
 * @UniqueEntity("person")
 */
class ParentContact extends AbstractEntity
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
     * @var Person
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="parent")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $vehicleRegistration;

    /**
     * @var I18n|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\I18n")
     * @ORM\JoinColumn(name="locale",referencedColumnName="id",nullable=true)
     */
    private $locale;

    /**
     * @var boolean|null
     * @ORM\Column(length=1, options={"default": 1})
     */
    private $receiveNotificationEmails = true;

    /**
     * @var boolean|null
     * @ORM\Column(length=1, options={"default": 1})
     */
    private $viewCalendarSchool = true;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return ParentContact
     */
    public function setId(?string $id): ParentContact
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * setPerson
     * @param Person $person
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:11
     */
    public function setPerson(Person $person, bool $reflect = true): ParentContact
    {
        $this->person = $person;
        if ($person && $person instanceof Person) {
            $person->setParent($this, false);
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVehicleRegistration(): ?string
    {
        return $this->vehicleRegistration;
    }

    /**
     * @param string|null $vehicleRegistration
     * @return ParentContact
     */
    public function setVehicleRegistration(?string $vehicleRegistration): ParentContact
    {
        $this->vehicleRegistration = $vehicleRegistration;
        return $this;
    }

    /**
     * @return I18n|null
     */
    public function getLocale(): ?I18n
    {
        return $this->locale;
    }

    /**
     * @param I18n|null $locale
     * @return ParentContact
     */
    public function setLocale(?I18n $locale): ParentContact
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isReceiveNotificationEmails(): bool
    {
        return (bool)$this->receiveNotificationEmails;
    }

    /**
     * @param bool|null $receiveNotificationEmails
     * @return ParentContact
     */
    public function setReceiveNotificationEmails(?bool $receiveNotificationEmails): ParentContact
    {
        $this->receiveNotificationEmails = (bool)$receiveNotificationEmails;
        return $this;
    }

    /**
     * @return bool
     */
    public function isViewCalendarSchool(): bool
    {
        return (bool)$this->viewCalendarSchool;
    }

    /**
     * @param bool|null $viewCalendarSchool
     * @return ParentContact
     */
    public function setViewCalendarSchool(?bool $viewCalendarSchool): ParentContact
    {
        $this->viewCalendarSchool = (bool)$viewCalendarSchool;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * create
     * @return array|string[]
     * 4/07/2020 09:51
     */
    public function create(): array
    {
        return [
            "CREATE TABLE `__prefix__ParentContact` (
                `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                `person` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                `locale` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                `vehicle_registration` varchar(20) DEFAULT NULL,
                `receive_notification_emails` varchar(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`),
                UNIQUE KEY `person` (`person`),
                KEY `locale` (`locale`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
        ];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/07/2020 09:51
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__ParentContact`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`locale`) REFERENCES `__prefix__I18n` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 4/07/2020 09:52
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
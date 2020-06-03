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
namespace App\Modules\Comms\Entity;

use App\Modules\System\Entity\Module;
use App\Modules\People\Entity\Person;
use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Notification
 * @package App\Modules\Comms\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Comms\Repository\NotificationRepository")
 * @ORM\Table(name="Notification",
 *     indexes={@ORM\Index(name="person",columns={"person"}),
 *     @ORM\Index(name="module",columns={"module"})})
 * @ORM\HasLifecycleCallbacks()
 * */
class Notification extends AbstractEntity
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=8, options={"default": "New"})
     */
    private $status = 'New';

    /**
     * @var array
     */
    private static $statusList = ['New', 'Archived'];

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="notifications")
     * @ORM\JoinColumn(name="module", referencedColumnName="id", nullable=true)
     */
    private $module;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",options={"default": "1"})
     * @Assert\NotBlank()
     */
    private $count = 1;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @var array|null
     */
    private $textOptions;

    /**
     * @var string|null
     * @ORM\Column(length=191,options={"comment": "Relative to absoluteURL, start with a forward slash"},nullable=true)
     */
    private $actionLink;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable")
     */
    private $timestamp;

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
     * @return Notification
     */
    public function setId(?string $id): Notification
    {
        $this->id = $id;
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
     * @return Notification
     */
    public function setPerson(?Person $person): Notification
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Notification
     */
    public function setStatus(?string $status): Notification
    {
        $this->status = in_array($status, self::getStatusList()) ? $status: 'New' ;
        return $this;
    }

    /**
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * @param Module|null $module
     * @return Notification
     */
    public function setModule(?Module $module): Notification
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int|null $count
     * @return Notification
     */
    public function setCount(?int $count): Notification
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     * @return Notification
     */
    public function setText(?string $text): Notification
    {
        $this->text = $text;
        return $this;
    }

    /**
     * getTextOptions
     * @return array|null
     */
    public function getTextOptions(): ?array
    {
        return $this->textOptions = $this->textOptions ?: [];
    }

    /**
     * setTextOptions
     * @param array|null $textOptions
     * @return Notification
     */
    public function setTextOptions(?array $textOptions): Notification
    {
        $this->textOptions = $textOptions ?: [];
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    /**
     * @param string|null $actionLink
     * @return Notification
     */
    public function setActionLink(?string $actionLink): Notification
    {
        $this->actionLink = $actionLink;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime|null $timestamp
     * @return Notification
     */
    public function setTimestamp(?\DateTime $timestamp): Notification
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * createTimestamp
     * @ORM\PrePersist()
     */
    public function createTimestamp()
    {
        if (null === $this->getTimestamp())
            $this->timestamp = new \DateTime();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'module' => $this->getModule()->getName(),
        ];
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Notification` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `status` CHAR(8) NOT NULL DEFAULT 'New',
                    `count` smallint DEFAULT NULL,
                    `text` longtext NOT NULL,
                    `action_link` CHAR(191) NOT NULL COMMENT 'Relative to absoluteURL, start with a forward slash',
                    `timestamp` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `person` CHAR(36) DEFAULT NULL,
                    `module` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `person` (`person`),
                    KEY `module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Notification`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

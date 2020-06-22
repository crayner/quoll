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
namespace App\Modules\Comms\Entity;

use App\Provider\ProviderFactory;
use App\Modules\School\Entity\YearGroup;
use App\Modules\Comms\Validator as Valid;
use App\Modules\People\Entity\Person;
use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NotificationListener
 * @package App\Modules\Comms\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Comms\Repository\NotificationListenerRepository")
 * @ORM\Table(name="NotificationListener",
 *     indexes={@ORM\Index(name="person",columns={"person"}),
 *     @ORM\Index(name="notification_event",columns={"notification_event"})})
 * @Valid\EventListener()
 */
class NotificationListener extends AbstractEntity
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
     * @var NotificationEvent|null
     * @ORM\ManyToOne(targetEntity="NotificationEvent", inversedBy="listeners")
     * @ORM\JoinColumn(name="notification_event", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $event;
    
    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(referencedColumnName="id",name="person")
     * @ORM\OrderBy({"surname": "ASC", "firstName": "ASC"})
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     * @Assert\Choice(callback="getScopeTypeList")
     * @Assert\NotBlank()
     */
    private $scopeType;

    /**
     * @var array
     */
    private static $scopeTypeList = [
        'All',
        'Student',
        'Staff',
        'Year Group'
    ];

    /**
     * @var string|null
     * @ORM\Column(length=36,nullable=true)
     */
    private $scopeIdentifier;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return NotificationListener
     */
    public function setId(?string $id): NotificationListener
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return NotificationEvent|null
     */
    public function getEvent(): ?NotificationEvent
    {
        return $this->event;
    }

    /**
     * @param NotificationEvent|null $notification
     * @return NotificationListener
     */
    public function setEvent(?NotificationEvent $event, bool $mirror = true): NotificationListener
    {
        if ($mirror)
            $event->addListener($this, false);
        $this->event = $event;
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
     * @return NotificationListener
     */
    public function setPerson(?Person $person): NotificationListener
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScopeType(): ?string
    {
        return $this->scopeType;
    }

    /**
     * @param string|null $scopeType
     * @return NotificationListener
     */
    public function setScopeType(?string $scopeType): NotificationListener
    {
        $this->scopeType = $scopeType;
        if ($scopeType === 'All')
            $this->setScopeIdentifier(null);
        return $this;
    }

    /**
     * getScopeIdentifier
     * @return string|null
     */
    public function getScopeIdentifier(): ?string
    {
        if ($this->getScopeType() === 'All')
            $this->scopeIdentifier = null;
        return $this->scopeIdentifier;
    }

    /**
     * setScopeIdentifier
     * @param string|null $scopeIdentifier
     * @return NotificationListener
     */
    public function setScopeIdentifier(?string $scopeIdentifier): NotificationListener
    {
        if ($this->getScopeType() === 'All')
            $scopeIdentifier  = null;
        $this->scopeIdentifier = $scopeIdentifier;
        return $this;
    }

    /**
     * getScopeTypeList
     * @return array
     */
    public static function getScopeTypeList(): array
    {
        $result = [];
        foreach(self::$scopeTypeList as $name)
            $result[$name] = $name;
        return $result;
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
     * getChainedValues
     * @param array $available
     * @return array
     */
    public static function getChainedValues(array $available): array
    {
        $result = [];
        if (array_key_exists('All', $available) || $available === [])
            $result['All'] = [];

        if (array_key_exists('Student', $available) || $available === [])
            $result['Student'] = ProviderFactory::create(Person::class)->getCurrentStudentChoiceList();

        if (array_key_exists('Staff', $available) || $available === [])
            $result['Staff'] = ProviderFactory::create(Person::class)->getCurrentStaffChoiceList();

        if (array_key_exists('Year Group', $available) || $available === [])
            $result['Year Group'] = ProviderFactory::create(YearGroup::class)->getCurrentYearGroupChoiceList();

        return $result;
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__NotificationListener` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `scope_type` CHAR(30) DEFAULT NULL,
                    `scope_identifier` CHAR(36) DEFAULT NULL,
                    `notification_event` CHAR(36) DEFAULT NULL,
                    `person` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `notification_event` (`notification_event`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__NotificationListener`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`notification_event`) REFERENCES `__prefix__NotificationEvent` (`id`);';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

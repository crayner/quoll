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

use App\Modules\Comms\Validator\EventListener;
use App\Provider\ProviderFactory;
use App\Modules\School\Entity\YearGroup;
use App\Modules\People\Entity\Person;
use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NotificationListener
 * @package App\Modules\Comms\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Comms\Repository\NotificationListenerRepository")
 * @ORM\Table(name="NotificationListener",
 *      indexes={@ORM\Index(name="person",columns={"person"}),
 *          @ORM\Index(name="notification_event",columns={"notification_event"})},
 *      uniqueConstraints={@ORM\UniqueConstraint(name="unique_listnener",columns={"notification_event","person","scope_type","scope_identifier"})}
 * )
 * @EventListener()
 * @UniqueEntity({"person","event","scopeType","scopeIdentifier"})
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
        'All' => 'All',
        'Student' => 'student',
        'Staff' => 'staff',
        'Year Group' => 'year_group',
    ];

    /**
     * @var string
     * @ORM\Column(length=36,nullable=true)
     * @Assert\Choice(callback="getIdentifierChoices")
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
     * setEvent
     * @param NotificationEvent|null $event
     * @param bool $mirror
     * @return $this
     * 24/06/2020 09:06
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
        if ($scopeType === 'All') {
            $this->setScopeIdentifier(null);
        }
        return $this;
    }

    /**
     * getScopeIdentifier
     * @return string
     * 24/06/2020 15:45
     */
    public function getScopeIdentifier(): ?string
    {
        if ($this->getScopeType() === 'All') {
            $this->scopeIdentifier = null;
        }

        return $this->scopeIdentifier;
    }

    /**
     * setScopeIdentifier
     * @param string|null $scopeIdentifier
     * @return NotificationListener
     */
    public function setScopeIdentifier(?string $scopeIdentifier): NotificationListener
    {
        if ($this->getScopeType() === 'All') {
            $scopeIdentifier = null;
        }

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
        foreach(self::$scopeTypeList as $label=>$value)
            $result[$label] = $value;
        return $result;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'unique') {
            return [
                'identifier' => $this->getId(),
                'person' => $this->getPerson(),
                'event' => $this->getEvent(),
                'scopeType' => $this->getScopeType(),
                'scopeIdentifier' => $this->getScopeIdentifier(),
            ];
        }
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
        if (in_array('All', $available) || $available === [])
            $result['All'] = [];

        if (in_array('student', $available) || $available === [])
            $result['student'] = ProviderFactory::create(Person::class)->getCurrentStudentChoiceList();

        if (in_array('staff', $available) || $available === [])
            $result['staff'] = ProviderFactory::create(Person::class)->getCurrentStaffChoiceList();

        if (in_array('year_group', $available) || $available === [])
            $result['year_group'] = ProviderFactory::create(YearGroup::class)->getCurrentYearGroupChoiceList();

        return $result;
    }

    /**
     * create
     * @return array|string[]
     * 24/06/2020 09:31
     */
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
                    KEY `person` (`person`),
                    UNIQUE KEY `unique_listener` (`notification_event`,`person`,`scope_type`,`scope_identifier`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 23/06/2020 15:31
     */
    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__NotificationListener`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`notification_event`) REFERENCES `__prefix__NotificationEvent` (`id`);';
    }

    /**
     * getVersion
     * @return string
     * 23/06/2020 15:31
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * getIdentifierChoices
     * @return array
     * 25/06/2020 09:52
     */
    public function getIdentifierChoices(): array
    {
        return self::getChainedValues([$this->getScopeType()]);
    }
}

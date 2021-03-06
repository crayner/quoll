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

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use App\Modules\People\Entity\Person;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NotificationEvent
 * @package App\Modules\Comms\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Comms\Repository\NotificationEventRepository")
 * @ORM\Table(name="NotificationEvent",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="event_module", columns={"event","module"})},
 *     indexes={@ORM\Index(name="module",columns={"module"}),
 *     @ORM\Index(name="action",columns={"action"})})
 * @UniqueEntity({"event","module"})
 * @ORM\HasLifecycleCallbacks()
 * */
class NotificationEvent extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=90)
     */
    private $event;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module",inversedBy="events")
     * @ORM\JoinColumn(name="module",referencedColumnName="id",nullable=true)
     */
    private $module;

    /**
     * @var Action|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Action")
     * @ORM\JoinColumn(name="action",referencedColumnName="id",nullable=true)
     */
    private $action;

    /**
     * @var string|null
     * @ORM\Column(length=12, options={"default": "Core"})
     * @Assert\Choice(callback="getTypeList")
     */
    private $type = 'Core';

    /**
     * @var array
     */
    private static $typeList = ['Core', 'Additional', 'CLI'];

    /**
     * @var array
     * @ORM\Column(type="simple_array")
     * @Assert\Choice(callback="getScopeList",multiple=true)
     */
    private $scopes = ['All'];

    /**
     * @var string[] 
     */
    private static $scopeList = [
        'All',
        'year_group',
        'student',
        'staff',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="NotificationListener",mappedBy="event",orphanRemoval=true)
     */
    private $listeners;

    /**
     * NotificationEvent constructor.
     */
    public function __construct()
    {
        $this->listeners = new ArrayCollection();
    }

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
     * @return NotificationEvent
     */
    public function setId(?string $id): NotificationEvent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * @param string|null $event
     * @return NotificationEvent
     */
    public function setEvent(?string $event): NotificationEvent
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return NotificationEvent
     */
    public function setType(?string $type): NotificationEvent
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Core';
        return $this;
    }

    /**
     * @return array
     */
    public function getScopes(): ?array
    {
        if (!is_array($this->scopes))
            $this->scopes = [];
        return $this->scopes ?: [];
    }

    /**
     * @param array|null $scopes
     * @return NotificationEvent
     */
    public function setScopes(?array $scopes): NotificationEvent
    {
        $this->scopes = $scopes ?: ['All'];
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getScopeList(): array
    {
        return self::$scopeList;
    }

    /**
     * isActive
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * getActive
     * @return string
     */
    public function getActive(): string
    {
        return $this->checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return NotificationEvent
     */
    public function setActive(?string $active): NotificationEvent
    {
        $this->active = self::checkBoolean($active);
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return Collection
     */
    public function getListeners(): Collection
    {
        if (null === $this->listeners)
            $this->listeners = new ArrayCollection();

        if ($this->listeners instanceof PersistentCollection)
            $this->listeners->initialize();

        return $this->listeners;
    }

    /**
     * Listeners.
     *
     * @param Collection|null $listeners
     * @return NotificationEvent
     */
    public function setListeners(?Collection $listeners): NotificationEvent
    {
        $this->listeners = $listeners;
        return $this;
    }

    /**
     * addListener
     * @param NotificationListener $listener
     * @param bool $mirror
     * @return $this
     * 24/06/2020 09:06
     */
    public function addListener(NotificationListener $listener, bool $mirror = true): NotificationEvent
    {
        if ($this->getListeners()->contains($listener))
            return $this;

        if ($mirror)
            $listener->setEvent($this, false);

        $this->listeners->add($listener);
        return $this;
    }

    /**
     * removeListener
     * @param NotificationListener $listener
     * @return NotificationEvent
     */
    public function removeListener(NotificationListener $listener): NotificationEvent
    {
        $this->getListeners()->removeElement($listener);

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
     * Module.
     *
     * @param Module|null $module
     * @return NotificationEvent
     */
    public function setModule(?Module $module): NotificationEvent
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        return $this->action;
    }

    /**
     * Action.
     *
     * @param Action|null $action
     * @return NotificationEvent
     */
    public function setAction(?Action $action): NotificationEvent
    {
        $this->action = $action;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getEvent(),
            'module' => $this->getModule()->getName(),
            'subscribers' => strval(intval($this->getListeners()->count())),
            'active' => $this->isActive() ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages'),
            'isActive' => $this->isActive(),
            'canDelete' => false,
        ];
    }

    /**
     * create
     * @return array|string[]
     * 23/06/2020 10:57
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__NotificationEvent` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `event` CHAR(90) NOT NULL,
                    `type` CHAR(12) NOT NULL DEFAULT 'Core',
                    `scopes` text NOT NULL,
                    `active` CHAR(1) NOT NULL DEFAULT 'Y',
                    `module` CHAR(36) DEFAULT NULL,
                    `action` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `event` (`event`,`module`),
                    KEY `module` (`module`),
                    KEY `action` (`action`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 23/06/2020 10:57
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__NotificationEvent`
                    ADD CONSTRAINT FOREIGN KEY (`action`) REFERENCES `__prefix__Action` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 23/06/2020 10:57
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * getCoreData
     * @return array
     * 23/06/2020 10:57
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/NotificationEventCoreData.yaml'));
    }

    /**
     * coreDataLinks
     * @return mixed
     * 23/06/2020 10:57
     */
    public function coreDataLinks()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/NotificationEventCoreLinks.yaml'));
    }

    /**
     * sortListeners
     * @return NotificationEvent
     * 24/06/2020 12:18
     */
    public function sortListeners(?ArrayCollection $listeners): NotificationEvent
    {
        $listeners = $listeners ?: $this->getListeners();

        $iterator = $listeners->getIterator();

        $iterator->uasort(
            function (NotificationListener $a, NotificationListener $b) {
                return $a->getScopeType() <= $b->getScopeType() ? -1 : 1;
            }
        );

        return $this->setListeners(new ArrayCollection(iterator_to_array($iterator, false)));
    }
}

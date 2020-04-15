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
namespace App\Modules\Comms\Entity;

use App\Manager\EntityInterface;
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

/**
 * Class NotificationEvent
 * @package App\Modules\Comms\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Comms\Repository\NotificationEventRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="NotificationEvent",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="eventModule", columns={"event","module"})},
 *     indexes={@ORM\Index(name="module",columns={"module"}),
 *     @ORM\Index(name="action",columns={"action"})})
 * @UniqueEntity(fields={"event","module"})
 * @ORM\HasLifecycleCallbacks()
 * */
class NotificationEvent implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(6) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=90)
     */
    private $event;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="moduleName",nullable=true)
     * @deprecated
     */
    private $moduleName;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="events")
     * @ORM\JoinColumn(name="module", referencedColumnName="id", nullable=true)
     */
    private $module;

    /**
     * @var string|null
     * @ORM\Column(length=50, name="actionName",nullable=true)
     * @deprecated
     */
    private $actionName;

    /**
     * @var Action|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Action")
     * @ORM\JoinColumn(name="action", referencedColumnName="id", nullable=true)
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
     * @var array|null
     * @ORM\Column(options={"default": "All"}, type="simple_array")
     */
    private $scopes;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="NotificationListener", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return NotificationEvent
     */
    public function setId(?int $id): NotificationEvent
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
     * @deprecated 10 Sep/2019  Use getModule()->getName()
     */
    public function getModuleName(): ?string
    {
        trigger_error(sprintf('The method %s is deprecated in %s', __METHOD__, __CLASS__), E_USER_DEPRECATED);
        return $this->moduleName ?: ($this->getModule() ? $this->getModule()->getName() : null);
    }

    /**
     * @param string|null $moduleName
     * @return NotificationEvent
     * @deprecated 10 Sep/2019  Use getModule()->setName()
     */
    public function setModuleName(?string $moduleName): NotificationEvent
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @return string|null
     * @deprecated 10 Sep/2019  Use getAction()->getName()
     */
    public function getActionName(): ?string
    {
        trigger_error(sprintf('The method %s is deprecated in %s', __METHOD__, __CLASS__), E_USER_DEPRECATED);
        return $this->actionName ?: ($this->getAction() ? $this->getAction()->getName() : null);
    }

    /**
     * @param string|null $actionName
     * @return NotificationEvent
     * @deprecated 10 Sep/2019  Use getAction()->setName()
     */
    public function setActionName(?string $actionName): NotificationEvent
    {
        $this->actionName = $actionName;
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
        // Legacy
        foreach($this->scopes as $q=>$w) {
            if ($w === 'gibbonPersonIDStudent')
                $this->scopes[$q] = 'Student';
            if ($w === 'gibbonPersonIDStaff')
                $this->scopes[$q] = 'Staff';
            if ($w === 'gibbonYearGroupID')
                $this->scopes[$q] = 'Year Group';
        }
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
     * getListenersByPerson
     *
     * @param NotificationListener $listener
     * @return Collection
     */
    public function getListenersByPerson(NotificationListener $listener): Collection
    {
        return $this->getListeners()->filter(function (NotificationListener $entity) use ($listener) {
            if ($listener->getScopeType() !== $entity->getScopeType() && $listener->getPerson()->isEqualTo($entity->getPerson()))
                return $entity;
        });
    }

    /**
     * Listeners.
     *
     * @param Collection $listeners
     * @return NotificationEvent
     */
    public function setListeners(Collection $listeners): NotificationEvent
    {
        $this->listeners = $listeners;
        return $this;
    }

    /**
     * addListener
     * @param NotificationListener $listener
     * @param bool $mirror
     * @return NotificationEvent
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
     * legacyNameCalling
     * @ORM\PrePersist()
     */
    public function legacyNameCalling()
    {
            $this->setModuleName($this->getModuleName());
            $this->setActionName($this->getActionName());
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

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__NotificationEvent` (
                    `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `event` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `type` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Core\',
                    `scopes` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'All\',
                    `active` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
                    `module` int(4) UNSIGNED DEFAULT NULL,
                    `action` int(7) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `event` (`event`,`module`) USING BTREE,
                    KEY `module` (`module`) USING BTREE,
                    KEY `action` (`action`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__NotificationEvent`
                    ADD CONSTRAINT FOREIGN KEY (`action`) REFERENCES `__prefix__Action` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    public function coreData(): string
    {
        return '';
    }
}

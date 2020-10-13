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
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\TranslationHelper;
use App\Modules\Comms\Entity\Notification;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Module
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ModuleRepository")
 * @ORM\Table(name="Module",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @UniqueEntity({"name"})
 * */
class Module extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=30, options={"comment": "This name should be globally unique preferably."})
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private $displayName;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $entryRoute;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $securityRoles;

    /**
     * @var string|null
     * @ORM\Column(length=12,options={"default": "Core"})
     */
    private $type = 'Core';

    /**
     * @var array
     */
    private static $typeList = ['Core','Additional'];

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": "1"})
     */
    private $active = true;

    /**
     * @var string|null
     * @ORM\Column(length=10)
     */
    private $category;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable")
     */
    private $versionDate;

    /**
     * @var string|null
     * @ORM\Column(length=40)
     */
    private $author;

    /**
     * @var string|null
     * @ORM\Column()
     */
    private $url;

    /**
     * @var Collection|Action[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\System\Entity\Action",inversedBy="modules",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinTable(name="ModuleAction",
     *      joinColumns={@ORM\JoinColumn(name="module",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="action",referencedColumnName="id")}
     *  )
     * @Assert\Count(min=1,minMessage="The module hold one or more actions.")
     */
    private ?Collection $actions;

    /**
     * @var null|string
     */
    private $status;

    /**
     * @var Collection|NotificationEvent[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Comms\Entity\NotificationEvent", mappedBy="module", orphanRemoval=true)
     */
    private $events;

    /**
     * @var Collection|Notification[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Comms\Entity\Notification", mappedBy="module", orphanRemoval=true)
     */
    private $notifications;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
     * @return Module
     */
    public function setId(?string $id): Module
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param bool $displayName
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Module
     */
    public function setName(?string $name): Module
    {
        $this->name = $name;
        if ($this->getDisplayName() === null) return $this->setDisplayName($name);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return Module
     */
    public function setDisplayName(?string $displayName): Module
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Module
     */
    public function setDescription(?string $description): Module
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntryRoute(): ?string
    {
        return $this->entryRoute;
    }

    /**
     * @param string|null $entryRoute
     * @return Module
     */
    public function setEntryRoute(?string $entryRoute): Module
    {
        $this->entryRoute = $entryRoute;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getSecurityRoles(): ?array
    {
        return $this->securityRoles ?: [];
    }

    /**
     * @param array|null $securityRoles
     * @return Module
     */
    public function setSecurityRoles(?array $securityRoles): Module
    {
        $this->securityRoles = $securityRoles ?: [];
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type = in_array($this->type, self::getTypeList()) ? $this->type : 'Core';
    }

    /**
     * @param string|null $type
     * @return Module
     */
    public function setType(?string $type): Module
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Core' ;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool|null $active
     * @return Module
     */
    public function setActive(?bool $active): Module
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return Module
     */
    public function setCategory(?string $category): Module
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getVersionDate(): ?\DateTimeImmutable
    {
        return $this->versionDate;
    }

    /**
     * VersionDate.
     *
     * @param \DateTimeImmutable|null $versionDate
     * @return Module
     */
    public function setVersionDate(?\DateTimeImmutable $versionDate): Module
    {
        $this->versionDate = $versionDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string|null $author
     * @return Module
     */
    public function setAuthor(?string $author): Module
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Module
     */
    public function setUrl(?string $url): Module
    {
        $this->url = $url;
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
     * toArray
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        if ($name === 'mainMenu') {
            return [
                'route' => SecurityHelper::isModuleAccessible($this) ? $this->getEntryRoute() : null,
                'name' => TranslationHelper::translate($this->getDisplayName() ?: $this->getName(), [] , $this->getName()),
                'textDomain' => $this->getName(),
                'category' => $this->getCategory(),
                'type' => $this->getType(),
                'roles' => $this->getSecurityRoles(),
                'url' => UrlGeneratorHelper::getUrl($this->getEntryRoute()),
            ];
        }
        if ($name === 'buildContent') {
            return [
                'name' => $this->getName(),
                'displayName' => $this->getDisplayName(),
                'description' => $this->getDescription(),
                'entryRoute' => $this->getEntryRoute(),
                'securityRoles' => [ 'arrayField' => $this->getSecurityRoles()],
                'type' => $this->getType(),
                'active' => $this->isActive(),
                'category' => $this->getCategory(),
                'versionDate' => [ 'convertDate' => $this->getVersionDate()->format('Y-m-d') ],
                'author' => $this->getAuthor(),
                'url' => $this->getUrl(),
            ];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'displayName' => $this->displayName,
            'description' => $this->description,
            'entryRoute' => $this->entryRoute,
            'type' => $this->getType(),
            'active' => $this->active,
            'category' => $this->category,
            'versionDate' => $this->versionDate,
            'author' => $this->author,
            'url' => $this->url,
            'status' => $this->getStatus(),
            'isNotCore' => $this->getType() !== 'Core',
        ];
    }

    /**
     * getActions
     *
     * 3/09/2020 07:46
     * @param bool $sort
     * @return Collection
     */
    public function getActions(bool $sort = false): Collection
    {
        if (null === $this->actions)
            $this->actions = new ArrayCollection();

        if ($this->actions instanceof PersistentCollection)
            $this->actions->initialize();

        if ($sort) {
            try {
                $iterator = $this->actions->getIterator();

                $iterator->uasort(
                    function (Action $a, Action $b) {
                        return $a->getCategory().$a->getDisplayName() <= $b->getCategory().$b->getDisplayName() ? -1 : 1;
                    }
                );

                $this->setActions(new ArrayCollection(iterator_to_array($iterator, false)));
            } catch (Exception $e) {}
        }

        return $this->actions ;
    }

    /**
     * Actions.
     *
     * @param Collection $actions
     * @return Module
     */
    public function setActions(Collection $actions): Module
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * addAction
     *
     * 2/09/2020 16:26
     * @param Action $action
     * @param bool $reflect
     * @return Module
     */
    public function addAction(Action $action, bool $reflect = true): Module
    {
        if ($this->getActions()->contains($action)) return $this;

        if ($reflect) $action->addModule($this, false);

        $this->actions->add($action);

        return $this;
    }

    /**
     * getFullEntryRoute
     * @param string|null $entryRoute
     * @return string
     */
    public function getFullEntryRoute(?string $entryRoute = null): string
    {
        return Action::getRouteName($this->getName(), ($entryRoute ?: $this->getEntryRoute()));
    }

    /**
     * isEqualTo
     * @param Module $user
     * @return true
     */
    public function isEqualTo(Module $module): bool
    {
        if ($module->getId() !== $this->getId())
            return false;

        return true;
    }

    /**
     * getModuleDir
     * @return string
     */
    private function getModuleDir(): string
    {
        return realpath(__DIR__ . '/../../vendor/kookaburra') ?: '';
    }

    /**
     * @return bool|null
     */
    public function getStatus(): string
    {
        if (null === $this->status) {
            if ($this->getType() === 'Core') {
                $this->status = TranslationHelper::translate('Installed', [], 'messages');
            } else {
                if (false === is_dir($this->getModuleDir() . '/' . str_replace(' ', '-', strtolower($this->getName()))))
                {
                    $this->status = TranslationHelper::translate('Not Installed', [], 'messages');
                } else {
                    $installed = $this->getUpgradeLogs()->filter(function($log) {
                        return $log->getVersionDate() === 'Installation';
                    });
                    if ($this->getUpgradeLogs()->count() === 0 || $installed->count() === 0)
                        $this->status = TranslationHelper::translate('Not Installed', [], 'messages');
                    else
                        $this->status = TranslationHelper::translate('Installed', [], 'messages');
                }
            }
        }
        return $this->status;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events = $this->events ?: new ArrayCollection();
    }

    /**
     * Events.
     *
     * @param Collection $events
     * @return Module
     */
    public function setEvents(Collection $events): Module
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /**
     * Notifications.
     *
     * @param Collection $notifications
     * @return Module
     */
    public function setNotifications(Collection $notifications): Module
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * __toSting
     * @return string|null
     */
    public function __toSting(): ?string
    {
        return $this->getName() ?? $this->getId();
    }

    /**
     * coreData
     * @return array
     * 12/06/2020 10:49
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/ModuleCoreData.yaml'));
    }

    /**
     * coreData
     * @return array
     * 12/06/2020 10:49
     */
    public function coreDataLinks(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/ModuleCoreLinks.yaml'));
    }
}

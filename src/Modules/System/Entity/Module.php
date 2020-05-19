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
namespace App\Modules\System\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\TranslationHelper;
use App\Modules\Comms\Entity\Notification;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Module
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ModuleRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Module",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @UniqueEntity({"name"})
 * */
class Module implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(4) UNSIGNED AUTO_INCREMENT", options={"comment": "This number is assigned at install, and is only unique to the installation"})
     * @ORM\GeneratedValue
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(name="entry_route")
     */
    private $entryRoute;

    /**
     * @var string|null
     * @ORM\Column(length=12, options={"default": "Core"})
     */
    private $type = 'Core';

    /**
     * @var array
     */
    private static $typeList = ['Core', 'Additional'];

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=10)
     */
    private $category;

    /**
     * @var string|null
     * @ORM\Column(length=6)
     */
    private $version;

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
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Modules\System\Entity\Action", mappedBy="module", orphanRemoval=true)
     */
    private $actions;

    /**
     * @var null|string
     */
    private $status;

    /**
     * @var bool
     */
    private $updateRequired = false;

    /**
     * @var Collection|ModuleUpgrade[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\System\Entity\ModuleUpgrade",mappedBy="module",orphanRemoval=true)
     * @ORM\OrderBy({"version" = "DESC"})
     */
    private $upgradeLogs;

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
        $this->upgradeLogs = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
     * @return Module
     */
    public function setId(?int $id): Module
    {
        $this->id = $id;
        return $this;
    }

    /**
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
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return Module
     */
    public function setActive(?string $active): Module
    {
        $this->active = self::checkBoolean($active);
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
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     * @return Module
     */
    public function setVersion(?string $version): Module
    {
        $this->version = $version;
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
                'route' => SecurityHelper::isRouteAccessible($this->getEntryRoute()) ? $this->getEntryRoute() : null,
                'name' => TranslationHelper::translate($this->getName(), [] , $this->getName()),
                'textDomain' => $this->getName(),
                'category' => $this->getCategory(),
                'type' => $this->getType(),
                'url' => UrlGeneratorHelper::getUrl($this->getEntryRoute()),
            ];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'entryRoute' => $this->entryRoute,
            'type' => $this->getType(),
            'active' => $this->active,
            'category' => $this->category,
            'version' => $this->version,
            'author' => $this->author,
            'url' => $this->url,
            'status' => $this->getStatus(),
            'updateRequired' => $this->isUpdateRequired(),
            'isNotCore' => $this->getType() !== 'Core',
        ];
    }

    /**
     * @return Collection
     */
    public function getActions(): Collection
    {
        if (null === $this->actions)
            $this->actions = new ArrayCollection();

        if ($this->actions instanceof PersistentCollection)
            $this->actions->initialize();

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
                $this->status = TranslationHelper::translate('Installed');
            } else {
                if (false === is_dir($this->getModuleDir() . '/' . str_replace(' ', '-', strtolower($this->getName()))))
                {
                    $this->status = TranslationHelper::translate('Not Installed');
                } else {
                    $installed = $this->getUpgradeLogs()->filter(function($log) {
                        return $log->getVersion() === 'Installation';
                    });
                    if ($this->getUpgradeLogs()->count() === 0 || $installed->count() === 0)
                        $this->status = TranslationHelper::translate('Not Installed');
                    else
                        $this->status = TranslationHelper::translate('Installed');
                }
            }
        }
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isUpdateRequired(): bool
    {
        return $this->updateRequired ? true : false;
    }

    /**
     * UpdateRequired.
     *
     * @param bool $updateRequired
     * @return Module
     */
    public function setUpdateRequired(bool $updateRequired): Module
    {
        $this->updateRequired = $updateRequired;
        return $this;
    }

    /**
     * getUpgradeLogs
     * @return Collection
     */
    public function getUpgradeLogs(): Collection
    {
        return $this->upgradeLogs = $this->upgradeLogs ?: new ArrayCollection();
    }

    /**
     * UpgradeLogs.
     *
     * @param Collection $upgradeLog
     * @return Module
     */
    public function setUpgradeLogs(Collection $upgradeLogs): Module
    {
        $this->upgradeLogs = $upgradeLogs;
        return $this;
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
     * @return Collection
     */
    public function getUpgrades(): Collection
    {
        return $this->upgrades;
    }

    /**
     * Upgrades.
     *
     * @param Collection $upgrades
     * @return Module
     */
    public function setUpgrades(Collection $upgrades): Module
    {
        $this->upgrades = $upgrades;
        return $this;
    }

    /**
     * __toSting
     * @return string|null
     */
    public function __toSting(): ?string
    {
        return $this->getName();
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return 'CREATE TABLE `__prefix__Module` (
                    `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT \'This name should be globally unique preferably, but certainly locally unique\',
                    `description` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `entry_route` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `type` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Core\',
                    `active` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
                    `category` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `version` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `author` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    KEY `category` (`category`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public function coreData(): string
    {
        return '';
    }

}
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
use App\Modules\Security\Entity\Role;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Action
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ActionRepository")
 * @ORM\Table(name="Action",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="moduleName", columns={"name", "module"})},
 *     indexes={@ORM\Index(name="module", columns={"module"})})
 * @UniqueEntity(fields={"name","module"})
 */
class Action implements EntityInterface
{
    CONST VERSION = '20200401';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="actions")
     * @ORM\JoinColumn(name="module",referencedColumnName="id", nullable=false)
     */
    private $module;

    /**
     * @var string|null
     * @ORM\Column(length=50, options={"comment": "The action name should be unqiue to the module that it is related to"})
     */
    private $name;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", columnDefinition="INT(2)")
     */
    private $precedence;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     */
    private $category;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $description;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array", name="route_List")
     */
    private $routeList;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $entryRoute;

    /**
     * @var string
     * @ORM\Column(length=1,options={"default": "Y"})
     */
    private $entrySidebar = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1,options={"default": "Y"})
     */
    private $menuShow = 'Y';

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $role;

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
     * @return Action
     */
    public function setId(?string $id): Action
    {
        $this->id = $id;
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
     * @return Action
     */
    public function setModule(?Module $module): Action
    {
        $this->module = $module;
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
     * getDisplayName
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        if (null === $this->getName())
            return null;
        $name = explode('_', $this->name);
        return $name[0];
    }

    /**
     * @param string|null $name
     * @return Action
     */
    public function setName(?string $name): Action
    {
        $this->name = mb_substr($name, 0, 50);
        return $this;
    }

    /**
     * getTranslatedName
     * @return string|null
     */
    public function getTranslatedName(): ?string
    {
        if (null === $this->getName())
            return null;
        $name = explode('_', $this->getName());
        $domain = $this->getModule() ? str_replace(' ', '', $this->getModule()->getName()) : 'messages';
        return TranslationHelper::translate($name[0], [], $domain);
    }

    /**
     * @return int|null
     */
    public function getPrecedence(): ?int
    {
        return $this->precedence;
    }

    /**
     * @param int|null $precedence
     * @return Action
     */
    public function setPrecedence(?int $precedence): Action
    {
        $this->precedence = $precedence;
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
     * @return Action
     */
    public function setCategory(?string $category): Action
    {
        $this->category = mb_substr($category, 0, 20);
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
     * @return Action
     */
    public function setDescription(?string $description): Action
    {
        $this->description = mb_substr($description, 0, 255);
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteList(): array
    {
        if (is_string($this->routeList))
            $this->routeList = [$this->routeList];
        return $this->routeList ?: [];
    }

    /**
     * RouteList.
     *
     * @param array|null $RouteList
     * @return Action
     */
    public function setRouteList(?array $RouteList): Action
    {
        $this->routeList = $RouteList;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getentryRoute(): ?string
    {
        return $this->entryRoute;
    }

    /**
     * setentryRoute
     * @param string|null $entryRoute
     * @return Action
     */
    public function setentryRoute(?string $entryRoute): Action
    {
        $this->entryRoute = mb_substr($entryRoute, 0, 255);
        return $this;
    }

    /**
     * isEntrySidebar
     * @return bool
     */
    public function isEntrySidebar(): bool
    {
        return $this->getEntrySidebar() === 'Y';
    }

    /**
     * @return string
     */
    public function getEntrySidebar(): string
    {
        return self::checkBoolean($this->entrySidebar);
    }

    /**
     * @param string $entrySidebar
     * @return Action
     */
    public function setEntrySidebar(string $entrySidebar): Action
    {
        $this->entrySidebar = self::checkBoolean($entrySidebar);
        return $this;
    }

    /**
     * @return string
     */
    public function getMenuShow(): string
    {
        return $this->menuShow;
    }

    /**
     * @param string $menuShow
     * @return Action
     */
    public function setMenuShow(string $menuShow): Action
    {
        $this->menuShow = in_array($menuShow, self::getBooleanList()) ? $menuShow : 'Y';
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRole(): ?array
    {
        return $this->role;
    }

    /**
     * Role.
     *
     * @param array|null $role
     * @return Action
     */
    public function setRole(?array $role): Action
    {
        $this->role = $role;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        if ($name === 'module_menu') {
            return [
                'category' => $this->getCategory(),
                'moduleName' => $this->getModule()->getName(),
                'actionName' => $this->getName(),
                'type' => $this->getModule()->getType(),
                'precedence' => $this->getPrecedence(),
                'moduleEntry' => $this->getModule()->getEntryRoute(),
                'entryRoute' => $this->getentryRoute(),
                'routeList' => $this->getRouteList(),
                'name' => $this->getDisplayName(),
            ];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'translatedName' => $this->getTranslatedName(),
            'precedence' => $this->precedence,
            'category' => $this->category,
            'routeList' => $this->routeList,
            'entryRoute' => $this->entryRoute,
            'entrySidebar' => $this->entrySidebar,
            'menuShow' => $this->menuShow,
            'module' => $this->getModule() ? $this->getModule()->getId() : null,
        ];
    }

    /**
     * getRouteName
     * @param String $module
     * @param string $action
     * @return string
     */
    public static function getRouteName(String $module, string $action)
    {
        return strtolower(str_replace(' ', '_',  $module). '__' . $action);
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE IF NOT EXISTS `__prefix__Action` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(50) NOT NULL COMMENT 'The action name should be unique to the module that it is related to',
                    `precedence` int(2) DEFAULT NULL,
                    `category` CHAR(20) NOT NULL,
                    `description` CHAR(191) NOT NULL,
                    `route_list` longtext NOT NULL COMMENT '(DC2Type:simple_array)',
                    `entry_route` CHAR(191) NOT NULL,
                    `entry_sidebar` CHAR(1) NOT NULL DEFAULT 'Y',
                    `menu_show` CHAR(1) NOT NULL DEFAULT 'Y',
                    `role` CHAR(32) DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                    `module` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `moduleName` (`name`,`module`),
                    KEY `module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Action`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

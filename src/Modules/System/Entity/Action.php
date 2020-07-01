<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: __prefix__
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Action
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ActionRepository")
 * @ORM\Table(name="Action",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="module_restriction_name", columns={"name","restriction","module"}),
 *     @ORM\UniqueConstraint(name="entry_route_precedence",columns={"entry_route","precedence"})},
 *     indexes={@ORM\Index(name="module", columns={"module"})})
 * @UniqueEntity({"name","restriction","module"})
 * @UniqueEntity({"entryRoute","precedence"})
 */
class Action extends AbstractEntity
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
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="actions")
     * @ORM\JoinColumn(name="module",referencedColumnName="id", nullable=false)
     */
    private $module;

    /**
     * @var string|null
     * @ORM\Column(length=50, options={"comment": "The action name and restriction should be unique to the module that it is related to"})
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=50,nullable=true)
     */
    private $restriction;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",nullable=true)
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
     * @var SecurityRole[]|Collection
     * @ORM\ManyToMany(targetEntity="App\Modules\Security\Entity\SecurityRole")
     * @ORM\JoinTable(name="ActionSecurityRole",
     *      joinColumns={@ORM\JoinColumn(name="action",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="security_role",referencedColumnName="id")}
     *  )
     */
    private $securityRoles;

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
     * 11/06/2020 08:46
     */
    public function getDisplayName(): ?string
    {
        return $this->getName();
    }

    /**
     * getFullName
     * @return string|null
     * 11/06/2020 08:46
     */
    public function getFullName(): ?string
    {
        return $this->getRestriction() ? $this->getName() . '_' . $this->getRestriction() : $this->getName();
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
     * @return string|null
     */
    public function getRestriction(): ?string
    {
        return $this->restriction;
    }

    /**
     * @param string|null $restriction
     * @return Action
     */
    public function setRestriction(?string $restriction): Action
    {
        $this->restriction = $restriction;
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
     * isMenuShow
     * @return bool
     * 21/06/2020 10:34
     */
    public function isMenuShow(): bool
    {
        return $this->getMenuShow() === 'Y';
    }

    /**
     * getMenuShow
     * @return string
     * 21/06/2020 10:34
     */
    public function getMenuShow(): string
    {
        return self::checkBoolean($this->menuShow);
    }

    /**
     * setMenuShow
     * @param string $menuShow
     * @return $this
     * 21/06/2020 10:34
     */
    public function setMenuShow(string $menuShow): Action
    {
        $this->menuShow = self::checkBoolean($menuShow);
        return $this;
    }

    /**
     * getSecurityRoles
     * @return Collection
     * 30/06/2020 10:17
     */
    public function getSecurityRoles(): Collection
    {
        if ($this->securityRoles === null) {
            $this->securityRoles = new ArrayCollection();
        }

        if ($this->securityRoles instanceof PersistentCollection) {
            $this->securityRoles->initialize();
        }

        return $this->securityRoles;
    }

    /**
     * getSecurityRolesAsStrings
     * @return array
     * 30/06/2020 11:10
     */
    public function getSecurityRolesAsStrings(): array
    {
        $result = [];
        foreach($this->getSecurityRoles() as $role) {
            $result[] = $role->getRole();
        }
        return $result;
    }

    /**
     * @param SecurityRole[]|Collection $securityRoles
     * @return Action
     */
    public function setSecurityRoles(?Collection $securityRoles): Action
    {
        $this->securityRoles = $securityRoles;
        return $this;
    }

    /**
     * addSecurityRole
     * @param SecurityRole|null $role
     * @return $this
     * 30/06/2020 10:17
     */
    public function addSecurityRole(?SecurityRole $role): Action
    {
        if ($role !== null && $this->getSecurityRoles()->contains($role)) {
            return $this;
        }

        $this->securityRoles->add($role);

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
                'actionName' => $this->getFullName(),
                'type' => $this->getModule()->getType(),
                'precedence' => $this->getPrecedence(),
                'moduleEntry' => $this->getModule()->getEntryRoute(),
                'entryRoute' => $this->getentryRoute(),
                'routeList' => $this->getRouteList(),
                'name' => $this->getDisplayName(),
            ];
        }
        if ($name === 'actionPermissions') {
            $roles = implode(', ', $this->getSecurityRolesAsStrings());
            return [
                'id' => $this->getId(),
                'name' => $this->getName(),
                'restriction' => $this->getRestriction(),
                'description' => $this->getDescription(),
                'category' => $this->getCategory(),
                'roles' => $roles ?: TranslationHelper::translate('Full Access', [], 'Security')
            ];
        }
        if ($name === 'buildContent') {
            return [
                'name' => $this->name,
                'category' => $this->category,
                'description' => $this->getDescription(),
                'routeList' => $this->routeList,
                'entryRoute' => $this->entryRoute,
                'entrySidebar' => $this->entrySidebar,
                'menuShow' => $this->menuShow,
                'precedence' => $this->precedence,
                'restriction' => $this->restriction
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
                    `name` VARCHAR(50) NOT NULL COMMENT 'The action name and restriction should be unique to the module that it is related to',
                    `restriction` VARCHAR(50) DEFAULT NULL,
                    `precedence` smallint DEFAULT NULL,
                    `category` VARCHAR(20) NOT NULL,
                    `description` VARCHAR(191) NOT NULL,
                    `route_list` longtext NOT NULL COMMENT '(DC2Type:simple_array)',
                    `entry_route` VARCHAR(191) NOT NULL,
                    `entry_sidebar` VARCHAR(1) NOT NULL DEFAULT 'Y',
                    `menu_show` VARCHAR(1) NOT NULL DEFAULT 'Y',
                    `module` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `module_restriction_name` (`name`,`restriction`,`module`),
                    UNIQUE KEY `entry_route_precedence` (`entry_route`,`precedence`),
                    KEY `module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
                "CREATE TABLE __prefix__ActionSecurityRole (
                    `action` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `security_role` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    INDEX `action` (`action`), 
                    INDEX `security_role` (`security_role`), 
                    PRIMARY KEY(`action`, `security_role`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Action`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);
                ALTER TABLE `__prefix__ActionSecurityRole` 
                    ADD CONSTRAINT FOREIGN KEY (`action`) REFERENCES `__prefix__Action` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`security_role`) REFERENCES `__prefix__SecurityRole` (`id`);";
    }

    /**
     * coreData
     * @return array
     * 12/06/2020 10:16
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents('ActionCoreData.yaml'));
    }

    /**
     * coreData
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * isArrayField
     * @param string $name
     * @return bool
     */
    public function isArrayField(string $name): bool
    {
         return in_array($name, ['securityRoles','routeList']);
    }

    /**
     * coreDataLinks
     * @return mixed
     * 12/06/2020 10:16
     */
    public function coreDataLinks()
    {
        return Yaml::parse(file_get_contents('ActionCoreLinks.yaml'));
    }
}

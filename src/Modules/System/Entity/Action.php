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
 * @ORM\Table(options={"auto_increment": 1}, name="Action",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="moduleName", columns={"name", "module"})},
 *     indexes={@ORM\Index(name="module", columns={"module"})})
 * @UniqueEntity(fields={"name","module"})
 */
class Action implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(7) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @ORM\Column(length=191, name="entry_route")
     */
    private $entryRoute;

    /**
     * @var string
     * @ORM\Column(length=1, name="entrySidebar", options={"default": "Y"})
     */
    private $entrySidebar = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="menuShow", options={"default": "Y"})
     */
    private $menuShow = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="defaultPermissionAdmin", options={"default": "N"})
     */
    private $defaultPermissionAdmin = 'N';

    /**
     * @var string
     * @ORM\Column(length=1, name="defaultPermissionTeacher", options={"default": "N"})
     */
    private $defaultPermissionTeacher = 'N';

    /**
     * @var string
     * @ORM\Column(length=1, name="defaultPermissionStudent", options={"default": "N"})
     */
    private $defaultPermissionStudent = 'N';

    /**
     * @var string
     * @ORM\Column(length=1, name="defaultPermissionParent", options={"default": "N"})
     */
    private $defaultPermissionParent = 'N';

    /**
     * @var string
     * @ORM\Column(length=1, name="defaultPermissionSupport", options={"default": "N"})
     */
    private $defaultPermissionSupport = 'N';

    /**
     * @var string
     * @ORM\Column(length=1, name="categoryPermissionStaff", options={"default": "Y"})
     */
    private $categoryPermissionStaff = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="categoryPermissionStudent", options={"default": "Y"})
     */
    private $categoryPermissionStudent = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="categoryPermissionParent", options={"default": "Y"})
     */
    private $categoryPermissionParent = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="categoryPermissionOther", options={"default": "Y"})
     */
    private $categoryPermissionOther = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=32,nullable=true)
     */
    private $role;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Action
     */
    public function setId(?int $id): Action
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
     * @return string
     */
    public function getDefaultPermissionAdmin(): string
    {
        return $this->defaultPermissionAdmin;
    }

    /**
     * @param string $defaultPermissionAdmin
     * @return Action
     */
    public function setDefaultPermissionAdmin(string $defaultPermissionAdmin): Action
    {
        $this->defaultPermissionAdmin = self::checkBoolean($defaultPermissionAdmin, 'N');
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPermissionTeacher(): string
    {
        return $this->defaultPermissionTeacher;
    }

    /**
     * @param string $defaultPermissionTeacher
     * @return Action
     */
    public function setDefaultPermissionTeacher(string $defaultPermissionTeacher): Action
    {
        $this->defaultPermissionTeacher = self::checkBoolean($defaultPermissionTeacher, 'N');
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPermissionStudent(): string
    {
        return $this->defaultPermissionStudent;
    }

    /**
     * @param string $defaultPermissionStudent
     * @return Action
     */
    public function setDefaultPermissionStudent(string $defaultPermissionStudent): Action
    {
        $this->defaultPermissionStudent = self::checkBoolean($defaultPermissionStudent, 'N');
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPermissionParent(): string
    {
        return $this->defaultPermissionParent;
    }

    /**
     * @param string $defaultPermissionParent
     * @return Action
     */
    public function setDefaultPermissionParent(string $defaultPermissionParent): Action
    {
        $this->defaultPermissionParent = self::checkBoolean($defaultPermissionParent, 'N');
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPermissionSupport(): string
    {
        return $this->defaultPermissionSupport;
    }

    /**
     * @param string $defaultPermissionSupport
     * @return Action
     */
    public function setDefaultPermissionSupport(string $defaultPermissionSupport): Action
    {
        $this->defaultPermissionSupport = self::checkBoolean($defaultPermissionSupport, 'N');
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryPermissionStaff(): string
    {
        return $this->categoryPermissionStaff;
    }

    /**
     * @param string $categoryPermissionStaff
     * @return Action
     */
    public function setCategoryPermissionStaff(string $categoryPermissionStaff): Action
    {
        $this->categoryPermissionStaff = self::checkBoolean($categoryPermissionStaff, 'Y');
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryPermissionStudent(): string
    {
        return $this->categoryPermissionStudent;
    }

    /**
     * @param string $categoryPermissionStudent
     * @return Action
     */
    public function setCategoryPermissionStudent(string $categoryPermissionStudent): Action
    {
        $this->categoryPermissionStudent = self::checkBoolean($categoryPermissionStudent, 'Y');
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryPermissionParent(): string
    {
        return $this->categoryPermissionParent;
    }

    /**
     * @param string $categoryPermissionParent
     * @return Action
     */
    public function setCategoryPermissionParent(string $categoryPermissionParent): Action
    {
        $this->categoryPermissionParent = self::checkBoolean($categoryPermissionParent, 'Y');
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryPermissionOther(): string
    {
        return $this->categoryPermissionOther;
    }

    /**
     * @param string $categoryPermissionOther
     * @return Action
     */
    public function setCategoryPermissionOther(string $categoryPermissionOther): Action
    {
        $this->categoryPermissionOther = self::checkBoolean($categoryPermissionOther, 'Y');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * Role.
     *
     * @param string|null $role
     * @return Action
     */
    public function setRole(?string $role): Action
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
            'defaultPermissionAdmin' => $this->defaultPermissionAdmin,
            'defaultPermissionTeacher' => $this->defaultPermissionTeacher,
            'defaultPermissionStudent' => $this->defaultPermissionStudent,
            'defaultPermissionParent' => $this->defaultPermissionParent,
            'defaultPermissionSupport' => $this->defaultPermissionSupport,
            'categoryPermissionStaff' => $this->categoryPermissionStaff,
            'categoryPermissionStudent' => $this->categoryPermissionStudent,
            'categoryPermissionParent' => $this->categoryPermissionParent,
            'categoryPermissionOther' => $this->categoryPermissionOther,
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
    public function create(): string
    {
        return "CREATE TABLE IF NOT EXISTS `__prefix__Action` (
                    `id` int(7) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'The action name should be unique to the module that it is related to',
                    `precedence` int(2) DEFAULT NULL,
                    `category` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `description` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `route_list` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
                    `entryRoute` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `entrySidebar` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `menuShow` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `defaultPermissionAdmin` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `defaultPermissionTeacher` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `defaultPermissionStudent` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `defaultPermissionParent` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `defaultPermissionSupport` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `categoryPermissionStaff` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `categoryPermissionStudent` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `categoryPermissionParent` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `categoryPermissionOther` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `role` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `module` int(4) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `moduleName` (`name`,`module`) USING BTREE,
                    KEY `module` (`module`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Action`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }
}
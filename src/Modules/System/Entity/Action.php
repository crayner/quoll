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
use App\Manager\Traits\BooleanList;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
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

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="actions")
     * @ORM\JoinColumn(name="module",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private Module $module;

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
     * @ORM\Column(type="simple_array", name="route_list")
     */
    private $routeList;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $entryRoute;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": "1"})
     */
    private $entrySidebar = true;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": "1"})
     */
    private $menuShow = true;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $securityRoles;

    /**
     * getId
     *
     * 2/09/2020 09:05
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
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
    public function getEntryRoute(): ?string
    {
        return $this->entryRoute;
    }

    /**
     * setentryRoute
     * @param string|null $entryRoute
     * @return Action
     */
    public function setEntryRoute(?string $entryRoute): Action
    {
        $this->entryRoute = mb_substr($entryRoute, 0, 255);
        return $this;
    }

    /**
     * @return bool
     */
    public function isEntrySidebar(): bool
    {
        return (bool)$this->entrySidebar;
    }

    /**
     * @param bool|null $entrySidebar
     * @return Action
     */
    public function setEntrySidebar(?bool $entrySidebar): Action
    {
        $this->entrySidebar = (bool)$entrySidebar;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMenuShow(): bool
    {
        return (bool)$this->menuShow;
    }

    /**
     * @param bool|null $menuShow
     * @return Action
     */
    public function setMenuShow(?bool $menuShow): Action
    {
        $this->menuShow = (bool)$menuShow;
        return $this;
    }

    /**
     * @return array
     */
    public function getSecurityRoles(): array
    {
        if (null === $this->securityRoles) {
            $this->securityRoles = [];
        }

        return $this->securityRoles;
    }

    /**
     * @param array|null $securityRoles
     * @return Action
     */
    public function setSecurityRoles(?array $securityRoles): Action
    {
        $this->securityRoles = $securityRoles;
        return $this;
    }

    /**
     * addSecurityRole
     * @param string|null $role
     * @return $this|SecurityRole
     * 4/07/2020 09:13
     */
    public function addSecurityRole(?string $role): Action
    {
        if (null === $role || in_array($role, $this->getSecurityRoles())) {
            return $this;
        }

        $this->securityRoles[] = $role;

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
            $roles = implode(', ', $this->getSecurityRoles());
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
                'name' => $this->getName(),
                'category' => $this->getCategory(),
                'description' => $this->getDescription(),
                'routeList' => ['arrayField' => $this->getRouteList()],
                'entryRoute' => $this->getEntryRoute(),
                'entrySidebar' => $this->isEntrySidebar(),
                'menuShow' => $this->isMenuShow(),
                'precedence' => $this->getPrecedence(),
                'restriction' => $this->getRestriction(),
                'securityRoles' => ['arrayField' => $this->getSecurityRoles()],
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
     * coreData
     * @return array
     * 12/06/2020 10:16
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/ActionCoreData.yaml'));
    }

    /**
     * coreDataLinks
     * @return mixed
     * 12/06/2020 10:16
     */
    public function coreDataLinks()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/ActionCoreLinks.yaml'));
    }
}

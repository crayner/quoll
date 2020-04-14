<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 09:33
 */
namespace App\Modules\Security\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\System\Entity\Action;
use App\Util\TranslationsHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Role
 * @package App\Modules\Security\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Security\Repository\RoleRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Role",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="nameShort", columns={"nameShort"}),
 *     @ORM\UniqueConstraint(name="role", columns={"role"})})
 */
class Role implements EntityInterface
{
    use BooleanList;
    
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(3) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=32, unique=true, nullable=false)
     */
    private $role;

    /**
     * @var string
     * @ORM\Column(length=8, options={"default": "Staff"})
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getCategoryList")
     */
    private $category = 'Staff';

    /**
     * @var string
     * @ORM\Column(length=20, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=4, name="nameShort", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $nameShort;

    /**
     * @var string
     * @ORM\Column(length=60, name="description")
     * @Assert\NotBlank()
     * @Assert\Length(max=60)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(length=12, name="type", options={"default": "Core"})
     * @Assert\Choice(callback="getTypelist")
     */
    private $type = 'Additional';

    /**
     * @var string
     * @ORM\Column(length=1, name="canLoginRole", options={"default": "Y"})
     */
    private $canLoginRole = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="futureYearsLogin", options={"default": "Y"})
     */
    private $futureYearsLogin = 'Y';

    /**
     * @var string
     * @ORM\Column(length=1, name="pastYearsLogin", options={"default": "Y"})
     */
    private $pastYearsLogin = 'Y';

    /**
     * @var string
     * @ORM\Column(length=10, name="restriction", options={"default": "None"})
     * @Assert\Choice(callback="getRestrictionList")
     */
    private $restriction = 'None';

    /**
     * @var Collection|Action[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\System\Entity\Action", mappedBy="roles", cascade={"persist"})
     */
    private $actions;

    /**
     * @return array
     */
    public static function getRestrictionList(): array
    {
        return self::$restrictionList;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return array
     */
    public static function getCategoryList(): array
    {
        return self::$categoryList;
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
     * @return Role
     */
    public function setId(?int $id): Role
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @var array
     */
    private static $categoryList = ['Staff','Student','Parent','Other'];

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category ?: '';
    }

    /**
     * setCategory
     * @param string $category
     * @return Role
     */
    public function setCategory(string $category): Role
    {
        $this->category = in_array($category, self::getCategoryList()) ? $category : 'Staff';
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: '';
    }

    /**
     * setName
     * @param string $name
     * @return Role
     */
    public function setName(string $name): Role
    {
        $this->name = mb_substr($name, 0, 20);
        return $this;
    }

    /**
     * @return string
     */
    public function getNameShort(): string
    {
        return $this->nameShort ?: '';
    }

    /**
     * setNameShort
     * @param string $nameShort
     * @return Role
     */
    public function setNameShort(string $nameShort): Role
    {
        $this->nameShort = mb_substr($nameShort, 0, 4);
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?: '';
    }

    /**
     * setDescription
     * @param string $description
     * @return Role
     */
    public function setDescription(string $description): Role
    {
        $this->description = mb_substr($description, 0, 60);
        return $this;
    }

    /**
     * @var array
     */
    private static $typeList = ['Core', 'Additional'];

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * setType
     * @param string $type
     * @return Role
     */
    public function setType(string $type): Role
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Additional';
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCanLoginRole(): bool
    {
        return $this->canLoginRole === 'Y';
    }

    /**
     * @return string
     */
    public function getCanLoginRole(): string
    {
        return $this->canLoginRole;
    }

    /**
     * setCanLoginRole
     * @param string $canLoginRole
     * @return Role
     */
    public function setCanLoginRole(string $canLoginRole): Role
    {
        $this->canLoginRole = self::checkBoolean($canLoginRole);
        return $this;
    }

    /**
     * @return boolean
     */
    public function isFutureYearsLogin(): bool
    {
        return $this->futureYearsLogin === 'Y';
    }

    /**
     * @return string
     */
    public function getFutureYearsLogin(): string
    {
        return self::checkBoolean($this->futureYearsLogin);
    }

    /**
     * setFutureYearsLogin
     * @param string $futureYearsLogin
     * @return Role
     */
    public function setFutureYearsLogin(string $futureYearsLogin): Role
    {
        $this->futureYearsLogin = self::checkBoolean($futureYearsLogin);
        return $this;
    }

    /**
     * @return boolean
     */
    public function isPastYearsLogin(): bool
    {
        return $this->pastYearsLogin === 'Y';
    }

    /**
     * @return string
     */
    public function getPastYearsLogin(): string
    {
        return self::checkBoolean($this->pastYearsLogin);
    }

    /**
     * setPastYearsLogin
     * @param string $pastYearsLogin
     * @return Role
     */
    public function setPastYearsLogin(string $pastYearsLogin): Role
    {
        $this->pastYearsLogin = self::checkBoolean($pastYearsLogin);
        return $this;
    }

    /**
     * @var array
     */
    private static $restrictionList = ['None', 'Same Role', 'Admin Only'];

    /**
     * @return string
     */
    public function getRestriction(): string
    {
        return $this->restriction;
    }

    /**
     * setRestriction
     * @param string $restriction
     * @return Role
     */
    public function setRestriction(string $restriction): Role
    {
        $this->restriction = in_array($restriction, self::getRestrictionList()) ? $restriction : 'None';
        return $this;
    }

    /**
     * @return Collection|Action[]|null
     */
    public function getActions(): Collection
    {
        if ($this->actions === null)
            $this->actions = new ArrayCollection();

        if ($this->actions instanceof PersistentCollection)
            $this->actions->initialize();

        return $this->actions;
    }

    /**
     * Permissions.
     *
     * @param Collection|Action[]|null $actions
     * @return Role
     */
    public function setActions(?Collection $actions)
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * addAction
     * @param Action $action
     * @param bool $mirror
     * @return Role
     */
    public function addAction(Action $action, bool $mirror = true): Role
    {
        if ($this->getActions()->contains($action))
            return $this;

        if ($mirror)
            $action->addRole($this, false);

        $this->actions->add($action);

        return $this;
    }

    /**
     * removeAction
     * @param Action $action
     * @param bool $mirror
     * @return Role
     */
    public function removeAction(Action $action, bool $mirror = true): Role
    {
        if ($mirror)
            $action->removeRole($this, false);

        $this->getActions()->removeElement($action);
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        return [
            'category' => TranslationsHelper::translate($this->getCategory()),
            'name' => $this->getName(),
            'name_short' => $this->getNameShort(),
            'description' => $this->getDescription(),
            'type' => TranslationsHelper::translate($this->getType()),
            'login_years' => TranslationsHelper::translate($this->getLoginYearsDescription()),
            'isAdditional' => $this->getType() !== 'Core',
        ];
    }

    /**
     * getLoginYearsDescription
     * @return string
     */
    public function getLoginYearsDescription(): string
    {
        if (!$this->isCanLoginRole()) {
            return 'None';
        } elseif ($this->isFutureYearsLogin() && $this->isPastYearsLogin()) {
            return 'All years';
        } elseif (!$this->isFutureYearsLogin() && !$this->isPastYearsLogin()) {
            return 'Current year only';
        } elseif (!$this->isFutureYearsLogin()) {
            return 'Current/past years only';
        } elseif (!$this->isPastYearsLogin()) {
            return 'Current/future years only';
        }
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId() ?: '';
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__Role` (
                    `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `role` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `category` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Staff',
                    `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameShort` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `description` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `type` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Core',
                    `canLoginRole` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `futureYearsLogin` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `pastYearsLogin` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `restriction` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'None',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `nameShort` (`nameShort`),
                    UNIQUE KEY `role` (`role`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;                ";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return "INSERT INTO `__prefix__Role` ('role', `category`, `name`, `nameShort`, `description`, `type`, `canLoginRole`, `futureYearsLogin`, `pastYearsLogin`, `restriction`) VALUES
                    ('ROLE_SYSTEM_ADMIN', 'Staff', 'Administrator', 'Adm', 'Controls all aspects of the system', 'Core', 'Y', 'Y', 'Y', 'Admin Only'),
                    ('ROLE_TEACHER', 'Staff', 'Teacher', 'Tcr', 'Regular, classroom teacher', 'Core', 'Y', 'Y', 'Y', 'None'),
                    ('ROLE_STUDENT', 'Student', 'Student', 'Std', 'Person studying in the school', 'Core', 'Y', 'Y', 'Y', 'None'),
                    ('ROLE_PARENT', 'Parent', 'Parent', 'Prt', 'Parent or guardian of person studying in', 'Core', 'Y', 'Y', 'Y', 'None'),
                    ('ROLE_SUPPORT_STAFF', 'Staff', 'Support Staff', 'SSt', 'Staff who support teaching and learning', 'Core', 'Y', 'Y', 'Y', 'None'),
                    ('ROLE_LIBRARIAN', 'Staff', 'Librarian', 'LIB', 'Library Staff', 'Core', 'Y', 'N', 'N', 'Admin Only');";
    }
}
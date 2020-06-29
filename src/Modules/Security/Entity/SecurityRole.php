<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: __prefix__
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 29/06/2020
 * Time: 09:17
 */
namespace App\Modules\Security\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SecurityRoles
 * @package App\Modules\Security\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Security\Repository\SecurityRoleRepository")
 * @ORM\Table(name="SecurityRole",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="role",columns={"role"})}
 *     )
 * @UniqueEntity("role")
 */
class SecurityRole extends AbstractEntity
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
     * @ORM\Column(length=63)
     * @Assert\NotBlank()
     */
    private $role;

    /**
     * @var string
     * @ORM\Column(length=63)
     * @Assert\NotBlank()
     */
    private $label;

    /**
     * @var string
     * @ORM\Column(length=63)
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getCategoryList")
     */
    private $category;

    /**
     * @var string[]
     */
    private static $categoryList = [
        'Staff',
        'Parent',
        'Student',
        'Contact',
        'System',
    ];

    /**
     * @var SecurityRole[]|Collection|null
     * @ORM\ManyToMany(targetEntity="App\Modules\Security\Entity\SecurityRole", cascade={"all"})
     * @ORM\JoinTable(name="SecurityRoleChildren",
     *      joinColumns={@ORM\JoinColumn(name="parent",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="child",referencedColumnName="id")}
     *      )
     */
    private $childRoles;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $allowLogin;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $allowFutureYears;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $allowPastYears;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return SecurityRole
     */
    public function setId(?string $id): SecurityRole
    {
        $this->id = $id;
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
     * @param string $role
     * @return SecurityRole
     */
    public function setRole(string $role): SecurityRole
    {
        $this->role = strtoupper($role);
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return SecurityRole
     */
    public function setLabel(string $label): SecurityRole
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return SecurityRole
     */
    public function setCategory(string $category): SecurityRole
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getCategoryList(): array
    {
        return self::$categoryList;
    }

    /**
     * @param string[] $categoryList
     */
    public static function setCategoryList(array $categoryList): void
    {
        self::$categoryList = $categoryList;
    }

    /**
     * @return Collection|SecurityRole[]|null
     */
    public function getChildRoles(): Collection
    {
        if ($this->childRoles === null) {
            $this->setChildRoles(new ArrayCollection());
        }

        if ($this->childRoles instanceof PersistentCollection) {
            $this->childRoles->initialize();
        }

        return $this->childRoles;
    }

    /**
     * @param Collection|SecurityRole[]|null $childRoles
     * @return SecurityRole
     */
    public function setChildRoles($childRoles)
    {
        $this->childRoles = $childRoles;
        return $this;
    }

    /**
     * addChildRole
     * @param SecurityRole|null $role
     * @return $this
     * 29/06/2020 09:38
     */
    public function addChildRole(?SecurityRole $role) 
    {
        if ($role === null || $this->getChildRoles()->contains($role)) {
            return $this;
        }
        
        $this->childRoles->set($role->getRole(), $role);
        
        return $this;
    }

    /**
     * getChildRolesAsString
     * @return string
     * 29/06/2020 09:43
     */
    public function getChildRolesAsString(bool $useHierachy = false)
    {
        $result = '';
        if ($useHierachy) {
            foreach(SecurityHelper::getAssignableRoleNames([$this->getRole()]) as $role) {
                $result .= $role . ', ';
            }
        } else {
            foreach ($this->getChildRoles() as $role) {
                $result .= $role->getRole() . ', ';
            }
        }
        return rtrim($result, ', ');
    }

    /**
     * @return bool
     */
    public function isAllowLogin(): bool
    {
        return (bool) $this->allowLogin;
    }

    /**
     * @param bool $allowLogin
     * @return SecurityRole
     */
    public function setAllowLogin(bool $allowLogin): SecurityRole
    {
        $this->allowLogin = $allowLogin;
        if (!$allowLogin) {
            return $this->setAllowFutureYears(false)->setAllowPastYears(false);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowFutureYears(): bool
    {
        return (bool) $this->allowFutureYears;
    }

    /**
     * @param bool $allowFutureYears
     * @return SecurityRole
     */
    public function setAllowFutureYears(bool $allowFutureYears): SecurityRole
    {
        $this->allowFutureYears = $allowFutureYears;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowPastYears(): bool
    {
        return (bool) $this->allowPastYears;
    }

    /**
     * @param bool $allowPastYears
     * @return SecurityRole
     */
    public function setAllowPastYears(bool $allowPastYears): SecurityRole
    {
        $this->allowPastYears = $allowPastYears;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 29/06/2020 09:40
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'role' => $this->getRole(),
            'category' => TranslationHelper::translate('securityrole.category.' . strtolower($this->getCategory()), [], 'Security'),
            'label' => $this->getLabel(),
            'children' => $this->getChildRolesAsString(true),
            'future_years' => TranslationHelper::translate($this->isAllowFutureYears() ? 'Yes' : 'No', [], 'messages'),
            'past_years' => TranslationHelper::translate($this->isAllowPastYears() ? 'Yes' : 'No', [], 'messages'),
            'login' => TranslationHelper::translate($this->isAllowLogin() ? 'Yes' : 'No', [], 'messages'),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * create
     * @return array|string[]
     * 29/06/2020 10:06
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__SecurityRole` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `role` VARCHAR(63) NOT NULL, 
                    `label` VARCHAR(63) NOT NULL,
                    `category` VARCHAR(63) NOT NULL, 
                    `allow_login` TINYINT(1) NOT NULL, 
                    `allow_future_years` TINYINT(1) NOT NULL, 
                    `allow_past_years` TINYINT(1) NOT NULL, 
                    PRIMARY KEY(`id`),
                    UNIQUE KEY `role` (`role`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;",
                "CREATE TABLE `__prefix__SecurityRoleChildren` (
                     `parent` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                     `child` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                     INDEX `parent` (`parent`), 
                     INDEX `child` (`child`), 
                     PRIMARY KEY(`parent`, `child`)
                ) DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 29/06/2020 10:07
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__SecurityRoleChildren`
                    ADD CONSTRAINT FOREIGN KEY (`parent`) REFERENCES `__prefix__SecurityRole` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`child`) REFERENCES `__prefix__SecurityRole` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 29/06/2020 10:07
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * canDelete
     * @return bool
     * 29/06/2020 10:32
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(SecurityRole::class)->canDelete($this);
    }
}
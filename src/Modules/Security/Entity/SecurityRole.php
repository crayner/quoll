<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
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
use App\Modules\Security\Manager\RoleHierarchy;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

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
    private ?string $id = null;

    /**
     * @var string|null
     * @ORM\Column(length=63)
     * @Assert\NotBlank()
     */
    private ?string $role;

    /**
     * @var string|null
     * @ORM\Column(length=63)
     * @Assert\NotBlank()
     */
    private ?string $label;

    /**
     * @var string|null
     * @ORM\Column(length=63)
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getCategoryList")
     */
    private ?string $category;

    /**
     * @var SecurityRole[]|Collection|null
     * @ORM\ManyToMany(targetEntity="App\Modules\Security\Entity\SecurityRole")
     * @ORM\JoinTable(name="SecurityRoleChildren",
     *      joinColumns={@ORM\JoinColumn(name="parent",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="child",referencedColumnName="id")}
     *      )
     */
    private ?Collection $childRoles = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private bool $allowLogin = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private bool $allowFutureYears = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default": 0})
     */
    private bool $allowPastYears = false;

    /**
     * SecurityRole constructor.
     */
    public function __construct()
    {
        $this->setChildRoles(new ArrayCollection());
    }

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
        return RoleHierarchy::getCategoryList();
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
     * @param bool $useHierachy
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
        if ($name === 'buildContent') {
            return [
                'role' => $this->getRole(),
                'label' => $this->getLabel(),
                'category' => $this->getCategory(),
                'allow_future_years' => $this->isAllowFutureYears(),
                'allow_past_years' => $this->isAllowPastYears(),
                'allow_login' => $this->isAllowLogin(),
            ];
        }
        return [
            'id' => $this->getId(),
            'role' => $this->getRole(),
            'category' => TranslationHelper::translate('securityrole.category.' . strtolower($this->getCategory()), [], 'Security'),
            'label' => $this->getLabel(),
            'children' => $this->getChildRolesAsString(false),
            'future_years' => TranslationHelper::translate($this->isAllowFutureYears() ? 'Yes' : 'No', [], 'messages'),
            'past_years' => TranslationHelper::translate($this->isAllowPastYears() ? 'Yes' : 'No', [], 'messages'),
            'login' => TranslationHelper::translate($this->isAllowLogin() ? 'Yes' : 'No', [], 'messages'),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * coreData
     * @return array
     * 22/07/2020 08:43
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/SecurityRoleCoreData.yaml'));
    }

    /**
     * coreData
     * @return array
     * 22/07/2020 08:43
     */
    public function coreDataLinks(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/SecurityRoleCoreLinks.yaml'));
    }

    /**
     * canDelete
     *
     * 19/08/2020 09:57
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(SecurityRole::class)->canDelete($this);
    }
}
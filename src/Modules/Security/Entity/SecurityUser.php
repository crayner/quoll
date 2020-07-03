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
 * Date: 1/07/2020
 * Time: 10:25
 */
namespace App\Modules\Security\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SecurityUser
 * @package App\Modules\Security\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Security\Repository\SecurityUserRepository")
 * @ORM\Table(name="SecurityUser",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="person",columns={"person"}),
 *     @ORM\UniqueConstraint(name="username",columns={"username"})}
 * )
 * @UniqueEntity("person")
 * @UniqueEntity("username")
 */
class SecurityUser extends AbstractEntity implements UserInterface, EncoderAwareInterface, EquatableInterface, \Serializable
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
     * @var Person
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",inversedBy="securityUser",fetch="EAGER")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=64)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $canLogin;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default": 0, "comment": "Force user to reset password on next login."})
     */
    private $passwordForceReset;

    /**
     * @var string|null
     * @ORM\Column(length=15,nullable=true,name="last_ip_address")
     */
    private $lastIPAddress;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",nullable=true)
     */
    private $lastTimestamp;

    /**
     * @var string|null
     * @ORM\Column(length=15,nullable=true,name="last_fail_ip_address")
     */
    private $lastFailIPAddress;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",nullable=true)
     */
    private $lastFailTimestamp;

    /**
     * @var integer
     * @ORM\Column(type="smallint", options={"default": "0"})
     */
    private $failCount = 0;

    /**
     * @var SecurityRole[]|Collection
     * @ORM\ManyToMany(targetEntity="App\Modules\Security\Entity\SecurityRole",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinTable(name="SecurityUserRole",
     *      joinColumns={@ORM\JoinColumn(name="security_user",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="security_role",referencedColumnName="id")}
     *  )
     */
    private $securityRoles;

    /**
     * @var string|null
     * @ORM\Column(length=191,name="google_api_refresh_token",nullable=true)
     */
    private $googleAPIRefreshToken;

    /**
     * SecurityUser constructor.
     * @param Person $person
     */
    public function __construct(?Person $person = null)
    {
        $this->setPerson($person);
        $this->setSecurityRoles(new ArrayCollection());
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
     * @return SecurityUser
     */
    public function setId(?string $id): SecurityUser
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * @param Person $person
     * @return SecurityUser
     */
    public function setPerson(Person $person, bool $reflect = true): SecurityUser
    {
        $this->person = $person;
        if ($reflect && $person instanceof Person) {
            $person->setSecurityUser($this, false);
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     * @return SecurityUser
     */
    public function setUsername(?string $username): SecurityUser
    {
        $this->username = $username;
        return $this;
    }

    /**
     * hasUsername
     * @param string $username
     * @return bool
     * 3/07/2020 10:20
     */
    public function hasUsername(string $username): bool
    {
        if ($this->getUsername() === $username) {
            return true;
        }

        if (SecurityHelper::useEmailAsUsername()) {
            if ($this->getPerson() instanceof Person && $this->getPerson()->getContact() instanceof Contact && $this->getPerson()->getContact()->getEmail() === $username) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return SecurityUser
     */
    public function setPassword(?string $password): SecurityUser
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCanLogin(): bool
    {
        return $this->canLogin;
    }

    /**
     * @param bool $canLogin
     * @return SecurityUser
     */
    public function setCanLogin(bool $canLogin): SecurityUser
    {
        $this->canLogin = $canLogin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPasswordForceReset(): bool
    {
        return $this->passwordForceReset;
    }

    /**
     * @param bool $passwordForceReset
     * @return SecurityUser
     */
    public function setPasswordForceReset(bool $passwordForceReset): SecurityUser
    {
        $this->passwordForceReset = $passwordForceReset;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastIPAddress(): ?string
    {
        return $this->lastIPAddress;
    }

    /**
     * @param string|null $lastIPAddress
     * @return SecurityUser
     */
    public function setLastIPAddress(?string $lastIPAddress): SecurityUser
    {
        $this->lastIPAddress = $lastIPAddress;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastTimestamp(): ?\DateTimeImmutable
    {
        return $this->lastTimestamp;
    }

    /**
     * @param \DateTimeImmutable|null $lastTimestamp
     * @return SecurityUser
     */
    public function setLastTimestamp(?\DateTimeImmutable $lastTimestamp): SecurityUser
    {
        $this->lastTimestamp = $lastTimestamp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastFailIPAddress(): ?string
    {
        return $this->lastFailIPAddress;
    }

    /**
     * @param string|null $lastFailIPAddress
     * @return SecurityUser
     */
    public function setLastFailIPAddress(?string $lastFailIPAddress): SecurityUser
    {
        $this->lastFailIPAddress = $lastFailIPAddress;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastFailTimestamp(): ?\DateTimeImmutable
    {
        return $this->lastFailTimestamp;
    }

    /**
     * @param \DateTimeImmutable|null $lastFailTimestamp
     * @return SecurityUser
     */
    public function setLastFailTimestamp(?\DateTimeImmutable $lastFailTimestamp): SecurityUser
    {
        $this->lastFailTimestamp = $lastFailTimestamp;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailCount(): int
    {
        return $this->failCount;
    }

    /**
     * @param int $failCount
     * @return SecurityUser
     */
    public function setFailCount(int $failCount): SecurityUser
    {
        $this->failCount = $failCount;
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
     * 30/06/2020 11:04
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
     * setSecurityRoles
     * @param Collection|null $securityRoles
     * @return $this
     * 1/07/2020 12:07
     */
    public function setSecurityRoles(?Collection $securityRoles): SecurityUser
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
    public function addSecurityRole(?SecurityRole $role): SecurityUser
    {
        if ($role !== null && $this->getSecurityRoles()->contains($role)) {
            return $this;
        }

        $this->securityRoles->add($role);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGoogleAPIRefreshToken(): ?string
    {
        return $this->googleAPIRefreshToken;
    }

    /**
     * setGoogleAPIRefreshToken
     * @param string|null $googleAPIRefreshToken
     * @return $this
     * 2/07/2020 09:28
     */
    public function setGoogleAPIRefreshToken(?string $googleAPIRefreshToken): SecurityUser
    {
        $this->googleAPIRefreshToken = mb_substr($googleAPIRefreshToken, 0, 191);
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * create
     * @return array|string[]
     * 1/07/2020 10:46
     */
    public function create(): array
    {
        return [
            "CREATE TABLE `__prefix__SecurityUser` (
                `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                `person` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                `username` VARCHAR(64) NOT NULL, password VARCHAR(191) NOT NULL, 
                `can_login` TINYINT(1) NOT NULL, 
                `password_force_reset` TINYINT(1) DEFAULT '0' NOT NULL, 
                `last_ip_address` VARCHAR(15) DEFAULT NULL, 
                `last_timestamp` DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
                `last_fail_ip_address` VARCHAR(15) DEFAULT NULL, 
                `last_fail_timestamp` DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
                `fail_count` SMALLINT DEFAULT 0 NOT NULL,
                UNIQUE INDEX `person` (`person`), 
                UNIQUE INDEX `username` (`username`)
                PRIMARY KEY(`id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;",
            "CREATE TABLE `__prefix__SecurityUserRole` (
                    `security_user` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `security_role` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    INDEX `security_user` (`security_user`), 
                    INDEX `security_role` (`security_role`), 
                    PRIMARY KEY(`security_user`, `security_role`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 1/07/2020 10:46
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__SecurityUser` 
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);
                ALTER TABLE `__prefix__SecurityUserRole`
                    ADD CONSTRAINT FOREIGN KEY (`security_user`) REFERENCES `__prefix__SecurityUser` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`security_role`) REFERENCES `__prefix__SecurityRole` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 1/07/2020 10:46
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }


    /**
     * serialize
     * @return string
     * 1/07/2020 13:08
     */
    public function serialize()
    {
        return serialize(array(
            $this->getId(),
            $this->getUsername(),
        ));
    }

    /**
     * unserialize
     * @param string $serialized
     * 3/07/2020 09:54
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            ) = unserialize($serialized);

        $su = ProviderFactory::create(SecurityUser::class)->loadUserByUsername($this->username);

        $this->setPerson($su->getPerson())
            ->setCanLogin($su->isCanLogin())
            ->setPasswordForceReset($su->isPasswordForceReset())
            ->setFailCount($su->getFailCount())
            ->setLastIPAddress($su->getLastIPAddress())
            ->setLastFailIPAddress($su->getLastFailIPAddress())
            ->setLastTimestamp($su->getLastTimestamp())
            ->setLastFailTimestamp($su->getLastFailTimestamp())
            ->setPassword($su->getPassword())
            ->setGoogleAPIRefreshToken($su->getGoogleAPIRefreshToken())
            ->setSecurityRoles($su->getSecurityRoles());
    }

    /**
     * getEncoderName
     * @return string|null
     * 1/07/2020 13:05
     */
    public function getEncoderName()
    {
        return 'native';
    }

    /**
     * isEqualTo
     * @param UserInterface $user
     * @return bool
     * 1/07/2020 13:13
     */
    public function isEqualTo(UserInterface $user)
    {
        return $user->getId() === $this->getId();
    }

    /**
     * getRoles
     * @return array|string[]
     * 1/07/2020 13:06
     */
    public function getRoles()
    {
        return $this->getSecurityRolesAsStrings();
    }

    /**
     * getSalt
     * @return string|null
     * 1/07/2020 13:06
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * eraseCredentials
     * 1/07/2020 13:13
     */
    public function eraseCredentials()
    {
        ProviderFactory::create(SecurityUser::class)->setEntity(null);
    }

    /**
     * getEmail
     * @return string|null
     * 3/07/2020 11:15
     */
    public function getEmail(): ?string
    {
        if($this->getPerson() && $this->getPerson()->getContact()) {
            return $this->getPerson()->getContact()->getEmail();
        }
        return null;
    }

    /**
     * getLocale
     * @return string
     * 3/07/2020 11:20
     */
    public function getLocale(): string
    {
        $locale = null;
        if ($this->getPerson() && $this->getPerson()->isStaff()) {
            $locale = $this->getPerson()->getStaff()->getLocale() ? $this->getPerson()->getStaff()->getLocale()->getCode() : null;
        } elseif ($this->getPerson() && $this->getPerson()->isStudent()) {
            $locale = $this->getPerson()->getStaff()->getLocale() ? $this->getPerson()->getStudent()->getLocale()->getCode() : null;
        } elseif ($this->getPerson() && $this->getPerson()->isParent()) {
            $locale = $this->getPerson()->getStaff()->getLocale() ? $this->getPerson()->getParent()->getLocale()->getCode() : null;
        }
        return $locale ?: ParameterBagHelper::get('locale');
    }
}
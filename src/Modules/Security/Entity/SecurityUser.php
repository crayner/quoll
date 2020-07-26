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
 * Date: 1/07/2020
 * Time: 10:25
 */
namespace App\Modules\Security\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Entity\Locale;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
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
 *     uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person",columns={"person"}),
 *      @ORM\UniqueConstraint(name="username",columns={"username"})
 *     },
 *     indexes={@ORM\Index(name="locale",columns={"locale"})}
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
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",inversedBy="securityUser",cascade={"persist"})
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
     */
    private $password;

    /**
     * @var Locale|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Locale")
     * @ORM\JoinColumn(name="locale",nullable=true)
     */
    private $locale;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private $canLogin = true;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",options={"default": 0, "comment": "Force user to reset password on next login."})
     */
    private $passwordForceReset = false;

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
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $failCount = 0;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array")
     */
    private $securityRoles;

    /**
     * @var string|null
     * @ORM\Column(length=191,name="google_api_refresh_token",nullable=true)
     */
    private $googleAPIRefreshToken;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $superUser = false;

    /**
     * SecurityUser constructor.
     * @param Person $person
     */
    public function __construct(?Person $person = null)
    {
        $this->setPerson($person);
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
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * setPerson
     * @param Person|null $person
     * @param bool $reflect
     * @return $this
     * 4/07/2020 11:24
     */
    public function setPerson(?Person $person, bool $reflect = true): SecurityUser
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
        if (empty($username)) {
            if ($this->getPerson() && $this->getPerson()->getContact() && ($email = $this->getPerson()->getContact()->getEmail())) {
                $username = $email;
            }
        }
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
     * @return array|null
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
     * @return SecurityUser
     */
    public function setSecurityRoles(?array $securityRoles): SecurityUser
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
    public function addSecurityRole(?string $role): SecurityRole
    {
        if (null === $role || in_array($role, $this->getSecurityRoles())) {
            return $this;
        }

        $this->securityRoles[] = $role;

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
        return $this->getSecurityRoles();
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
     * @return Locale|null
     */
    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    /**
     * @param Locale|null $locale
     * @return SecurityUser
     */
    public function setLocale(?Locale $locale): SecurityUser
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * getCareGiver
     * @return CareGiver|null
     * 22/07/2020 13:55
     */
    public function getCareGiver(): ?CareGiver
    {
        return $this->getPerson() ? $this->getPerson()->getCareGiver() : null ;
    }

    /**
     * getStudent
     * @return Student|null
     * 11/07/2020 12:44
     */
    public function getStudent(): ?Student
    {
        return $this->getPerson() ? $this->getPerson()->getStudent() : null ;
    }

    /**
     * getStaff
     * @return Staff|null
     * 11/07/2020 13:41
     */
    public function getStaff(): ?Staff
    {
        return $this->getPerson() ? $this->getPerson()->getStaff() : null ;
    }

    /**
     * hasRole
     * @param string $role
     * @return bool
     * 16/07/2020 09:44
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getSecurityRoles());
    }

    /**
     * @return bool
     */
    public function isSuperUser(): bool
    {
        return $this->superUser;
    }

    /**
     * @param bool $superUser
     * @return SecurityUser
     */
    public function setSuperUser(bool $superUser): SecurityUser
    {
        $this->superUser = $superUser;
        return $this;
    }

    /**
     * changePassword
     * @param string $password
     * @return bool
     * 26/07/2020 12:32
     */
    public function changePassword(string $password): bool
    {
        $this->setPassword($password);

        $em = ProviderFactory::getEntityManager();
        $em->persist($this);
        $em->flush();
        
        return true;
    }
}

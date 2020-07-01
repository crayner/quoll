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
 * Date: 12/02/2019
 * Time: 16:20
 */
namespace App\Modules\Security\Manager;

use App\Modules\People\Entity\Person;
use App\Modules\People\Repository\PersonRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class SecurityUserProvider
 * @package App\Modules\Security\Manager
 */
class __SecurityUserProvider implements UserProviderInterface
{

    /**
     * @var integer|null
     */
    private $refresh;

    /**
     * @var SecurityUser|null
     */
    private $securityUser;

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username): UserInterface
    {
        if (null === ($user = $this->userRepository->loadUserByUsernameOrEmail($username))) {
            throw new BadCredentialsException(sprintf('No user found for "%s"', $username));
        }

        $this->setUser($user);
        // create the DTO and feed it with the entity
        $this->securityUser = new SecurityUser($user);

        return $this->getSecurityUser();
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException  if the user is not supported
     * @throws UsernameNotFoundException if the user is not found
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($this->supportsClass(get_class($user)) && $this->getSecurityUser() && $this->getSecurityUser()->isEqualTo($user))
            return $this->getUser();
        if (! $this->supportsClass(get_class($user)))
            throw new UnsupportedUserException(sprintf('The user provided was not valid.'));
        if ($user instanceof UserInterface)
           $this->loadUserByUsername($user->getUsername());
        if ($user->isEqualTo($this->getSecurityUser()))
            $this->refresh = $user->getId();
        return $this->getSecurityUser();
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return $class === SecurityUser::class;
    }

    /**
     * @var Person|null
     */
    private $user;

    /**
     * getUser
     * @return Person|null
     */
    public function getUser(): ?Person
    {
        return $this->user = $this->user ?: $this->loadUser();
    }

    /**
     * @param Person|null $user
     * @return SecurityUserProvider
     */
    public function setUser(?Person $user): SecurityUserProvider
    {
        $this->user = $user;
        return $this;
    }

    /**
     * loadUser
     * @return Person|null
     */
    private function loadUser(): ?Person
    {
        $person = $this->getSecurityUser() ? $this->getUserRepository()->find( $this->getSecurityUser()->getId()) : null;
        $this->setUser($person);
        return $this->user;
    }

    /**
     * @var PersonRepository|null
     */
    private $userRepository;

    /**
     * @return PersonRepository|null
     */
    public function getUserRepository(): ?PersonRepository
    {
        return $this->userRepository;
    }

    /**
     * @param PersonRepository|null $userRepository
     * @return SecurityUserProvider
     */
    public function setUserRepository(?PersonRepository $userRepository): SecurityUserProvider
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    /**
     * SecurityUserProvider constructor.
     * @param PersonRepository $repository
     */
    public function __construct(PersonRepository $repository)
    {
        $this->userRepository = $repository;
    }

    /**
     * @return SecurityUser|null
     */
    public function getSecurityUser(): ?SecurityUser
    {
        return $this->securityUser;
    }

    /**
     * @param SecurityUser|null $securityUser
     * @return SecurityUserProvider
     */
    public function setSecurityUser(?SecurityUser $securityUser): SecurityUserProvider
    {
        $this->securityUser = $securityUser;
        return $this;
    }
}
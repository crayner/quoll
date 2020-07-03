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
 * Time: 11:59
 */
namespace App\Modules\Security\Provider;

use App\Manager\EntityInterface;
use App\Modules\Security\Entity\SecurityUser;
use App\Provider\AbstractProvider;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class SecurityUserProvider
 * @package App\Modules\Security\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityUserProvider extends AbstractProvider implements UserProviderInterface
{
    /**
     * @var string
     */
    protected $entityName = SecurityUser::class;

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username): ?UserInterface
    {
        if ($this->getSession()->has('person') && ($user = $this->getSession()->get('person')) instanceof SecurityUser) {
            if ($user->getUsername() !== $username) {
                $this->setEntity($this->getRepository()->loadUserByUsernameOrEmail($username));
            } else {
                $this->setEntity($user);
            }
        } else {
            $this->setEntity($this->getRepository()->loadUserByUsernameOrEmail($username));
        }
        return $this->getEntity();
    }

    /**
     * refreshUser
     * @param UserInterface $user
     * @return EntityInterface|object|UserInterface|null
     * 1/07/2020 13:00\
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('The user provided was not valid.'));
        }

        $this->loadUserByUsername($user->getUsername());
        try {
            $this->refresh();
        } catch (ORMInvalidArgumentException $e) {
            $this->setEntity($this->getRepository()->find($user->getId()));
        }
        return $this->getEntity();
    }

    /**
     * supportsClass
     * @param string $class
     * @return bool
     * 1/07/2020 12:53
     */
    public function supportsClass(string $class)
    {
        return $class === SecurityUser::class;
    }
}

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
 * Date: 15/08/2020
 * Time: 08:11
 */
namespace App\Modules\Security\Manager;

use App\Modules\Security\Entity\SecurityUser;
use App\Provider\ProviderFactory;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class SecurityUserProvider
 * @package App\Modules\Security\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityUserProvider implements UserProviderInterface
{
    /**
     * SecurityUserProvider constructor.
     * @param ProviderFactory $factory
     */
    public function __construct(ProviderFactory $factory)
    {

    }

    /**
     * __call
     * 15/08/2020 08:12
     * @param $name
     * @param $args
     * @return
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        $provider = ProviderFactory::create(SecurityUser::class);

        if (method_exists($provider,$name)) {
            if (count($args) === 3) {
                return $provider->$name($args[0],$args[1],$args[2]);
            } else if (count($args) === 2) {
                return $provider->$name($args[0],$args[1]);
            } else if (count($args) === 1) {
                return $provider->$name($args[0]);
            }
            return $provider->$name();
        }
        throw new InvalidArgumentException(sprintf('The %s does not have a method called %s.', \App\Modules\Security\Provider\SecurityUserProvider::class, $name));
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username
     * @return UserInterface
     *
     * @throws \Exception
     */
    public function loadUserByUsername(string $username)
    {
        return ProviderFactory::create(SecurityUser::class)->loadUserByUsername($username);
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     * @return UserInterface
     *
     * @throws \Exception
     */
    public function refreshUser(UserInterface $user)
    {
        return ProviderFactory::create(SecurityUser::class)->refreshUser($user);

    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     * @return bool
     * @throws \Exception
     */
    public function supportsClass(string $class)
    {
        return ProviderFactory::create(SecurityUser::class)->supportsClass($class);

    }

}

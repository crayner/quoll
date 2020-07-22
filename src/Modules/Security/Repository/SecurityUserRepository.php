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
 * Time: 10:27
 */
namespace App\Modules\Security\Repository;

use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class SecurityUserRepository
 * @package App\Modules\Security\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityUserRepository extends ServiceEntityRepository
{
    /**
     * @var array
     */
    private $users = [];

    /**
     * SecurityUserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityUser::class);
    }

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return SecurityUser|null
     */
    public function loadUserByUsernameOrEmail(string $username): ?SecurityUser
    {
        if (key_exists($username, $this->users)) {
            return $this->users[$username];
        }
        if (SecurityHelper::useEmailAsUsername()) {
            try {
                $user = $this->createQueryBuilder('u')
                    ->select(['u', 'p', 'c'])
                    ->leftJoin('u.person', 'p')
                    ->leftJoin('p.contact', 'c')
                    ->where('(c.email = :username OR u.username = :username)')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getOneOrNullResult();
            } catch (NonUniqueResultException $e) {
                return null;
            }
        } else {
            try {
                $user = $this->createQueryBuilder('u')
                    ->select(['u', 'p'])
                    ->leftJoin('u.person', 'p')
                    ->where('u.username = :username')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getOneOrNullResult();
            } catch (NonUniqueResultException $e) {
                return null;
            }
        }

        if ($user) $this->users[$user->getId()] = $user;

        return $this->users[$username] = $user;
    }

    /**
     * find
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return mixed|object|null
     * 17/07/2020 13:22
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if (key_exists($id, $this->users)) {
            return $this->users[$id];
        }
        $this->users[$id] = parent::find($id, $lockMode, $lockVersion);
        if (null === $this->users[$id]) {
            unset($this->users[$id]);
            return null;
        }
        return $this->users[$id];
    }
}


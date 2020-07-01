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
        try {
            return $this->createQueryBuilder('u')
                ->select(['u','p'])
                ->leftJoin('u.person', 'p')
                ->where('p.email = :username OR u.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

}

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
 * Time: 09:18
 */
namespace App\Modules\Security\Repository;

use App\Modules\Security\Entity\SecurityRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class SecurityRolesRepository
 * @package App\Modules\Security
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityRoleRepository extends ServiceEntityRepository
{
    /**
     * SecurityRolesRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityRole::class);
    }

    /**
     * countRoleUseAsChild
     * @param SecurityRole $role
     * @return int
     * 29/06/2020 13:25
     */
    public function countRoleUseAsChild(SecurityRole $role): int
    {
        try {
            return intval($this->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->join('r.childRoles', 'c')
                ->where('c.id = :role')
                ->setParameter('role', $role)
                ->distinct()
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * findByCategoryAsStrings
     * @param string $category
     * @param bool $returnEntity
     * @return array
     * 1/07/2020 08:20
     */
    public function findByCategoryAsStrings(string $category, bool $returnEntity = false): array
    {
        $select = 'r.role';
        if ($returnEntity) {
            $select = 'r';
        }
        return $this->createQueryBuilder('r')
            ->select([$select])
            ->orderBy('r.role','ASC')
            ->where('r.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }
}

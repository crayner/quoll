<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 16/10/2019
 * Time: 14:31
 */

namespace App\Modules\System\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use App\Modules\System\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Modules\System\Entity\ModuleUpgrade;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ModuleUpgradeRepository
 * @package App\Modules\System\Repository
 */
class ModuleUpgradeRepository extends ServiceEntityRepository
{
    /**
     * ModuleUpgradeRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleUpgrade::class);
    }

    /**
     * deleteModuleRecords
     * @param Module $module
     * @return mixed
     */
    public function deleteModuleRecords(Module $module)
    {
        return $this->createQueryBuilder('mu')
            ->delete()
            ->where('mu.module = :module')
            ->setParameter('module', $module)
            ->getQuery()
            ->execute();
    }

    /**
     * hasModuleVersion
     * @param Module $module
     * @param string $version
     * @return bool
     */
    public function hasModuleVersion(Module $module, string $version): bool
    {
        try {
            return $this->createQueryBuilder('u')
                    ->where('u.module = :module')
                    ->andWhere('u.version = :version')
                    ->select(['COUNT(u.id)'])
                    ->setParameters(['module' => $module, 'version' => $version])
                    ->getQuery()
                    ->getSingleScalarResult() > 0;
        } catch (NoResultException | NonUniqueResultException | ORMInvalidArgumentException $e) {
            return false;
        }
    }
}

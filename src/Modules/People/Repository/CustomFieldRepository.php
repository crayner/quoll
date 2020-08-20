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
 * Date: 17/05/2020
 * Time: 12:20
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\CustomField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CustomFieldRepository
 * @package App\Modules\People\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CustomFieldRepository extends ServiceEntityRepository
{
    /**
     * CustomFieldRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomField::class);
    }

    /**
     * countByCategory
     *
     * 20/08/2020 08:59
     * @param string $category
     * @return int
     */
    public function countByCategory(string $category): int
    {
        try {
            return intval($this->createQueryBuilder('f')
                ->select(['COUNT(f.id)'])
                ->where('f.categories LIKE :category')
                ->andWhere('f.active = :true')
                ->setParameter('category', '%'.$category.'%')
                ->setParameter('true', true)
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * findByCategoryUsage
     * @param string $category
     * @param string $usage
     * @return array
     * 29/07/2020 14:40
     */
    public function findByCategoryUsage(string $category, string $usage = ''): array
    {
        $result = $this->createQueryBuilder('f')
            ->where('f.categories LIKE :category')
            ->setParameter('category', '%'.$category.'%');

        if ($usage === 'applicationForm') {
            $result
                ->andWhere('f.applicationForm = :true')
                ->setParameter('true', true);
        }
        if ($usage === 'publicRegistrationForm') {
            $result
                ->andWhere('f.publicRegistrationForm = :true')
                ->setParameter('true', true);
        }
        if ($usage === 'dataUpdate') {
            $result
                ->andWhere('f.dataUpdater = :true')
                ->setParameter('true', true);
        }

        return $result
            ->andWhere('f.active = :active')
            ->setParameter('active', true)
            ->orderBy('f.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

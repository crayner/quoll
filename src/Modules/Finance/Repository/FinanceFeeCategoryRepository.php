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
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Finance\Repository;

use App\Modules\Finance\Entity\FinanceFeeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FinanceFeeCategoryRepository
 * @package App\Modules\Finance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FinanceFeeCategoryRepository extends ServiceEntityRepository
{
    /**
     * FinanceFeeCategoryRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinanceFeeCategory::class);
    }
}

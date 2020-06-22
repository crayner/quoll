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

use App\Modules\Finance\Entity\FinanceBillingSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FinanceBillingScheduleRepository
 * @package App\Modules\Finance\Repository
 */
class FinanceBillingScheduleRepository extends ServiceEntityRepository
{
    /**
     * FinanceBillingScheduleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinanceBillingSchedule::class);
    }
}

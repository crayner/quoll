<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/06/2020
 * Time: 13:57
 */
namespace App\Modules\Planner\Repository;

use App\Modules\Planner\Entity\PlannerEntryGuest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PlannerEntryGuestRepository
 * @package App\Modules\Planner\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PlannerEntryGuestRepository extends ServiceEntityRepository
{
/**
 * ApplicationFormRepository constructor.
 * @param ManagerRegistry $registry
 */
public function __construct(ManagerRegistry $registry)
{
    parent::__construct($registry, PlannerEntryGuest::class);
}

}
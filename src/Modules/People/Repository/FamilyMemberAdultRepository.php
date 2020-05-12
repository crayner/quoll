<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 12/05/2020
 * Time: 08:54
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\FamilyMemberAdult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyMemberAdultRepository
 * @package App\Modules\People\Repository
 */
class FamilyMemberAdultRepository extends ServiceEntityRepository
{
    /**
     * FamilyChildRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyMemberAdult::class);
    }
}
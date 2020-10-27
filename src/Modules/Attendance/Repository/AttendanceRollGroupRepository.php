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
 * Date: 17/10/2020
 * Time: 09:43
 */
namespace App\Modules\Attendance\Repository;

use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Modules\RollGroup\Entity\RollGroup;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AttendanceRollGroupRepository
 *
 * 17/10/2020 09:44
 * @package App\Modules\Attendance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRollGroupRepository extends ServiceEntityRepository
{
    /**
     * AttendanceRollGroupRepository constructor.
     *
     * 17/10/2020 09:44
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceRollGroup::class);
    }
}

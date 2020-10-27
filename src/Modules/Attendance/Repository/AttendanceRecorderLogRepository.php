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
 * Date: 27/10/2020
 * Time: 08:26
 */
namespace App\Modules\Attendance\Repository;

use App\Modules\Attendance\Entity\AttendanceRecorderLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AttendanceRecorderLogRepository
 *
 * 27/10/2020 08:30
 * @package App\Modules\Attendance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRecorderLogRepository extends ServiceEntityRepository
{
    /**
     * AttendanceRollGroupRepository constructor.
     *
     * 17/10/2020 09:44
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceRecorderLog::class);
    }
}

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
 * Date: 19/10/2020
 * Time: 12:54
 */
namespace App\Modules\Attendance\Repository;

use App\Modules\Attendance\Entity\AttendanceLogClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AttendanceLogClassRepository
 *
 * 19/10/2020 12:54
 * @package App\Modules\Attendance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceLogClassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceLogClass::class);
    }
}

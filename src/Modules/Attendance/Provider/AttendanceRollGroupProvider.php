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
 * Time: 10:31
 */
namespace App\Modules\Attendance\Provider;

use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Provider\AbstractProvider;

/**
 * Class AttendanceRollGroupProvider
 *
 * 17/10/2020 10:32
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRollGroupProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = AttendanceRollGroup::class;
}

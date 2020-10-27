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
 * Time: 08:30
 */
namespace App\Modules\Attendance\Provider;

use App\Modules\Attendance\Entity\AttendanceRecorderLog;
use App\Provider\AbstractProvider;

/**
 * Class AttendanceRecorderLogProvider
 *
 * 27/10/2020 08:31
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRecorderLogProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = AttendanceRecorderLog::class;
}
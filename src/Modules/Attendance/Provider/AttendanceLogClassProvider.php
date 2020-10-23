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
 * Time: 12:55
 */
namespace App\Modules\Attendance\Provider;

use App\Modules\Attendance\Entity\AttendanceLogClass;
use App\Provider\AbstractProvider;

/**
 * Class AttendanceLogClassProvider
 *
 * 19/10/2020 12:55
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceLogClassProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = AttendanceLogClass::class;
}

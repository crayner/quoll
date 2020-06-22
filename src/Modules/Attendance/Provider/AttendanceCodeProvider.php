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
 * Date: 17/01/2020
 * Time: 14:16
 */
namespace App\Modules\Attendance\Provider;

use App\Provider\AbstractProvider;
use App\Modules\Attendance\Entity\AttendanceCode;

/**
 * Class AttendanceCodeProvider
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceCodeProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = AttendanceCode::class;

    /**
     * canDelete
     * @param AttendanceCode $code
     * @return bool
     */
    public function canDelete(AttendanceCode $code): bool
    {
        return !$code->isActive();
    }
}
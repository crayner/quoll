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
 * Date: 8/08/2020
 * Time: 09:27
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\TimetableDate;
use App\Provider\AbstractProvider;

/**
 * Class TimetableDayDateProvider
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDateProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = TimetableDate::class;
}

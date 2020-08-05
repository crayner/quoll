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
 * Date: 4/08/2020
 * Time: 13:56
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\TimetableColumnPeriod;
use App\Provider\AbstractProvider;

/**
 * Class TimetableColumnPeriodProvider
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableColumnPeriodProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = TimetableColumnPeriod::class;
}

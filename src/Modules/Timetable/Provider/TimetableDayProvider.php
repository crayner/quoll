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
 * Time: 08:31
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\TimetableDay;
use App\Provider\AbstractProvider;

/**
 * Class TimetableDayProvider
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = TimetableDay::class;
}

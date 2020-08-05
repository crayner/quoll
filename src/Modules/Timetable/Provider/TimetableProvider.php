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
 * Date: 3/08/2020
 * Time: 16:11
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\Timetable;
use App\Provider\AbstractProvider;

/**
 * Class TimetableProvider
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Timetable::class;
}

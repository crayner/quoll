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
 * Time: 11:39
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\TimetableColumn;
use App\Provider\AbstractProvider;

/**
 * Class TimetableColumnProvider
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableColumnProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = TimetableColumn::class;
}

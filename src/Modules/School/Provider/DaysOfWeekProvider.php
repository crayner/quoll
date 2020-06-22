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
 * Date: 30/05/2020
 * Time: 10:23
 */
namespace App\Modules\School\Provider;

use App\Modules\School\Entity\DaysOfWeek;
use App\Provider\AbstractProvider;

/**
 * Class DaysOfWeekProvider
 * @package App\Modules\School\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DaysOfWeekProvider extends AbstractProvider
{
    protected $entityName = DaysOfWeek::class;
}
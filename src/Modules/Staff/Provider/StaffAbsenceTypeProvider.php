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
 * Date: 28/05/2020
 * Time: 15:01
 */
namespace App\Modules\Staff\Provider;

use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Provider\AbstractProvider;

/**
 * Class StaffAbsenceTypeProvider
 * @package App\Modules\Staff\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffAbsenceTypeProvider extends AbstractProvider
{
    protected $entityName = StaffAbsenceType::class;
}
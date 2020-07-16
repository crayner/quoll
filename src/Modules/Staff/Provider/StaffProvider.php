<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/07/2019
 * Time: 15:33
 */

namespace App\Modules\Staff\Provider;

use App\Modules\Staff\Entity\Staff;
use App\Provider\AbstractProvider;

/**
 * Class StaffProvider
 * @package App\Modules\Staff\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Staff::class;
}

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

namespace App\Modules\People\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\People\Entity\Staff;
use App\Provider\EntityProviderInterface;

/**
 * Class StaffProvider
 * @package App\Modules\People\Provider
 */
class StaffProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Staff::class;
}
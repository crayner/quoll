<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/01/2020
 * Time: 09:54
 */

namespace App\Modules\School\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\School\Entity\Department;
use App\Provider\EntityProviderInterface;

/**
 * Class DepartmentProvider
 * @package App\Modules\School\Provider
 */
class DepartmentProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Department::class;
}
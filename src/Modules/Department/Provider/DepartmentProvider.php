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
 * Date: 2/01/2020
 * Time: 09:54
 */
namespace App\Modules\Department\Provider;

use App\Modules\Department\Entity\Department;
use App\Modules\People\Entity\Person;
use App\Provider\AbstractProvider;

/**
 * Class DepartmentProvider
 * @package App\Modules\Department\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DepartmentProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Department::class;
}

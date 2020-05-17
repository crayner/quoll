<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 4/12/2019
 * Time: 20:58
 */

namespace App\Modules\People\Provider;

use App\Provider\AbstractProvider;
use App\Modules\People\Entity\Family;

/**
 * Class FamilyProvider
 * @package App\Modules\People\Provider
 */
class FamilyProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = Family::class;
}
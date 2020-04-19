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
 * Date: 6/12/2019
 * Time: 11:29
 */

namespace App\Modules\People\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Modules\People\Entity\FamilyChild;

/**
 * Class FamilyChildProvider
 * @package App\Modules\People\Provider
 */
class FamilyChildProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = FamilyChild::class;
}
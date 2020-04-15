<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 31/12/2019
 * Time: 18:26
 */

namespace App\Modules\School\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Modules\School\Entity\House;

/**
 * Class HouseProvider
 * @package App\Modules\School\Provider
 */
class HouseProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = House::class;

    /**
     * canDelete
     * @param House $house
     * @return bool
     */
    public function canDelete(House $house): bool
    {
        return $house->canDelete();
    }
}
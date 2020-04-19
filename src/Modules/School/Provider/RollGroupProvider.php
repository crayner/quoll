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
 * Date: 27/07/2019
 * Time: 11:04
 */

namespace App\Modules\School\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Modules\School\Entity\RollGroup;

class RollGroupProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = RollGroup::class;

    /**
     * canDelete
     * @param RollGroup $roll
     * @return bool
     */
    public function canDelete(RollGroup $roll): bool
    {
        return $roll->getStudentCount() === 0;
    }
}
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
 * Date: 6/12/2019
 * Time: 07:52
 */

namespace App\Modules\People\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Modules\People\Entity\FamilyRelationship;

/**
 * Class FamilyRelationshipProvider
 * @package App\Modules\People\Provider
 */
class FamilyRelationshipProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = FamilyRelationship::class;

    /**
     * findOneRelationship
     * @param array $item
     * @return FamilyRelationship
     */
    public function findOneRelationship(array $item): FamilyRelationship
    {
        $fr = $this->getRepository()->findOneByFamilyAdultChild($item);

        return $fr ?: new FamilyRelationship();
    }
}
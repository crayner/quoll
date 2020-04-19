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
 * Date: 11/12/2019
 * Time: 13:21
 */

namespace App\Modules\People\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\People\Entity\District;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use App\Provider\EntityProviderInterface;

/**
 * Class DistrictProvider
 * @package App\Modules\People\Provider
 */
class DistrictProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = District::class;

    /**
     * countUsage
     * @param District $district
     * @return int
     */
    public function countUsage(District $district): int
    {
        $result = $this->getRepository(Person::class)->countDistrictUsage($district);
        if ($result > 0)
            return $result;
        $result += $this->getRepository(Family::class)->countDistrictUsage($district);
        return $result;
    }

    /**
     * canDelete
     * @param District|null $district
     * @return bool
     */
    public function canDelete(?District $district = null): bool
    {
        $district = $district ?: $this->getEntity();
        return $this->countUsage($district) === 0;
    }
}


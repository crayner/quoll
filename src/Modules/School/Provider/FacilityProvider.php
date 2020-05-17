<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 4/01/2020
 * Time: 17:29
 */

namespace App\Modules\School\Provider;

use App\Modules\Activities\Entity\ActivitySlot;
use App\Modules\School\Entity\Facility;
use App\Modules\School\Entity\FacilityPerson;
use App\Modules\School\Entity\RollGroup;
use App\Provider\AbstractProvider;

/**
 * Class FacilityProvider
 * @package App\Modules\School\Provider
 */
class FacilityProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = Facility::class;

    /**
     * canDelete
     * @param Facility $facility
     * @return bool
     */
    public function canDelete(Facility $facility): bool
    {
        if ($this->getRepository(RollGroup::class)->countFacility($facility) > 0)
            return false;
        if ($this->getRepository(ActivitySlot::class)->countFacility($facility) > 0)
            return false;
        if ($this->getRepository(FacilityPerson::class)->countFacility($facility) > 0)
            return false;
        return true;
    }
}
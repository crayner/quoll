<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
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

use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Provider\AbstractProvider;
use App\Modules\School\Entity\House;
use App\Provider\ProviderFactory;

/**
 * Class HouseProvider
 * @package App\Modules\School\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HouseProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = House::class;

    /**
     * canDelete
     * @param House $house
     * @return bool
     * 16/07/2020 10:28
     */
    public function canDelete(House $house): bool
    {
        return ProviderFactory::getRepository(Student::class)->countInHouse($house) + ProviderFactory::getRepository(Staff::class)->countInHouse($house) === 0;
    }
}

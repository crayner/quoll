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
 * Date: 6/05/2020
 * Time: 16:21
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use App\Provider\AbstractProvider;

/**
 * Class AddressProvider
 * @package App\Modules\People\Provider
 */
class AddressProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = Address::class;

    /**
     * canDelete
     * @param Address $address
     * @return bool
     */
    public function canDelete(Address $address): bool
    {
        if ($this->getRepository(Family::class)->countAddressUse($address) > 0) {
            return false;
        }
        if ($this->getRepository(Person::class)->countAddressUse($address) > 0) {
            return false;
        }
        return true;
    }
}
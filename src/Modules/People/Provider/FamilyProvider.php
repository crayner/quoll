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
 * Date: 4/12/2019
 * Time: 20:58
 */

namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
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

    /**
     * @var array
     */
    private $phoneList;

    /**
     * isPhoneInFamily
     * @param Phone $phone
     * @return bool
     */
    public function isPhoneInFamily(Phone $phone): bool
    {
        if (is_null($this->phoneList)) {
            $this->phoneList = [];
            foreach($this->getRepository()->findPhoneList() as $item) {
                $this->phoneList[$item['id']] = $item['id'];
            }
        }
        if (key_exists($phone->getId(), $this->phoneList)) {
            return true;
        }
        return false;
    }
}

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
 * Date: 9/05/2020
 * Time: 13:34
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;

/**
 * Class PhoneProvider
 * @package App\Modules\People\Provider
 */
class PhoneProvider extends AbstractProvider
{

    protected $entityName = Phone::class;

    /**
     * getFamilyPhonesOfPerson
     * @param Person $person
     * @return array
     */
    public function getFamilyPhonesOfPerson(Person $person): array
    {
        $result = [];
        foreach($this->getRepository(Family::class)->getFamiliesOfPerson($person) as $family) {
            foreach($family->getFamilyPhones() as $phone) {
                $result[] = $phone;
            }
        }
        array_unique($result);
        return $result;
    }

    /**
     * canDelete
     * @param Phone $phone
     * @return bool
     */
    public function canDelete(Phone $phone): bool
    {
        if (ProviderFactory::create(Contact::class)->isPhoneInContact($phone)) {
            return false;
        }
        if (ProviderFactory::create(Family::class)->isPhoneInFamily($phone)) {
            return false;
        }
        return true;
    }
}
<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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

use App\Manager\Traits\EntityTrait;
use App\Modules\People\Entity\Phone;
use App\Provider\EntityProviderInterface;

/**
 * Class PhoneProvider
 * @package App\Modules\People\Provider
 */
class PhoneProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = Phone::class;

    public function listFamilyPhonesOfPerson(Person $person): string
    {

    }
}
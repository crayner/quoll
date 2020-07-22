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
 * Date: 22/07/2020
 * Time: 15:40
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Phone;
use App\Provider\AbstractProvider;

/**
 * Class ContactProvider
 * @package App\Modules\People\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ContactProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Contact::class;

    /**
     * @var array
     */
    private $phoneList;

    /**
     * isPhoneInContact
     * @param Phone $phone
     * @return bool
     * 22/07/2020 15:42
     */
    public function isPhoneInContact(Phone $phone): bool
    {
        if (is_null($this->phoneList)) {
            $this->phoneList = $this->getRepository()->findPhoneList();
        }
        if (key_exists($phone->getId(), $this->phoneList)) {
            return true;
        }
        return false;
    }

}
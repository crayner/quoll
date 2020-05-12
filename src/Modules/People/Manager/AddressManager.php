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
 * Date: 5/05/2020
 * Time: 19:02
 */
namespace App\Modules\People\Manager;

use App\Manager\SpecialInterface;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Util\StringUtil;

/**
 * Class AddressManager
 * @package App\Modules\People\Manager
 */
class AddressManager implements SpecialInterface
{
    /**
     * @var FormView
     */
    private $addressForm;

    /**
     * @var FormView
     */
    private $localityForm;

    /**
     * @var Address
     */
    private $address;

    /**
     * AddressManager constructor.
     * @param Address $address
     * @param FormView $addressForm
     * @param FormView $localityForm
     */
    public function __construct(Address $address, FormView $addressForm, FormView $localityForm)
    {
        $this->setAddressForm($addressForm);
        $this->setLocalityForm($localityForm);
        $this->setAddress($address);
    }

    /**
     * getName
     * @return string
     */
    public function getName(): string
    {
        return StringUtil::fqcnToBlockPrefix(static::class);
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        return [
            'address_form' => $this->getAddressForm()->vars['toArray'],
            'locality_form' => $this->getLocalityForm()->vars['toArray'],
            'locality_choices' => static::getLocalityChoices(),
            'locality_list' => static::getLocalityList(),
            'name' => $this->getName(),
            'address_id' => $this->getAddress()->getId() > 0 ? $this->getAddress()->getId() : 0,
            'locality_id' => $this->getAddress()->getLocality() ? $this->getAddress()->getLocality()->getId() : 0,
        ];
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * Address.
     *
     * @param Address $address
     * @return AddressManager
     */
    public function setAddress(Address $address): AddressManager
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return FormView
     */
    public function getAddressForm(): FormView
    {
        return $this->addressForm;
    }

    /**
     * AddressForm.
     *
     * @param FormView $addressForm
     * @return AddressManager
     */
    public function setAddressForm(FormView $addressForm): AddressManager
    {
        $this->addressForm = $addressForm;
        return $this;
    }

    /**
     * @return FormView
     */
    public function getLocalityForm(): FormView
    {
        return $this->localityForm;
    }

    /**
     * LocalityForm.
     *
     * @param FormView $localityForm
     * @return AddressManager
     */
    public function setLocalityForm(FormView $localityForm): AddressManager
    {
        $this->localityForm = $localityForm;
        return $this;
    }

    /**
     * getLocalityList
     * @return array
     */
    public static function getLocalityChoices(): array
    {
        return ProviderFactory::create(Locality::class)->buildChoiceList();
    }

    /**
     * getLocalityList
     * @return array
     */
    public static function getLocalityList(): array
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Locality::class)->findBy([],['name' => 'ASC', 'territory' => 'ASC']) as $locality) {
            $result[$locality->getId()] = $locality->toArray('full');
        }
        return $result;
    }
}
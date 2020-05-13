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

use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AddressManager
 * @package App\Modules\People\Manager
 */
class AddressManager
{
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    /**
     * @var string
     */
    private $country;

    /**
     * @var array|null
     */
    private $information;

    /**
     * AddressManager constructor.
     * @param ParameterBagInterface $bag
     */
    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
        $this->country = $bag->get('country');
    }

    /**
     * getPreferredCountries
     * @return array
     */
    public function getPreferredCountries(): array
    {
        if ($this->bag->has('preferred_countries')) {
            $cList = $this->bag->get('preferred_countries');
        } else {
            $cList = [];
        }
        $list = [];
        foreach($cList as $c)
            $list[Countries::getAlpha3Name($c)] = $c;
        return $list;
    }

    /**
     * isPostCodeHere
     * @param string $position
     * @param string|null $country
     * @return bool
     */
    public function isPostCodeHere(string $position, ?string $country = null): bool
    {
        if (null === $this->getCountry($country)) {
            return true;
        }
        $data = $this->getCountryInformation($this->getCountry($country));
        return in_array($data['postcode']['location'], [$position, 'both']);
    }

    /**
     * getAddressInformation
     * @return array
     */
    protected function getInformation(): array
    {
        if (null === $this->information) {
            $this->information = Yaml::parse(file_get_contents(__DIR__ . '/../../../../config/information/address.yaml'));
        }
        return $this->information;
    }

    /**
     * hasCountryInformation
     * @param string $country
     * @return bool
     */
    protected function hasCountryInformation(string $country): bool
    {
        return key_exists($country, $this->getInformation());
    }

    /**
     * getCountryInformation
     * @param string $country
     * @return mixed
     */
    protected function getCountryInformation(string $country): array
    {
        return $this->getInformation()[$country];
    }

    /**
     * parseCountry
     * @param string $country
     * @return array
     */
    protected function parseCountry(string $country): array
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults(
            [
                'postcode' => [],
            ]
        );

        $data = $resolver->resolve($this->hasCountryInformation($country) ? $this->getCountryInformation($country) : []);
        $resolver->clear();
        $resolver->setDefaults(
            [
                'location' => 'both',
                'validation' => '',
                'style' => null,
            ]
        );
        $resolver->addAllowedValues('location', ['both','street','locality']);
        $resolver->addAllowedTypes('validation', ['string']);
        $resolver->addAllowedTypes('style', ['null','string']);

        $data['postcode'] = $resolver->resolve($data['postcode']);
        return $data;
    }

    /**
     * getCountry
     * @param string|null $country
     * @return string|null
     */
    public function getCountry(?string $country): ?string
    {
        return $country ?: $this->country;
    }

    /**
     * isValidPostCode
     * @param Address|Locality $entity
     * @return bool
     */
    public function isValidPostCode($entity) : bool
    {
        dump($entity);
        if ($entity instanceof Address) {
            $reg = $this->getPostcodeValidation($entity->getLocality()->getCountry());
            if ($reg === '') {
                return true;
            }
            return ($entity->getPostCode() === null && $entity->getLocality()->getPostCode() === null) || preg_match($reg, $entity->getPostCode() . $entity->getLocality()->getPostCode()) > 0;
        }
        if ($entity instanceof Locality) {
            $reg = $this->getPostcodeValidation($entity->getCountry());
            if ($reg === '') {
                return true;
            }
            return $entity->getPostCode() === null || preg_match($reg, $entity->getPostCode()) > 0;
        }
        return false;
    }

    /**
     * getPostCodeValidation
     * @param string $code
     * @return string
     */
    protected function getPostCodeValidation(string $code): string
    {
        if (!$this->hasCountryInformation($code)) {
            return '';
        }
        $data = $this->getCountryInformation($code);
        return $data['postcode']['validation'];
    }
}
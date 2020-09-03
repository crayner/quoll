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
 * Date: 5/05/2020
 * Time: 19:02
 */
namespace App\Modules\People\Manager;

use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Modules\System\Manager\SettingFactory;
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
        $this->country = SettingFactory::getSettingManager()->get('System', 'country');
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
        foreach($cList as $c) {
            $c = strtoupper($c);
            $list[Countries::getAlpha3Name($c)] = $c;
        }
        return $list;
    }

    /**
     * isPostCodeHere
     * @param string $position
     * @param string|null $country
     * @param bool $strict
     * @return bool
     */
    public function isPostCodeHere(string $position, ?string $country = null, bool $strict = false): bool
    {
        if (null === $this->getCountry($country) && !$strict) {
            return true;
        }
        $data = $this->getCountryInformation($this->getCountry($country));

        return in_array($data['postcode']['location'], [$position, $strict ? '' : 'both']);
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
                'style' => '',
            ]
        );
        $resolver->addAllowedTypes('style', ['string']);
        $resolver->addAllowedTypes('postcode', ['array']);

        $data = $resolver->resolve($this->hasCountryInformation($country) ? $this->getCountryInformation($country) : []);
        $resolver->clear();
        $resolver->setDefaults(
            [
                'location' => 'both',
                'validation' => '',
                'format' => [],
            ]
        );
        $resolver->addAllowedValues('location', ['both','street','locality']);
        $resolver->addAllowedTypes('validation', ['string']);
        $resolver->addAllowedTypes('format', ['array']);

        $data['postcode'] = $resolver->resolve($data['postcode']);

        $resolver->clear();
        $resolver->setDefaults(
            [
                'match' => null,
                'template' => null,
            ]
        );
        $resolver->addAllowedTypes('match', ['null','string']);
        $resolver->addAllowedTypes('template', ['null','string']);

        $data['postcode']['format'] = $resolver->resolve($data['postcode']['format']);

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
        $data = $this->parseCountry($code);
        return $data['postcode']['validation'];
    }

    /**
     * getPostCodeFormat
     * @param string $code
     * @return array|null
     */
    protected function getPostCodeFormat(string $code): ?array
    {
        if (!$this->hasCountryInformation($code)) {
            return null;
        }
        $data = $this->parseCountry($code);
        return $data['postcode']['format'];
    }

    /**
     * formatPostCode
     * @param Address|Locality $entity
     * @return string|null
     */
    public function formatPostCode($entity): ?string
    {
        $country = null;
        if ($entity instanceof Address) {
            if (!$entity->getLocality()) {
                return $entity->getPostCode();
            }
            $country = $entity->getLocality()->getCountry();
            if (null === $country) {
                return $entity->getPostCode().$entity->getLocality()->getPostCode();
            }
            $postCode = null;
            if ($this->isPostCodeHere('street', $country, true)) {
                $postCode = $entity->getPostCode();
            } else if ($this->isPostCodeHere('locality', $country, true)) {
                $postCode = $entity->getLocality()->getPostCode();
            }
            if (null === $postCode || '' === $postCode) {
                return null;
            }
            return $this->getFormattedPostCode($country, $postCode);

        }
        $country = null;
        if ($entity instanceof Locality) {
            $country = $entity->getCountry();
            if (null === $country) {
                return $entity->getPostCode();
            }
            $postCode = null;
            if ($this->isPostCodeHere('locality', $country, true)) {
                $postCode = $entity->getPostCode();
            }
            if (null === $postCode || '' === $postCode) {
                return null;
            }
            return $this->getFormattedPostCode($country, $postCode);

        }

        throw new \TypeError(sprintf('Only an %s or %s are accepted.', Address::class, Locality::class));
    }

    /**
     * getFormattedPostCode
     * @param string $country
     * @param string $postCode
     * @return string
     */
    private function getFormattedPostCode(string $country, string $postCode): string
    {
        if ($this->getPostCodeFormat($country) === null) {
            return $postCode;
        }
        $format = $this->getPostCodeFormat($country);
        if ($format === []) {
            return $postCode;
        }
        $matches = [];

        preg_match($format['match'], $postCode, $matches);
        foreach($matches as $q=>$w) {
            if ($w === $postCode || $w === '') {
                unset($matches[$q]);
            }
        }
        if (count($matches) < 2) {
            return $postCode;
        }

        return str_replace(['{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}', '{8}', '{9}', '{0}'], $matches, $format['template']);
    }
}
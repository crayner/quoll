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
 * Time: 12:22
 */
namespace App\Modules\People\Manager;

use App\Modules\People\Entity\Phone;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PhoneCodes
 * @package App\Modules\People\Manager
 */
class PhoneManager
{
    /**
     * @var array
     */
    public static $codes;

    /**
     * @var array
     * Codes here are not found in the Symfony Intl lists, but are already in ISO-3166
     * usually with a status not equal to 'officially assigned'.
     * These codes are in the phone codes list.
     */
    public static $missing_codes = [
        'alpha2Name' => [
            'AN' => 'Netherlands Antilles'
        ],
        'alpha3Name' => [
            'ANT' => 'Netherlands Antilles'
        ],
        'alpha2alpha3' => [
            'AN' => 'ANT'
        ],
    ];
    
    /**
     * getPath
     * @return string
     */
    protected static function readCodes(): array
    {
        if (self::$codes === null) {
            self::$codes = Yaml::parse(file_get_contents(__DIR__ .  '/../../../../config/information/phone.yaml'), true);
        }
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'validation' => null,
                'format' => null,
                'iddCode' => 'x',
            ]
        );
        $resolver->setAllowedTypes('validation', ['null','string']);
        $resolver->setAllowedTypes('format', ['null','array']);
        $resolver->setAllowedTypes('iddCode', ['string','integer']);
        foreach(self::$codes as $q=>$w) {
            self::$codes[$q] = $resolver->resolve($w);
        }
        return self::$codes;
    }

    /**
     * getIddCode
     * @param string $alpha2 Alpha 2 Code
     * @return string
     */
    public static function getIddCode(string $alpha2): string
    {
        $alpha2 = strtoupper($alpha2);
        try {
            $alpha3 = Countries::getAlpha3Code($alpha2);
        } catch (MissingResourceException $e) {
            $alpha3 = self::$missing_codes['alpha2alpha3'][$alpha2];
        }

        if (key_exists($alpha3, self::readCodes())) {
            return self::$codes[$alpha3]['iddCode'];
        }
        throw new MissingResourceException(sprintf('The phone IDD Code is not available for %s.', $alpha2));
    }

    /**
     * getAlpha3IddCode
     * @param string $alpha3 Alpha 3 Code
     * @return string
     */
    public static function getAlpha3IddCode(string $alpha3): string
    {
        $alpha3 = strtoupper($alpha3);
        if (key_exists($alpha3, self::readCodes())) {
            return self::$codes[$alpha3]['iddCode'];
        }
        throw new MissingResourceException(sprintf('The phone IDD Code is not available for %s.', $alpha3));
    }

    /**
     * getIddCodes
     * @return array
     */
    public static function getIddCodes(): array
    {
        return self::readCodes();
    }

    /**
     * getIddCodeChoices
     * @param bool $useAlpha3
     * @return array
     */
    public static function getIddCodeChoices(): array
    {
        $result = [];
        foreach(self::readCodes() as $q=>$w) {
            try {
                $value = $q;
                $name = Countries::getAlpha3Name($q) . ' (+' . $w['iddCode'] . ')';
                $result[$name] = $value;
            } catch (MissingResourceException $e) {
                $value = $q;
                $name = self::$missing_codes['alpha3Name'][$q] . ' (+' . $w['iddCode'] . ')';
                $result[$name] = $value;
            }
        }
        ksort($result);

        return $result;
    }

    /**
     * getIddCodeChoices
     * @param ParameterBagInterface $bag
     * @return array
     */
    public static function getIddCodePreferredChoices(ParameterBagInterface $bag): array
    {

        $result = [];
        if ($bag->has('preferred_countries') && $bag->get('preferred_countries') !== [] && is_array($bag->get('preferred_countries'))) {
            foreach ($bag->get('preferred_countries') as $q) {
                try {
                    $value = strtoupper($q);
                    $alpha2 = Countries::getAlpha2Code($value);
                    $name = Countries::getName($alpha2) . ' (+' . self::getAlpha3IddCode($value) . ')';
                    $result[$name] = $value;
                } catch (MissingResourceException $e) {
                    $value = strtoupper($q);
                    $name = self::$missing_codes['alpha3Name'][$value] . ' (+' . self::getAlpha3IddCode($value) . ')';
                    $result[$name] = $value;
                }
            }
            ksort($result);
        }
        return $result;
    }

    /**
     * formatPhoneNumber
     * @param Phone $phone
     * @param bool $withIDD
     * @return string
     */
    public static function formatPhoneNumber(Phone $phone, bool $withIDD = true): string
    {
        if (null !== $phone->getPhoneNumber()) {
            if (($format = self::getPhoneFormat($phone->getCountry())) === null) {
                return '(+' . self::getAlpha3IddCode($phone->getCountry()) . ') ' . $phone->getPhoneNumber();
            } else {
                $matches = [];
                preg_match($format['match'], $phone->getPhoneNumber(), $matches);

                foreach($matches as $q=>$w) {
                    if ($w === $phone->getPhoneNumber() || $w === '') {
                        unset($matches[$q]);
                    }
                }

                if (count($matches) < 2) {
                    return ($format['useIdd'] && $withIDD ? str_replace('idd', self::getAlpha3IddCode($phone->getCountry()), $format['useIdd']) : '') . $phone->getPhoneNumber();
                }

                return ($format['useIdd'] && $withIDD ? str_replace('idd', self::getAlpha3IddCode($phone->getCountry()), $format['useIdd']) : '') . str_replace(['{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}', '{8}', '{9}', '{0}'], $matches, $format['template']);
            }
        }
        return (string)$phone->getPhoneNumber();

    }

    /**
     * getPhoneFormat
     * @param string $alpha3
     * @return array|null
     */
    protected static function getPhoneFormat(string $alpha3): ?array
    {
        if (!key_exists($alpha3, self::readCodes())) {
            return null;
        }
        $format = self::$codes[$alpha3]['format'];
        if (is_array($format)) {
            $resolver = new OptionsResolver();
            $resolver->setDefaults(
                [
                    'template' => false,
                    'match' => false,
                    'useIdd' => false,
                ]
            );
            $resolver->setAllowedTypes('template', ['boolean','string']);
            $resolver->setAllowedTypes('match', ['boolean','string']);
            $resolver->setAllowedTypes('useIdd', ['boolean','string']);
            $format = $resolver->resolve($format);

            if (!$format['template'] || !$format['match']) {
                return null;
            }
            return $format;
        }
        return null;
    }

    /**
     * getValidationRegex
     * @param string $alpha3
     * @return string|null
     */
    public static function getValidationRegex(string $alpha3): ?string
    {
        if (!key_exists($alpha3, self::readCodes())) {
            return null;
        }
        return self::$codes[$alpha3]['validation'];
    }

    /**
     * getAlpha3Name
     * @param string $alpha3
     * @return string
     */
    public static function getAlpha3Name(string $alpha3): string
    {
        try {
            return Countries::getAlpha3Name($alpha3);
        } catch (MissingResourceException $e) {
            if (key_exists($alpha3, self::$missing_codes['alpha3Name'])) {
                return self::$missing_codes['alpha3Name'][$alpha3];
            }
            throw $e;
        }
    }

    /**
     * formatCountryCode
     * @param Phone $phone
     * @return string
     */
    public static function formatCountryCode(Phone $phone): string
    {
        return Countries::getAlpha3Name($phone->getCountry()) . ' (+' . self::getAlpha3IddCode($phone->getCountry()) . ')';
    }
}
<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:56
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Locale
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\LocaleRepository")
 * @ORM\Table(name="Locale")
 */
class Locale extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=5)
     * @Assert\Choice(callback="getLanguages")
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $versionDate;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $active = true;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $installed = false;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 0})
     */
    private $systemDefault = false;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     */
    private $dateFormat;

    /**
     * @var string|null
     * @ORM\Column(type="text",name="date_format_regex")
     */
    private $dateFormatRegEx;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="date_format_php")
     */
    private $dateFormatPHP;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $rtl = false;

    /**
     * @var bool
     */
    private $defaultLanguage = false;

    /**
     * @var array
     */
    private static $languages = array(
        'nl_NL' => 'Dutch - Netherlands',
        'en_GB' => 'English - United Kingdom',
        'en_US' => 'English - United States',
        'es_ES' => 'Español',
        'fr_FR' => 'Français - France',
        'he_IL' => 'עברית - ישראל',
        'hr_HR' => 'Hrvatski - Hrvatska',
        'it_IT' => 'Italiano - Italia',
        'pl_PL' => 'Język polski - Polska',
        'pt_BR' => 'Português - Brasil',
        'ro_RO' => 'Română',
        'sq_AL' => 'Shqip - Shqipëri',
        'vi_VN' => 'Tiếng Việt - Việt Nam',
        'tr_TR' => 'Türkçe - Türkiye',
        'ar_SA' => 'العربية - المملكة العربية السعودية',
        'th_TH' => 'ภาษาไทย - ราชอาณาจักรไทย',
        'ur_PK' => 'پاکستان - اُردُو',
        'zh_CN' => '汉语 - 中国',
        'zh_HK' => '體字 - 香港',
    );

    /**
     * @var Person|null
     */
    private $person;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return Locale
     */
    public function setId(?string $id): Locale
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return Locale
     */
    public function setCode(?string $code): Locale
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Locale
     */
    public function setName(?string $name): Locale
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getVersionDate(): ?\DateTimeImmutable
    {
        return $this->versionDate;
    }

    /**
     * VersionDate.
     *
     * @param \DateTimeImmutable|null $versionDate
     * @return Locale
     */
    public function setVersionDate(?\DateTimeImmutable $versionDate): Locale
    {
        $this->versionDate = $versionDate;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool|null $active
     * @return Locale
     */
    public function setActive(?bool $active): Locale
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInstalled(): bool
    {
        $this->installed = (false === realpath(__DIR__ . '/../../../../../translations/messages+intl-icu.'.$this->getCode().'.yaml') ? 'N' : 'Y');
        return $this->getInstalled() === 'Y';
    }

    /**
     * @param bool|null $installed
     * @return Locale
     */
    public function setInstalled(?bool $installed): Locale
    {
        $this->installed = (bool)$installed;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSystemDefault(): bool
    {
        return (bool)$this->systemDefault;
    }

    /**
     * @param bool|null $systemDefault
     * @return Locale
     */
    public function setSystemDefault(?bool $systemDefault): Locale
    {
        $this->systemDefault = (bool)$systemDefault;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    /**
     * @param string|null $dateFormat
     * @return Locale
     */
    public function setDateFormat(?string $dateFormat): Locale
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFormatRegEx(): ?string
    {
        return $this->dateFormatRegEx;
    }

    /**
     * @param string|null $dateFormatRegEx
     * @return Locale
     */
    public function setDateFormatRegEx(?string $dateFormatRegEx): Locale
    {
        $this->dateFormatRegEx = $dateFormatRegEx;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFormatPHP(): ?string
    {
        return $this->dateFormatPHP;
    }

    /**
     * @param string|null $dateFormatPHP
     * @return Locale
     */
    public function setDateFormatPHP(?string $dateFormatPHP): Locale
    {
        $this->dateFormatPHP = $dateFormatPHP;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isRtl(): bool
    {
        return (bool)$this->rtl;
    }

    /**
     * @param bool|null $rtl
     * @return Locale
     */
    public function setRtl(?bool $rtl): Locale
    {
        $this->rtl = (bool)$rtl;
        return $this;
    }

    /**
     * __toArray
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function __toArray(): array
    {
        $normaliser = new ObjectNormalizer();

        return $normaliser->normalize($this);
    }

    /**
     * @return array
     */
    public static function getLanguages(): array
    {
        return array_flip(self::$languages);
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array {
        return [
            "id" => $this->getId(),
            "code" => $this->getCode(),
            "name" => $this->getName(),
            'active' => TranslationHelper::translate($this->isActive() ? 'Yes' : 'No', [], 'messages'),
            'status' => $this->getStatus(),
            'isActive' => $this->isActive(),
            'isNotDefault' => !$this->isSystemDefault() && $this->isInstalled(),
        ];
    }

    /**
     * getStatus
     * @return string
     */
    public function getStatus(): string
    {
        $result = '';
        if ($this->isSystemDefault())
            $result .= ', ' . TranslationHelper::translate('Default');

        if ($this->isInstalled())
            $result .= ', '.TranslationHelper::translate('Installed');

        $result = trim($result,', ');

        return $result;
    }

    /**
     * @return bool
     */
    public function isDefaultLanguage(): bool
    {
        return $this->defaultLanguage;
    }

    /**
     * DefaultLanguage.
     *
     * @param bool $defaultLanguage
     * @return Locale
     */
    public function setDefaultLanguage(bool $defaultLanguage): Locale
    {
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/LocaleCoreData.yaml'));
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return Locale
     */
    public function setPerson(?Person $person): Locale
    {
        $this->person = $person;
        return $this;
    }
}

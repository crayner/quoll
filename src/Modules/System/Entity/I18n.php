<?php /** @noinspection ALL */

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
 * Class I18n
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\I18nRepository")
 * @ORM\Table(name="i18n")
 */
class I18n extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

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
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     */
    private $installed = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     */
    private $systemDefault = 'N';

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
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     */
    private $rtl = 'N';

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
     * @return I18n
     */
    public function setId(?string $id): I18n
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
     * @return I18n
     */
    public function setCode(?string $code): I18n
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
     * @return I18n
     */
    public function setName(?string $name): I18n
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
     * @return I18n
     */
    public function setVersionDate(?\DateTimeImmutable $versionDate): I18n
    {
        $this->versionDate = $versionDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return self::checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return I18n
     */
    public function setActive(?string $active): I18n
    {
        $this->active = self::checkBoolean($active, 'Y');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInstalled(): ?string
    {
        $this->installed = (false === realpath(__DIR__ . '/../../../../../translations/messages+intl-icu.'.$this->getCode().'.yaml') ? 'N' : 'Y');

        return $this->installed;
    }

    /**
     * @return boolean
     */
    public function isInstalled(): bool
    {
        return $this->getInstalled() === 'Y';
    }

    /**
     * @param string|null $installed
     * @return I18n
     */
    public function setInstalled(?string $installed): I18n
    {
        $this->installed = self::checkBoolean($installed, 'N');
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSystemDefault(): bool
    {
        return $this->getSystemDefault() === 'Y' ? true : false;
    }

    /**
     * @return string|null
     */
    public function getSystemDefault(): ?string
    {
        return $this->systemDefault;
    }

    /**
     * @param string|null $systemDefault
     * @return I18n
     */
    public function setSystemDefault(?string $systemDefault): I18n
    {
        $this->systemDefault = self::checkBoolean($systemDefault, 'N');
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
     * @return I18n
     */
    public function setDateFormat(?string $dateFormat): I18n
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
     * @return I18n
     */
    public function setDateFormatRegEx(?string $dateFormatRegEx): I18n
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
     * @return I18n
     */
    public function setDateFormatPHP(?string $dateFormatPHP): I18n
    {
        $this->dateFormatPHP = $dateFormatPHP;
        return $this;
    }

    /**
     * @return boolean|null
     */
    public function isRtl(): ?bool
    {
        return $this->getRtl() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getRtl(): ?string
    {
        return self::checkBoolean($this->rtl, 'N');
    }

    /**
     * @param string|null $rtl
     * @return I18n
     */
    public function setRtl(?string $rtl): I18n
    {
        $this->rtl = self::checkBoolean($rtl, 'N');
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
     * @return I18n
     */
    public function setDefaultLanguage(bool $defaultLanguage): I18n
    {
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__I18n` (
                `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `code` CHAR(5) NOT NULL,
                `name` CHAR(100) NOT NULL,
                `version_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                `active` CHAR(1) NOT NULL DEFAULT 'Y',
                `installed` CHAR(1) NOT NULL DEFAULT 'N',
                `system_default` CHAR(1) NOT NULL DEFAULT 'N',
                `date_format` CHAR(20) NOT NULL,
                `date_format_regex` longtext NOT NULL,
                `date_format_php` CHAR(20) NOT NULL,
                `rtl` CHAR(1) NOT NULL DEFAULT 'N',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): array
    {
        return Yaml::parse("
-
  code: 'en_GB'
  name: 'English - United Kingdom'
  active: 'Y'
  installed: 'Y'
  systemDefault: 'Y'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'en_US'
  name: 'English - United States'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'mm/dd/yyyy'
  dateFormatRegEx: '/([1-9]|1[012])[- /.]([1-9]|[12][0-9]|3[01])[- /.](19|20\\d\\d)/'
  dateFormatPHP: 'm/d/Y'
  rtl: 'N'
-
  code: 'es_ES'
  name: 'Español'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'zh_CN'
  name: '汉语 - 中国'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'yyyy-mm-dd'
  dateFormatRegEx: '/^[0-9]{4}-([1-9]|1[0-2])-([1-9]|[1-2][0-9]|3[0-1])$/'
  dateFormatPHP: 'Y-m-d'
  rtl: 'N'
-
  code: 'zh_HK'
  name: '體字 - 香港'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'pl_PL'
  name: 'Język polski - Polska'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\\\d\\\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'it_IT'
  name: 'Italiano - Italia'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'id_ID'
  name: 'Bahasa Indonesia - Indonesia'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'ar_SA'
  name: 'العربية - المملكة العربية السعودية'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'Y'
-
  code: 'fr_FR'
  name: 'Français - France'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'ur_PK'
  name: 'پاکستان - اُردُو'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'Y'
-
  code: 'sw_KE'
  name: 'Swahili'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'pt_PT'
  name: 'Português'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'ro_RO'
  name: 'Română'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd.mm.yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd.m.Y'
  rtl: 'N'
-
  code: 'ja_JP'
  name: '日本語'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'yyyy-mm-dd'
  dateFormatRegEx: '/^[0-9]{4}-([1-9]|1[0-2])-([1-9]|[1-2][0-9]|3[0-1])$/'
  dateFormatPHP: 'Y-m-d'
  rtl: 'N'
-
  code: 'ru_RU'
  name: 'ру́сский язы́к'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd.mm.yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd.m.Y'
  rtl: 'N'
-
  code: 'uk_UA'
  name: 'українська мова'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd.mm.yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd.m.Y'
  rtl: 'N'
-
  code: 'bn_BD'
  name: 'বাংলা'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'da_DK'
  name: 'Dansk - Danmark'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'fa_IR'
  name: 'فارسی'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'Y'
-
  code: 'pt_BR'
  name: 'Português - Brasil'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'ka_GE'
  name: 'ქართული ენა'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'nl_NL'
  name: 'Dutch - Netherlands'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'hu_HU'
  name: 'Magyar - Magyarország'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'bg_BG'
  name: 'български език'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'ko_KP'
  name: '한국어 - 대한민국'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'fi_FI'
  name: 'Suomen Kieli - Suomi'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'de_DE'
  name: 'Deutsch - Deutschland'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'in_OR'
  name: 'ଓଡ଼ିଆ - इंडिया'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'no_NO'
  name: 'Norsk - Norge'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'vi_VN'
  name: 'Tiếng Việt - Việt Nam'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'sq_AL'
  name: 'Shqip - Shqipëri'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'th_TH'
  name: 'ภาษาไทย - ราชอาณาจักรไทย'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'el_GR'
  name: 'ελληνικά - Ελλάδα'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
-
  code: 'am_ET'
  name: 'አማርኛ - ኢትዮጵያ'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'om_ET'
  name: 'Afaan Oromo - Ethiopia'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'hr_HR'
  name: 'Hrvatski - Hrvatska'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'et_EE'
  name: 'Eesti Keel - Eesti'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd/mm/yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd/m/Y'
  rtl: 'N'
-
  code: 'he_IL'
  name: 'עברית - ישראל'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd.mm.yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd.m.Y'
  rtl: 'Y'
-
  code: 'tr_TR'
  name: 'Türkçe - Türkiye'
  active: 'Y'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd.mm.yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd.m.Y'
  rtl: 'N'
-
  code: 'my_MM'
  name: 'မြန်မာ - မြန်မာ'
  active: 'N'
  installed: 'N'
  systemDefault: 'N'
  dateFormat: 'dd-mm-yyyy'
  dateFormatRegEx: '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i'
  dateFormatPHP: 'd-m-Y'
  rtl: 'N'
");
    }

    public static function getVersion(): string
    {
        return self::VERSION;
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
     * @return I18n
     */
    public function setPerson(?Person $person): I18n
    {
        $this->person = $person;
        return $this;
    }
}

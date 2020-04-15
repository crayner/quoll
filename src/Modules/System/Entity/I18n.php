<?php /** @noinspection ALL */

/**
 * Created by PhpStorm.
 *
* Kookaburra
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:56
 */
namespace App\Modules\System\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class I18n
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\I18nRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="i18n")
 */
class I18n implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(4) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @var string|null
     * @ORM\Column(length=10, nullable=true)
     */
    private $version;

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
     * @ORM\Column(length=1, name="systemDefault", options={"default": "N"})
     */
    private $systemDefault = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=20, name="dateFormat")
     */
    private $dateFormat;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="dateFormatRegEx")
     */
    private $dateFormatRegEx;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="dateFormatPHP")
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
        'nl_NL' => 'Dutch - Nederland',
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return I18n
     */
    public function setId(?int $id): I18n
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
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     * @return I18n
     */
    public function setVersion(?string $version): I18n
    {
        $this->version = $version;
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
    public function create(): string
    {
        return "CREATE TABLE `__prefix__I18n` (
                `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
                `code` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                `active` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                `installed` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                `systemDefault` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                `dateFormat` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `dateFormatRegEx` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `dateFormatPHP` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `rtl` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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
    public function coreData(): string
    {
        return "INSERT INTO `__prefix__I18n` (`code`, `name`, `version`, `active`, `installed`, `systemDefault`, `dateFormat`, `dateFormatRegEx`, `dateFormatPHP`, `rtl`) VALUES
                    ('en_GB', 'English - United Kingdom', NULL, 'Y', 'Y', 'Y', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('en_US', 'English - United States', NULL, 'Y', 'N', 'N', 'mm/dd/yyyy', '/([1-9]|1[012])[- /.]([1-9]|[12][0-9]|3[01])[- /.](19|20\\d\\d)/', 'm/d/Y', 'N'),
                    ('es_ES', 'Español', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('zh_CN', '汉语 - 中国', NULL, 'Y', 'N', 'N', 'yyyy-mm-dd', '/^[0-9]{4}-([1-9]|1[0-2])-([1-9]|[1-2][0-9]|3[0-1])$/', 'Y-m-d', 'N'),
                    ('zh_HK', '體字 - 香港', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('pl_PL', 'Język polski - Polska', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\\\d\\\\d$/i', 'd/m/Y', 'N'),
                    ('it_IT', 'Italiano - Italia', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('id_ID', 'Bahasa Indonesia - Indonesia', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('ar_SA', 'العربية - المملكة العربية السعودية', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'Y'),
                    ('fr_FR', 'Français - France', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('ur_PK', 'پاکستان - اُردُو', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'Y'),
                    ('sw_KE', 'Swahili', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('pt_PT', 'Português', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('ro_RO', 'Română', NULL, 'Y', 'N', 'N', 'dd.mm.yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd.m.Y', 'N'),
                    ('ja_JP', '日本語', NULL, 'N', 'N', 'N', 'yyyy-mm-dd', '/^[0-9]{4}-([1-9]|1[0-2])-([1-9]|[1-2][0-9]|3[0-1])$/', 'Y-m-d', 'N'),
                    ('ru_RU', 'ру́сский язы́к', NULL, 'N', 'N', 'N', 'dd.mm.yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd.m.Y', 'N'),
                    ('uk_UA', 'українська мова', NULL, 'N', 'N', 'N', 'dd.mm.yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd.m.Y', 'N'),
                    ('bn_BD', 'বাংলা', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('da_DK', 'Dansk - Danmark', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('fa_IR', 'فارسی', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'Y'),
                    ('pt_BR', 'Português - Brasil', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('ka_GE', 'ქართული ენა', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('nl_NL', 'Dutch - Nederland', NULL, 'Y', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('hu_HU', 'Magyar - Magyarország', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('bg_BG', 'български език', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('ko_KP', '한국어 - 대한민국', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('fi_FI', 'Suomen Kieli - Suomi', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('de_DE', 'Deutsch - Deutschland', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('in_OR', 'ଓଡ଼ିଆ - इंडिया', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('no_NO', 'Norsk - Norge', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('vi_VN', 'Tiếng Việt - Việt Nam', NULL, 'Y', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('sq_AL', 'Shqip - Shqipëri', NULL, 'Y', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('th_TH', 'ภาษาไทย - ราชอาณาจักรไทย', NULL, 'Y', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('el_GR', 'ελληνικά - Ελλάδα', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[-]([1-9]|1[012])[-](19|20)\\d\\d$/i', 'd-m-Y', 'N'),
                    ('am_ET', 'አማርኛ - ኢትዮጵያ', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('om_ET', 'Afaan Oromo - Ethiopia', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('hr_HR', 'Hrvatski - Hrvatska', NULL, 'Y', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('et_EE', 'Eesti Keel - Eesti', NULL, 'N', 'N', 'N', 'dd/mm/yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd/m/Y', 'N'),
                    ('he_IL', 'עברית - ישראל', NULL, 'Y', 'N', 'N', 'dd.mm.yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd.m.Y', 'Y'),
                    ('tr_TR', 'Türkçe - Türkiye', NULL, 'Y', 'N', 'N', 'dd.mm.yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd.m.Y', 'N'),
                    ('my_MM', 'မြန်မာ - မြန်မာ', NULL, 'N', 'N', 'N', 'dd-mm-yyyy', '/^([1-9]|[12][0-9]|3[01])[- /.]([1-9]|1[012])[- /.](19|20)\\d\\d$/i', 'd-m-Y', 'N');
                    ";
    }

}
<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\System\Entity;

use App\Manager\EntityInterface;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Country
 * @package App\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\CountryRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Country")
 */
class Country implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer", columnDefinition="INT(4) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=80,unique=true)
     */
    private $printable_name;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="iddCountryCode")
     */
    private $iddCountryCode;
    
    /**
     * @var array
     */
    private static $codeList;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param int|null $id
     * @return Country
     */
    public function setId(?int $id): Country
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrintableName(): ?string
    {
        return $this->printable_name;
    }

    /**
     * PrintableName.
     *
     * @param string|null $printable_name
     * @return Country
     */
    public function setPrintableName(?string $printable_name): Country
    {
        $this->printable_name = $printable_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIddCountryCode(): ?string
    {
        return $this->iddCountryCode;
    }

    /**
     * IddCountryCode.
     *
     * @param string|null $iddCountryCode
     * @return Country
     */
    public function setIddCountryCode(?string $iddCountryCode): Country
    {
        $this->iddCountryCode = $iddCountryCode;
        return $this;
    }

    /**
     * getCountryCodeList
     * @return array
     */
    public static function getCountryCodeList(): array
    {
        if (null !== self::$codeList)
            return self::$codeList;
        self::$codeList = [];
        foreach(ProviderFactory::getRepository(Country::class)->getCountryCodeList() as $code)
            self::$codeList[$code->getPrintableName() . ' ('.$code->getIddCountryCode().')'] = $code->getIddCountryCode();

        return self::$codeList;
    }

    /**
     * nameWithCode
     * @return string
     */
    public function nameWithCode(): string
    {
        return $this->getPrintableName() . ' (' . $this->getIddCountryCode() . ')';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `gibboncountry` (
                `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
                `printable_name` varchar(80) NOT NULL,
                `iddCountryCode` varchar(7) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `printable_name` (`printable_name`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return "INSERT INTO `gibboncountry` (`printable_name`, `iddCountryCode`) VALUES
                    ('Afghanistan', '93'),
                    ('Albania', '355'),
                    ('Algeria', '213'),
                    ('American Samoa', '1684'),
                    ('Andorra', '376'),
                    ('Angola', '244'),
                    ('Anguilla', '1264'),
                    ('Antarctica', '672'),
                    ('Antigua and Barbuda', '1268'),
                    ('Argentina', '54'),
                    ('Armenia', '374'),
                    ('Aruba', '297'),
                    ('Australia', '61'),
                    ('Austria', '43'),
                    ('Azerbaijan', '994'),
                    ('Bahamas', '1242'),
                    ('Bahrain', '973'),
                    ('Bangladesh', '880'),
                    ('Barbados', '1246'),
                    ('Belarus', '375'),
                    ('Belgium', '32'),
                    ('Belize', '501'),
                    ('Benin', '229'),
                    ('Bermuda', '1441'),
                    ('Bhutan', '975'),
                    ('Bolivia', '591'),
                    ('Bosnia and Herzegovina', '387'),
                    ('Botswana', '267'),
                    ('Brazil', '55'),
                    ('British Indian Ocean Territory', '246'),
                    ('Brunei Darussalam', '673'),
                    ('Bulgaria', '359'),
                    ('Burkina Faso', '226'),
                    ('Burundi', '257'),
                    ('Cambodia', '855'),
                    ('Cameroon', '237'),
                    ('Canada', '1'),
                    ('Cape Verde', '238'),
                    ('Cayman Islands', '1345'),
                    ('Central African Republic', '236'),
                    ('Chad', '235'),
                    ('Chile', '56'),
                    ('China', '86'),
                    ('Christmas Island', '61'),
                    ('Cocos (Keeling) Islands', '61'),
                    ('Colombia', '57'),
                    ('Comoros', '269'),
                    ('Congo', '242'),
                    ('Congo, the Democratic Republic of the', '243'),
                    ('Cook Islands', '682'),
                    ('Costa Rica', '506'),
                    ('Croatia', '385'),
                    ('Cuba', '53'),
                    ('Cyprus', '357'),
                    ('Czech Republic', '420'),
                    ('Denmark', '45'),
                    ('Djibouti', '253'),
                    ('Dominica', '1767'),
                    ('Dominican Republic', '1809'),
                    ('Ecuador', '593'),
                    ('Egypt', '20'),
                    ('El Salvador', '503'),
                    ('Equatorial Guinea', '240'),
                    ('Eritrea', '291'),
                    ('Estonia', '372'),
                    ('Ethiopia', '251'),
                    ('Falkland Islands', '500'),
                    ('Faroe Islands', '298'),
                    ('Fiji', '679'),
                    ('Finland', '358'),
                    ('France', '33'),
                    ('French Polynesia', '689'),
                    ('Gabon', '241'),
                    ('Gambia', '220'),
                    ('Georgia', '995'),
                    ('Germany', '49'),
                    ('Ghana', '233'),
                    ('Gibraltar', '350'),
                    ('Greece', '30'),
                    ('Greenland', '299'),
                    ('Grenada', '1473'),
                    ('Guadeloupe', '590'),
                    ('Guam', '1671'),
                    ('Guatemala', '502'),
                    ('Guinea', '224'),
                    ('Guinea-Bissau', '245'),
                    ('Guyana', '592'),
                    ('Haiti', '509'),
                    ('Holy See (Vatican City State)', '39'),
                    ('Honduras', '504'),
                    ('Hong Kong', '852'),
                    ('Hungary', '36'),
                    ('Iceland', '354'),
                    ('India', '91'),
                    ('Indonesia', '62'),
                    ('Iran', '98'),
                    ('Iraq', '964'),
                    ('Ireland', '353'),
                    ('Israel', '972'),
                    ('Italy', '39'),
                    ('Jamaica', '1876'),
                    ('Japan', '81'),
                    ('Jordan', '962'),
                    ('Kazakhstan', '7'),
                    ('Kenya', '254'),
                    ('Kiribati', '686'),
                    ('Korea, Democratic People\'s Republic of', '850'),
                    ('Korea, Republic of', '82'),
                    ('Kuwait', '965'),
                    ('Kyrgyzstan', '996'),
                    ('Lao People\'s Democratic Republic', '856'),
                    ('Latvia', '371'),
                    ('Lebanon', '961'),
                    ('Lesotho', '266'),
                    ('Liberia', '231'),
                    ('Libyan Arab Jamahiriya', '218'),
                    ('Liechtenstein', '423'),
                    ('Lithuania', '370'),
                    ('Luxembourg', '352'),
                    ('Macao', '853'),
                    ('Macedonia', '389'),
                    ('Madagascar', '261'),
                    ('Malawi', '265'),
                    ('Malaysia', '60'),
                    ('Maldives', '960'),
                    ('Mali', '223'),
                    ('Malta', '356'),
                    ('Marshall Islands', '692'),
                    ('Mauritania', '222'),
                    ('Mauritius', '230'),
                    ('Mayotte', '262'),
                    ('Mexico', '52'),
                    ('Micronesia', '691'),
                    ('Moldova, Republic of', '373'),
                    ('Monaco', '377'),
                    ('Mongolia', '976'),
                    ('Montenegro', '382'),
                    ('Montserrat', '1664'),
                    ('Morocco', '212'),
                    ('Mozambique', '258'),
                    ('Myanmar', '95'),
                    ('Namibia', '264'),
                    ('Nauru', '674'),
                    ('Nepal', '977'),
                    ('Netherlands', '31'),
                    ('Netherlands Antilles', '599'),
                    ('New Caledonia', '687'),
                    ('New Zealand', '64'),
                    ('Nicaragua', '505'),
                    ('Niger', '227'),
                    ('Nigeria', '234'),
                    ('Niue', '683'),
                    ('Norfolk Island', '672'),
                    ('Northern Mariana Islands', '1670'),
                    ('Norway', '47'),
                    ('Oman', '968'),
                    ('Pakistan', '92'),
                    ('Palau', '680'),
                    ('Panama', '507'),
                    ('Papua New Guinea', '675'),
                    ('Paraguay', '595'),
                    ('Peru', '51'),
                    ('Philippines', '63'),
                    ('Pitcairn', '64'),
                    ('Poland', '48'),
                    ('Portugal', '351'),
                    ('Puerto Rico', '1'),
                    ('Qatar', '974'),
                    ('Reunion', '262'),
                    ('Romania', '40'),
                    ('Russia', '7'),
                    ('Rwanda', '250'),
                    ('Saint Helena', '290'),
                    ('Saint Kitts and Nevis', '1869'),
                    ('Saint Lucia', '1758'),
                    ('Saint Pierre and Miquelon', '508'),
                    ('Saint Vincent and the Grenadines', '1784'),
                    ('Samoa', '685'),
                    ('San Marino', '378'),
                    ('Sao Tome and Principe', '239'),
                    ('Saudi Arabia', '966'),
                    ('Senegal', '221'),
                    ('Serbia', '381'),
                    ('Seychelles', '248'),
                    ('Sierra Leone', '232'),
                    ('Singapore', '65'),
                    ('Slovakia', '421'),
                    ('Slovenia', '386'),
                    ('Solomon Islands', '677'),
                    ('Somalia', '252'),
                    ('South Africa', '27'),
                    ('Spain', '34'),
                    ('Sri Lanka', '94'),
                    ('Sudan', '249'),
                    ('Suriname', '597'),
                    ('Svalbard and Jan Mayen', '47'),
                    ('Swaziland', '268'),
                    ('Sweden', '46'),
                    ('Switzerland', '41'),
                    ('Syrian Arab Republic', '963'),
                    ('Taiwan', '886'),
                    ('Tajikistan', '992'),
                    ('Tanzania, United Republic of', '255'),
                    ('Thailand', '66'),
                    ('Timor-Leste', '670'),
                    ('Togo', '228'),
                    ('Tokelau', '690'),
                    ('Tonga', '676'),
                    ('Trinidad and Tobago', '1868'),
                    ('Tunisia', '216'),
                    ('Turkey', '90'),
                    ('Turkmenistan', '993'),
                    ('Turks and Caicos Islands', '1649'),
                    ('Tuvalu', '688'),
                    ('Uganda', '256'),
                    ('Ukraine', '380'),
                    ('United Arab Emirates', '971'),
                    ('United Kingdom', '44'),
                    ('United States', '1'),
                    ('Uruguay', '598'),
                    ('Uzbekistan', '998'),
                    ('Vanuatu', '678'),
                    ('Venezuela', '58'),
                    ('Vietnam', '84'),
                    ('Virgin Islands, British', '1284'),
                    ('Virgin Islands, US', '1340'),
                    ('Wallis and Futuna', '681'),
                    ('Western Sahara', '212'),
                    ('Yemen', '967'),
                    ('Zambia', '260'),
                    ('Zimbabwe', '263');";
    }

}
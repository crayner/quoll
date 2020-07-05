<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 25/07/2019
 * Time: 09:38
 */
namespace App\Modules\System\Form\Entity;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class SystemSettings
 * @package App\Form\Entity
 */
class SystemSettings
{
    /**
     * @var null|string
     */
    private $title;

    /**
     * @var null|string
     */
    private $surname;

    /**
     * @var null|string
     */
    private $firstName;

    /**
     * @var null|string
     */
    private $email;

    /**
     * @var bool
     */
    private $support = true;

    /**
     * @var null|string
     */
    private $username;

    /**
     * @var null|string
     */
    private $password;

    /**
     * @var Request
     */
    private $request;

    /**
     * injectRequest
     * @param Request $request
     */
    public function injectRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Title.
     *
     * @param string|null $title
     * @return SystemSettings
     */
    public function setTitle(?string $title): SystemSettings
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * Surname.
     *
     * @param string|null $surname
     * @return SystemSettings
     */
    public function setSurname(?string $surname): SystemSettings
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * FirstName.
     *
     * @param string|null $firstName
     * @return SystemSettings
     */
    public function setFirstName(?string $firstName): SystemSettings
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Email.
     *
     * @param string|null $email
     * @return SystemSettings
     */
    public function setEmail(?string $email): SystemSettings
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSupport(): bool
    {
        return (bool) $this->support;
    }

    /**
     * Support.
     *
     * @param bool $support
     * @return SystemSettings
     */
    public function setSupport(bool $support): SystemSettings
    {
        $this->support = (bool) $support;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Username.
     *
     * @param string|null $username
     * @return SystemSettings
     */
    public function setUsername(?string $username): SystemSettings
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Password.
     *
     * @param string|null $password
     * @return SystemSettings
     */
    public function setPassword(?string $password): SystemSettings
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $systemName = 'Kookaburra';

    /**
     * @var string
     */
    private $installType = 'Testing';

    /**
     * @var array
     */
    private static $installTypeList = [
        'Production'  => 'Production',
        'Testing'     => 'Testing',
        'Development' => 'Development',
    ];

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $this->setBaseUrl(null);
        return $this->baseUrl;
    }

    /**
     * BaseUrl.
     *
     * @param string $baseUrl
     * @return SystemSettings
     */
    public function setBaseUrl(?string $absoluteUrl): SystemSettings
    {
        $baseUrl = $this->request->server->get('REQUEST_SCHEME').'://';
        $baseUrl .= $this->request->server->get('SERVER_NAME');
        $port = $this->request->server->get('REQUEST_SCHEME') === 'http' && $this->request->server->get('SERVER_PORT') !== '80' ? ':'.$this->request->server->get('SERVER_PORT') : '';
        $port = $this->request->server->get('REQUEST_SCHEME') === 'https' && $this->request->server->get('SERVER_PORT') !== '443' ? ':'.$this->request->server->get('SERVER_PORT') : '';
        $baseUrl .= $port;
        $this->baseUrl = $baseUrl ;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        if (null === $this->basePath)
            $this->setBasePath(null);
        return $this->basePath;
    }

    /**
     * BasePath.
     *
     * @param string|null $basePath
     * @return SystemSettings
     */
    public function setBasePath(?string $basePath): SystemSettings
    {
        if (null === $basePath) {
            $basePath = realpath(__DIR__ . '/../../../public');
        }
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getSystemName(): string
    {
        return $this->systemName;
    }

    /**
     * SystemName.
     *
     * @param string $systemName
     * @return SystemSettings
     */
    public function setSystemName(string $systemName): SystemSettings
    {
        $this->systemName = $systemName;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstallType(): string
    {
        return $this->installType;
    }

    /**
     * InstallType.
     *
     * @param string $installType
     * @return SystemSettings
     */
    public function setInstallType(string $installType): SystemSettings
    {
        $this->installType = $installType;
        return $this;
    }

    /**
     * @return array
     */
    public static function getInstallTypeList(): array
    {
        return self::$installTypeList;
    }

    /**
     * @var string|null
     */
    private $organisationName;

    /**
     * @var string|null
     */
    private $organisationAbbreviation;

    /**
     * @return string|null
     */
    public function getOrganisationName(): ?string
    {
        return $this->organisationName;
    }

    /**
     * OrganisationName.
     *
     * @param string|null $organisationName
     * @return SystemSettings
     */
    public function setOrganisationName(?string $organisationName): SystemSettings
    {
        $this->organisationName = $organisationName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrganisationAbbreviation(): ?string
    {
        return $this->organisationAbbreviation;
    }

    /**
     * OrganisationNameShort.
     *
     * @param string|null $organisationAbbreviation
     * @return SystemSettings
     */
    public function setOrganisationAbbreviation(?string $organisationAbbreviation): SystemSettings
    {
        $this->organisationAbbreviation = $organisationAbbreviation;
        return $this;
    }

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $currency;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Country.
     *
     * @param string|null $country
     * @return SystemSettings
     */
    public function setCountry(?string $country): SystemSettings
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * Currency.
     *
     * @param string|null $currency
     * @return SystemSettings
     */
    public function setCurrency(?string $currency): SystemSettings
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * Timezone.
     *
     * @param string|null $timezone
     * @return SystemSettings
     */
    public function setTimezone(?string $timezone): SystemSettings
    {
        $this->timezone = $timezone;
        return $this;
    }
}

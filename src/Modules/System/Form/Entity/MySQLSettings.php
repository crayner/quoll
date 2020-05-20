<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 24/07/2019
 * Time: 11:59
 */
namespace App\Modules\System\Form\Entity;

use App\Manager\Traits\BooleanList;

/**
 * Class MySQLSettings
 * @package App\Form\Entity
 */
class MySQLSettings
{
    use BooleanList;
    /**
     * @var string
     */
    private $host = 'localhost';

    /**
     * @var string
     */
    private $dbname;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $port = '3306';

    /**
     * @var string
     */
    private $demo = 'N';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * getHost
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Host.
     *
     * @param string $host
     * @return MySQLSettings
     */
    public function setHost(?string $host): MySQLSettings
    {
        $this->host = $host ?: 'localhost';
        return $this;
    }

    /**
     * getName
     * @return string|null
     */
    public function getDbname(): ?string
    {
        return $this->dbname;
    }

    /**
     * Name.
     *
     * @param string $dbname
     * @return MySQLSettings
     */
    public function setDbname(?string $dbname): MySQLSettings
    {
        $this->dbname = $dbname;
        return $this;
    }

    /**
     * getUser
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * User.
     *
     * @param string $user
     * @return MySQLSettings
     */
    public function setUser(?string $user): MySQLSettings
    {
        $this->user = $user;
        return $this;
    }

    /**
     * getPassword
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Password.
     *
     * @param string $password
     * @return MySQLSettings
     */
    public function setPassword(?string $password): MySQLSettings
    {
        $this->password = $password;
        return $this;
    }

    /**
     * getPort
     * @return string|null
     */
    public function getPort(): ?string
    {
        return $this->port;
    }

    /**
     * Port.
     *
     * @param string $port
     * @return MySQLSettings
     */
    public function setPort(?string $port): MySQLSettings
    {
        $this->port = $port ?: '3306';
        return $this;
    }

    /**
     * getDemo
     * @return string
     */
    public function isDemo(): bool
    {
        return $this->getDemo() === 'Y' ? true : false;
    }

    /**
     * getDemo
     * @return string
     */
    public function getDemo(): string
    {
        return self::checkBoolean($this->demo, 'N');
    }

    /**
     * setDemo
     * @param string $demo
     * @return MySQLSettings
     */
    public function setDemo(?string $demo): MySQLSettings
    {
        $this->demo = self::checkBoolean($demo, 'N');
        return $this;
    }

    /**
     * getParams
     * @return array
     */
    public function getParams(bool $includeName = true): array
    {
        $params = [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'charset' => 'utf8',
        ];
        if ($includeName)
            $params['dbname'] = $this->getDbname();

        return $params;
    }

    /**
     * getParams
     * @return array
     */
    public function getDriverOptions(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Prefix.
     *
     * @param string $prefix
     * @return MySQLSettings
     */
    public function setPrefix(?string $prefix): MySQLSettings
    {
        $this->prefix = $prefix;
        return $this;
    }
}
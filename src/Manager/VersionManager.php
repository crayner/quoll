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
 * Date: 9/09/2019
 * Time: 12:33
 */

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

/**
 * Class VersionManager
 * @package App\Manager
 */
class VersionManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $config;

    /**
     * VersionManager constructor.
     */
    public function __construct()
    {
        $this->config = Yaml::parse(file_get_contents(__DIR__ . '/../../config/packages/version.yaml'));
    }

    /**
     * getConfigValue
     * @param string $name
     * @return mixed
     */
    public function getConfigValue(string $name)
    {
        if ($name === 'version')
            return $this->config['parameters'][$name];

        if (isset($this->config['parameters']['systemRequirements'][$name]))
            return $this->config['parameters']['systemRequirements'][$name];
        return $this->config['parameters']['systemRequirements']['settings'][$name];
    }

    /**
     * getPHPVersion
     * @return string
     */
    public function getPHPVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * isVersionValid
     * @param string $version
     * @param string $required
     * @param string $testAs
     * @return bool
     */
    public function isVersionValid(string $version, string $required, string $testAs = '>='): bool
    {
        return version_compare($version, $required, $testAs);
    }

    /**
     * getMySQLVersion
     * @return string
     */
    public function getMySQLVersion(): string
    {
        $sql = "SELECT VERSION()";
        $stmt = $this->getEm()->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['VERSION()'];
    }

    /**
     * @return EntityManager
     */
    public function getEm(): EntityManager
    {
        return $this->em;
    }

    /**
     * Em.
     *
     * @param EntityManager $em
     * @return VersionManager
     */
    public function setEm(EntityManager $em): VersionManager
    {
        $this->em = $em;
        return $this;
    }

    /**
     * getCollation
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCollation(): string
    {
        $sql = "SELECT COLLATION('a')";
        $stmt = $this->getEm()->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['COLLATION(\'a\')'];
    }

    /**
     * isApache
     * @return bool
     */
    public function isApache(): bool
    {
        return function_exists('apache_get_version');
    }

    /**
     * @return array
     */
    public function getApacheModules()
    {
        return apache_get_modules();
    }

    /**
     * isExtensionInstalled
     * @param string $name
     * @return bool
     */
    public function isExtensionInstalled(string $name): bool
    {
        return extension_loaded($name);
    }

    /**
     * isSettingOK
     * @param string $name
     * @param $value
     * @param string $operator
     * @return bool
     */
    public function isSettingOK(string $name, $value, string $operator = '=='): bool
    {
        $required = ini_get($name);
        switch($operator) {
            case '>=':
                return $value >= $required;
                break;
            default:
                return $value == $required;
        }
        return false;
    }

    /**
     * @var int
     */
    private $fileCount = 0;

    /**
     * @var int
     */
    public $publicWriteCount = 0;

    /**
     * getFileCount
     * @return int
     */
    public function getFileCount()
    {
        if ($this->fileCount > 0)
            return $this->fileCount;

        $this->fileCount = 0;
        $this->publicWriteCount = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__.'/../../public')) as $filename)
        {
            if (pathinfo($filename, PATHINFO_EXTENSION) != 'php') continue;
            if (strpos(pathinfo($filename, PATHINFO_DIRNAME), '/uploads') !== false) continue;
            if (fileperms($filename) & 0x0002) $this->publicWriteCount++;
            $this->fileCount++;
        }
        return $this->fileCount;
    }

    /**
     * getPublicWriteCount
     * @return int
     */
    public function getPublicWriteCount(): int
    {
        return $this->publicWriteCount;
    }

    /**
     * isUploadWriteable
     * @return bool
     */
    public function isUploadWriteable(): bool
    {
        return is_writeable(__DIR__.'/../../public/uploads');
    }
}
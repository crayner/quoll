<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/04/2020
 * Time: 10:44
 */

namespace App\Manager;

use Symfony\Component\Yaml\Yaml;

/**
 * Class ParameterFileManager
 * @package App\Manager
 */
class ParameterFileManager
{
    /**
     * getProjectDir
     * @return string
     */
    public static function getProjectDir(): string
    {
        return realpath(__DIR__ . '/../..');
    }

    /**
     * getSettingFileName
     * @return string
     */
    public static function getSettingFileName(): string
    {
        return realpath(self::getProjectDir() . '/config/packages/quoll.yaml');
    }

    /**
     * writeParamterFile
     * @param array $config
     */
    public static function writeParameterFile(array $config)
    {
        file_put_contents(self::getSettingFileName(), Yaml::dump($config, 8));
    }

    /**
     * readParameterFile
     * @return array
     */
    public static function readParameterFile(): array
    {
        return Yaml::parse(file_get_contents(self::getSettingFileName()));

    }
}
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
 * Date: 14/12/2019
 * Time: 11:46
 */

namespace App\Util;

/**
 * Class StringHelper
 * @package App\Util
 */
class StringHelper
{
    /**
     * toSnakeCase
     * @param string $value
     * @return string
     */
    public static function toSnakeCase(string $value): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst(preg_replace('/[^A-Za-z0-9:]/', '', $value))));
    }
    /**
     * toSnakeCase
     * @param string $value
     * @return string
     */
    public static function toCamelCase(string $value): string
    {
        return  str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $value)));
    }
}
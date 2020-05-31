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
 * Date: 25/05/2020
 * Time: 12:08
 */

namespace App\Util;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ParameterBagHelper
{
    /**
     * @var ParameterBagInterface
     */
    private static $parameterBag;

    /**
     * @return ParameterBagInterface
     */
    public static function getParameterBag(): ParameterBagInterface
    {
        return self::$parameterBag;
    }

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public static function setParameterBag(ParameterBagInterface $parameterBag): void
    {
        self::$parameterBag = $parameterBag;
    }

    /**
     * has
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return self::getParameterBag()->has($name);
    }

    /**
     * has
     * @param string $name
     * @param bool $fail
     * @return mixed
     */
    public static function get(string $name, bool $fail = true)
    {
        if (!$fail && !self::has($name)) {
            return null;
        }
        return self::getParameterBag()->get($name);
    }
}
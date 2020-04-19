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
 * Date: 10/08/2019
 * Time: 08:48
 */

namespace App\Util;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGeneratorHelper
{
    /**
     * @var UrlGeneratorInterface
     */
    private static $generator;

    /**
     * RouterHelper constructor.
     *
     * This class should only be used in the legacy area to generate routes to new code.
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        self::$generator = $generator;
    }

    /**
     * getPath
     * @param string|null $route
     * @param array $parameters
     * @param bool $relative
     * @return string
     */
    public static function getPath(?string $route, array $parameters = [], bool $relative = false)
    {
        if (is_null($route))
            return null;
        return self::$generator->generate($route, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * getUrl
     * @param string|null $name
     * @param array $parameters
     * @param bool $schemeRelative
     * @return string|null
     */
    public static function getUrl(?string $name, array $parameters = [], bool $schemeRelative = false): ?string
    {
        if (is_null($name))
            return null;
        return self::$generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }

}
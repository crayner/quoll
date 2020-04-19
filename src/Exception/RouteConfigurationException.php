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
 * Date: 26/07/2019
 * Time: 09:51
 */

namespace App\Exception;

/**
 * Class RouteConfigurationException
 * @package App\Exception
 */
class RouteConfigurationException extends \Exception
{
    /**
     * RouteConfigurationException constructor.
     * @param string $route
     * @param string $message
     */
    public function __construct(string $route, string $message = '') {
        if ('' === $message)
            $message = sprintf('The route "%s" is not configured with a double "_" underscore to separate module from action.', $route);
        parent::__construct($message);
    }
}
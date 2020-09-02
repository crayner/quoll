<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/09/2020
 * Time: 16:20
 */
namespace App\Modules\System\Exception;

use RuntimeException;

/**
 * Class InvalidModuleException
 * @package App\Modules\System\Exception
 * @author Craig Rayner <craig@craigrayner.com>
 */
class InvalidModuleException extends RuntimeException
{
    /**
     * InvalidActionException constructor.
     * @param string $route
     * @param string $name
     */
    public function __construct(string $route, string $name)
    {
        $message = sprintf('The module was not found for route "%s" and Controller module name of "%s"', $route, $name);

        parent::__construct($message);
    }
}
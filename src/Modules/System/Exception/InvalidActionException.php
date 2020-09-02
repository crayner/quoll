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
 * Time: 14:59
 */
namespace App\Modules\System\Exception;

use App\Modules\System\Entity\Module;
use RuntimeException;

/**
 * Class InvalidActionException
 * @package App\Modules\System\Exception
 * @author Craig Rayner <craig@craigrayner.com>
 */
class InvalidActionException extends RuntimeException
{
    /**
     * InvalidActionException constructor.
     * @param string $route
     * @param Module $module
     */
    public function __construct(string $route, Module $module)
    {
        $message = sprintf('The action was not found for route "%s" in module "%s"', $route, $module->getName());

        parent::__construct($message);
    }
}
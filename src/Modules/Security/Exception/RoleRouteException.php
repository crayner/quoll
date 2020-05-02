<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/05/2020
 * Time: 09:34
 */

namespace App\Modules\Security\Exception;

/**
 * Class RoleRouteException
 * @package App\Modules\Security\Exception
 */
class RoleRouteException extends \RuntimeException
{
    /**
     * RoleRouteException constructor.
     * @param string $action
     * @param string $message
     */
    public function __construct(string $action, string $message = 'The route "%s" has not been set in the Action table.')
    {
        parent::__construct(sprintf($message, $action));
    }
}

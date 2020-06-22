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
 * Date: 26/07/2019
 * Time: 09:51
 */

namespace App\Exception;

use Throwable;

class MissingModuleException extends \RuntimeException
{
    /**
     * MissingModuleException constructor.
     * @param string $message
     */
    public function __construct(string $controller, string $message = 'The Module is not defined by the controller name: %s')
    {
        parent::__construct(sprintf($message, $controller));
    }
}
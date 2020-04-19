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

use Throwable;

class MissingActionException extends \RuntimeException
{
    /**
     * MissingActionException constructor.
     * @param string $message
     */
    public function __construct(string $message = 'The React Form requires a valid action be defined.')
    {
        parent::__construct($message);
    }
}
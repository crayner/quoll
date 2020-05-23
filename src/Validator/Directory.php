<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/07/2019
 * Time: 09:36
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Directory
 * @package App\Validator
 */
class Directory extends Constraint
{
    const DIRECTORY_ERROR = 'ac08a913-b921-4da8-843d-87e0aeef78e9';

    protected static $errorNames = [
        self::DIRECTORY_ERROR => 'DIRECTORY_ERROR',
    ];

    public $message = 'The directory "{directory}" does not exist.';
    
    public $transDomain = 'messages';

}
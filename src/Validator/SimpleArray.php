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
 * Date: 30/11/2019
 * Time: 15:14
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class SimpleArray
 * @package App\Validator
 */
class SimpleArray extends Constraint
{
    const SIMPLE_ARRAY_ERROR = '300285a1-bed2-43e8-bd4d-2c41d4283dd9';

    protected static $errorNames = [
        self::SIMPLE_ARRAY_ERROR => 'SIMPLE_ARRAY_ERROR',
    ];

    public $transDomain = 'messages';

    public $unique = false;
}
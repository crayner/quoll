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
 * Date: 23/10/2020
 * Time: 11:12
 */
namespace App\Modules\Attendance\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Reason
 *
 * 23/10/2020 11:14
 * @package App\Modules\Attendance\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation()
 */
class Reason extends Constraint
{
    const INVALID_REASON_ERROR = '1aaf82ba-5307-4895-b8ab-3ef17b1363c5';

    /**
     * @var array|string[]
     */
    protected static $errorNames = [
        self::INVALID_REASON_ERROR => 'INVALID_REASON_ERROR',
    ];
}
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
 * Date: 7/11/2020
 * Time: 08:24
 */
namespace App\Modules\Timetable\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class SchoolDay
 *
 * 7/11/2020 08:26
 * @package App\Modules\Timetable\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SchoolDay extends Constraint
{
    const VALID_SCHOOL_DAY_ERROR = 'f1b44f9f-66b2-4f3e-b10b-5049393d1e51';

    protected static $errorNames = [
        self::VALID_SCHOOL_DAY_ERROR => 'VALID_SCHOOL_DAY_ERROR',
    ];

    public string $transDomain = 'Timetable';

    public bool $enforceCurrentYear = true;
}

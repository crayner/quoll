<?php
/**
 * Created by PhpStorm.
 *
 * This file is part of the Busybee Project.
 *
 * (c) Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/09/2018
 * Time: 16:00
 */
namespace App\Modules\Timetable\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class TimetableColumnPeriod
 * @package App\Modules\Timetable\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation()
 */
class TimetableColumnPeriod extends Constraint
{
    const TIMETABLE_COLUMN_ROW_ERROR = '001ab424-cf79-435f-b9b5-50f7ec0509ae';

    protected static $errorNames = [
        self::TIMETABLE_COLUMN_ROW_ERROR => 'TIMETABLE_COLUMN_ROW_ERROR',
    ];

    public $transDomain = 'Timetable';

    /**
     * getTargets
     *
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
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
 * Date: 3/10/2019
 * Time: 13:44
 */

namespace App\Modules\School\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class AcademicYear
 * @package App\Modules\School\Validator
 * @Annotation
 */
class AcademicYear extends Constraint
{
    const INVALID_ACADEMIC_YEAR_ERROR = 'ae82cdba-4764-4781-a650-14a4c7efeea0';

    protected static $errorNames = [
        self::INVALID_ACADEMIC_YEAR_ERROR => 'INVALID_ACADEMIC_YEAR_ERROR',
    ];

    public $transDomain = 'School';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
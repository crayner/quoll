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
 * Date: 12/11/2020
 * Time: 09:49
 */
namespace App\Modules\Enrolment\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class CourseClassAccess
 *
 * 12/11/2020 09:50
 * @package App\Modules\Enrolment\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassAccess extends Constraint
{
    const COURSE_CLASS_ACCESS_ERROR = 'ffad21ce-2dd1-4a95-8ab7-bb5a5b139475';

    /**
     * @var array|string[]
     */
    protected static $errorNames = [
        self::COURSE_CLASS_ACCESS_ERROR => 'COURSE_CLASS_ACCESS_ERROR',
    ];

    public string $translationDomain = 'Enrolment';

    public string $message = 'Access to "course_class" is denied.';
}

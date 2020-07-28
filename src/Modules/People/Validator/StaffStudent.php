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
 * Date: 28/07/2020
 * Time: 14:12
 */
namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class StaffStudent
 * @package App\Modules\People\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation()
 */
class StaffStudent extends Constraint
{
    const STAFF_STUDENT_ERROR = '7a1232a1-87bb-4c41-b8c1-c71fd3cf0b71';

    protected static $errorNames = [
        self::STAFF_STUDENT_ERROR => 'STAFF_STUDENT_ERROR',
    ];

    public $message = 'A person can be staff or student, not both.';

    public $transDomain = 'People';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

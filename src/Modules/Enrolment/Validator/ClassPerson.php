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
 * Date: 4/09/2020
 * Time: 12:29
 */
namespace App\Modules\Enrolment\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class ClassPerson
 * @package App\Modules\Enrolment\Validation
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation()
 */
class ClassPerson extends Constraint
{
    const INVALID_ROLE_ERROR = 'fd50573a-2c45-4bb6-aa84-7d9c97ffbbd3';

    protected static $errorNames = [
        self::INVALID_ROLE_ERROR => 'INVALID_ROLE_ERROR',
    ];

    /**
     * getTargets
     *
     * 4/09/2020 12:41
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

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
 * Time: 14:54
 */

namespace App\Modules\School\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class SpecialDay
 * @package App\Modules\School\Validator
 * @Annotation
 */
class SpecialDay extends Constraint
{
    const INVALID_SPECIAL_DAY_ERROR = 'a6ea5af9-5293-4cb5-a8cd-d776774265d6';

    protected static $errorNames = [
        self::INVALID_SPECIAL_DAY_ERROR => 'INVALID_SPECIAL_DAY_ERROR',
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
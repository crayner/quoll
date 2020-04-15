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
    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
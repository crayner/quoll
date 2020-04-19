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
 * Date: 21/12/2019
 * Time: 20:44
 */

namespace App\Modules\School\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Term
 * @package App\Modules\School\Validator
 * @Annotation
 */
class Term extends Constraint
{
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
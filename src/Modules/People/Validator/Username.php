<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/04/2020
 * Time: 07:20
 */

namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Username
 * @package App\Modules\People\Validator
 * @Annotation
 */
class Username extends Constraint
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
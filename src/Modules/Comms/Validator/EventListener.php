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
 * Date: 28/03/2020
 * Time: 12:34
 */

namespace App\Modules\Comms\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class EventListener
 * @package App\Modules\Comms\Validator
 * @Annotation
 */
class EventListener extends Constraint
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
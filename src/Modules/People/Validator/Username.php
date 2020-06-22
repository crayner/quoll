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
    const USERNAME_UNIQUE_ERROR = 'e1d0668e-a003-44ab-9cbd-12a822b5b562';

    const PRIMARY_ROLE_NOT_SET_ERROR = '7cfca74e-c9b0-4f69-b86b-7d6448176a12';

    protected static $errorNames = [
        self::USERNAME_UNIQUE_ERROR => 'USERNAME_UNIQUE_ERROR',
        self::PRIMARY_ROLE_NOT_SET_ERROR => 'PRIMARY_ROLE_NOT_SET_ERROR',
    ];

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 19/08/2019
 * Time: 18:10
 */

namespace App\Modules\Security\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class CurrentPassword
 * @package App\Modules\Security\Validator
 */
class CurrentPassword extends Constraint
{
    /**
     * @var string
     */
    public $translationDomain = 'Security';

    /**
     * @var string
     */
    public $message = 'Your request failed due to incorrect current password.';
}
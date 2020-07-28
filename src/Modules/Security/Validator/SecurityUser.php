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
 * Time: 09:33
 */
namespace App\Modules\Security\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class SecurityUser
 * @package App\Modules\Security\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation
 */
class SecurityUser extends Constraint
{
    const SECURITY_USER_ERROR = '4c0a10a4-c90d-4612-aea1-8209e2c987c9';
    const USERNAME_ERROR = '51c1357e-aedc-45cd-9e1b-5af2294b76ce';
    const SECURITY_ROLES_ERROR = '42b7c236-0a22-4495-bccd-acf4ae2cce19';

    protected static $errorNames = [
        self::SECURITY_USER_ERROR => 'SECURITY_USER_ERROR',
        self::USERNAME_ERROR => 'USERNAME_ERROR',
        self::SECURITY_ROLES_ERROR => 'SECURITY_ROLES_ERROR',
    ];

    public $transDomain = 'Security';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

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
 * Date: 29/06/2020
 * Time: 10:25
 */
namespace App\Modules\Security\Provider;

use App\Modules\Security\Entity\SecurityRole;
use App\Modules\System\Entity\Action;
use App\Provider\AbstractProvider;

/**
 * Class SecurityRoleProvider
 * @package App\Modules\Security\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityRoleProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = SecurityRole::class;

    /**
     * canDelete
     * @param SecurityRole $role
     * @return bool
     * 29/06/2020 10:34
     */
    public function canDelete(SecurityRole $role)
    {
        if ($this->getRepository(Action::class)->countRoleUse($role) > 0) {
            return false;
        }
        return $this->getRepository()->countRoleUseAsChild($role) === 0;
    }
}

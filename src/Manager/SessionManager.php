<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 15/04/2020
 * Time: 13:21
 */

namespace App\Manager;

use App\Modules\Security\Entity\Role;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SessionManager
 * @package App\Manager
 */
class SessionManager
{
    /**
     * Cache translated FastFinder actions to allow searching actions with the current locale
     * @param Role $role
     * @param Session $session
     * @return array
     */
    public static function cacheFastFinderActions(Role $role, Session $session):array
    {
        // Get the accessible actions for the current user
        $result = ProviderFactory::create(Module::class)->findByRole($role, false);
        $actions = [];
        if (count($result) > 0) {
            // Translate the action names
            foreach($result as $row)
            {
                $row['name'] = TranslationHelper::translate($row['name'], [], $row['name']);
                $actions[] = $row;
            }

            // Cache the resulting set of translated actions
            $session->set('fastFinderActions', $actions);
        }
        return $actions;
    }

}
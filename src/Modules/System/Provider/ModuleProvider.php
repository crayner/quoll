<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/07/2019
 * Time: 15:17
 */

namespace App\Modules\System\Provider;

use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Modules\System\Entity\Module;
use App\Modules\System\Entity\Setting;
use App\Util\CacheHelper;

/**
 * Class ModuleProvider
 * @package App\Modules\System\Provider
 */
class ModuleProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Module::class;

    /**
     * selectModulesByRole
     * @param bool $byCategory
     * @return mixed
     */
    public function buildFastFinder(bool $byCategory = true)
    {
        $settingProvider = ProviderFactory::create(Setting::class);
        $mainMenuCategoryOrder = $settingProvider->getSettingByScope('System', 'mainMenuCategoryOrder');

        $result = $this->buildMainMenu(UserHelper::getCurrentSecurityUser());
        $sorted = [];
        foreach(explode(',', $mainMenuCategoryOrder) as $category)
        {
            if ($byCategory && !isset($sorted[$category])) {
                $sorted[$category] = [];
            }
            foreach($result as $module)
            {
                $mod = [];
                $mod['name'] = $module->getName();
                $mod['category'] = $module->getCategory();
                $mod['type'] = $module->getType();
                $mod['entryRoute'] = $module->getEntryRoute();
                $mod['textDomain'] = $mod['name'];
                if ($mod['category'] === $category && $byCategory)
                {
                    $sorted[$category][] = $mod;
                } elseif (!$byCategory) {
                    $sorted[] = $mod;
                }
            }
            if (!$byCategory)
                break;
        }

        return $sorted;
    }

    /**
     * @var array|null
     */
    private $mainMenu;

    /**
     * buildMainMenu
     * @return array
     */
    public function buildMainMenu(SecurityUser $user): array
    {
        if (null === $this->mainMenu) {
            if (CacheHelper::isStale('mainMenu', 30)) {
                $this->mainMenu = $this->getRepository()->findBy(['active' => 'Y'], ['category' => 'ASC', 'name' => 'ASC']);
                CacheHelper::setCacheValue('mainMenu', $this->mainMenu, 30);
            } else {
                $this->mainMenu = CacheHelper::getCacheValue('mainMenu');
            }
        }
        return $this->mainMenu;
    }
}
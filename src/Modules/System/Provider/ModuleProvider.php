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

use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Module;
use App\Modules\System\Entity\Setting;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Util\CacheHelper;

/**
 * Class ModuleProvider
 * @package App\Modules\System\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ModuleProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Module::class;

    /**
     * @var array|null
     */
    private $mainMenu;

    /**
     * buildFastFinder
     * @param bool $byCategory
     * @return array
     * 2/07/2020 15:42
     */
    public function buildFastFinder(bool $byCategory = true)
    {
        $sorted = [];
        if (SecurityHelper::getCurrentUser() instanceof SecurityUser) {
            $settingProvider = ProviderFactory::create(Setting::class);
            $mainMenuCategoryOrder = $settingProvider->getSettingByScope('System', 'mainMenuCategoryOrder');

            $result = $this->buildMainMenu(SecurityHelper::getCurrentUser());
            foreach (explode(',', $mainMenuCategoryOrder) as $category) {
                if ($byCategory && !isset($sorted[$category])) {
                    $sorted[$category] = [];
                }
                foreach ($result as $module) {
                    $mod = [];
                    $mod['name'] = $module->getName();
                    $mod['category'] = $module->getCategory();
                    $mod['type'] = $module->getType();
                    $mod['entryRoute'] = $module->getEntryRoute();
                    $mod['textDomain'] = $mod['name'];
                    if ($mod['category'] === $category && $byCategory) {
                        $sorted[$category][] = $mod;
                    } elseif (!$byCategory) {
                        $sorted[] = $mod;
                    }
                }
                if (!$byCategory)
                    break;
            }
        }
        return $sorted;
    }

    /**
     * buildMainMenu
     * @param SecurityUser $user
     * @return array
     * 2/07/2020 15:41
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
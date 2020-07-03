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
 * Date: 29/07/2019
 * Time: 12:08
 */
namespace App\Twig;

use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use App\Util\CacheHelper;
use App\Util\TranslationHelper;

/**
 * Class MainMenu
 * @package App\Twig
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MainMenu implements ContentInterface
{
    use ContentTrait;

    /**
     * @var bool|array
     */
    private $content;

    /**
     * execute
     * 10/06/2020 11:28
     */
    public function execute(): void 
    {
        if (!$this->hasSession())
            return;

        $this->content = false;
        $user = SecurityHelper::getCurrentUser();
        if ($user instanceof SecurityUser) {
            if (CacheHelper::isStale('mainMenuItems', 30)) {
                $menuMainItems = ProviderFactory::create(Module::class)->buildMainMenu($user);

                $items = [];
                foreach ($menuMainItems as $q => $module)
                    $items[] = $module->toArray('mainMenu');

                $category = '';
                $group = [];
                $menuMainItems = [];
                foreach($items as $w) {
                    if ($w['category'] !== $category) {
                        if ($category !== '') {
                            $menuMainItems[TranslationHelper::translate($category)] = $group;
                            $group = [];
                        }
                        $category = $w['category'];
                    }
                    $group[] = $w;
                }
                if (count($group) > 0)
                    $menuMainItems[TranslationHelper::translate($category)] = $group;

                CacheHelper::setCacheValue('menuMainItems', $menuMainItems);
            } else {
                $menuMainItems = CacheHelper::getCacheValue('mainMenuItems');
            }
            $this->addAttribute('menuMainItems', $menuMainItems);
        } else {
            $menuMainItems = false;
            $this->removeAttribute('menuMainItems');
            CacheHelper::clearCacheValue('mainMenuItem');
        }
        $this->content = $menuMainItems;
    }
}
<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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

use App\Manager\ScriptManager;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Security\Util\UserHelper;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * Class MainMenu
 * @package App\Twig
 */
class MainMenu implements ContentInterface
{
    use ContentTrait;

    /**
     * execute
     */
    public function execute(): void 
    {
        $this->content = false;
        $user = UserHelper::getSecurityUser();
        $menuMainItems = false;

        if ($user instanceof SecurityUser) {
            if (! $this->getSession()->has('menuMainItems') || false === $this->getSession()->get('menuMainItems')) {
                $menuMainItems = ProviderFactory::create(Module::class)->selectModulesByRole($this->getSession()->get('gibbonRoleIDCurrent'));
                foreach ($menuMainItems as $category => &$items) {
                    foreach ($items as &$item) {
                        if (strpos($item['entryURL'], '.php') === false) {
                            $route = Action::getRouteName($item['name'], $item['entryURL']);
                            $altRoute = Action::getRouteName($item['name'], $item['alternateEntryURL']);
                            $item['route'] = SecurityHelper::isRouteAccessible($route) ? $route : $altRoute;
                            $item['url'] = false;
                            $moduleName = SecurityHelper::getModuleName($item['route']);
                            $item['name'] = TranslationHelper::translate($item['name'], [], $moduleName);
                            $item['href'] = UrlGeneratorHelper::getUrl($item['route']);
                        } else {
                            $modulePath = '/modules/' . $item['name'];
                            $entryURL = SecurityHelper::isActionAccessible($modulePath . '/' . $item['entryURL'])
                                ? $item['entryURL']
                                : $item['alternateEntryURL'];
                            $item['route'] = false;
                            $item['url'] = $this->getSession()->get('absoluteURL') . '/?q=' . $modulePath . '/' . $entryURL;
                            $item['href'] = $item['url'];
                        }
                    }
                }
                foreach($menuMainItems as $q=>$w) {
                    unset($menuMainItems[$q]);
                    $menuMainItems[TranslationHelper::translate($q)] = $w;
                }
                $this->getSession()->set('menuMainItems', $menuMainItems);
            } else {
                $menuMainItems = $this->getSession()->get('menuMainItems', false);
            }
            $this->addAttribute('menuMainItems', $menuMainItems);
        }
    }
}
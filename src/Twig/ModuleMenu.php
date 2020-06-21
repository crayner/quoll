<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 29/07/2019
 * Time: 13:43
 */

namespace App\Twig;

use App\Modules\System\Entity\Action;
use App\Provider\ProviderFactory;
use App\Util\CacheHelper;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Class ModuleMenu
 * @package App\Twig
 */
class ModuleMenu implements SidebarContentInterface
{
    use SidebarContentTrait;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var boolean
     */
    private $showSidebar = true;

    /**
     * @var ArrayCollection
     */
    private $attributes;

    /**
     * @var string
     */
    private $name = 'Module Menu';

    /**
     * @var string
     */
    private $position = 'middle';

    /**
     * @var Request
     */
    private $request;

    /**
     * execute
     */
    public function execute(): ModuleMenu
    {
        $request = $this->getRequest();
        if (false === $request->attributes->has('action') || false === $request->attributes->get('action') || false === $request->attributes->get('action')->isMenuShow()) {
            return $this;
        }

        if ($request->attributes->has('module') && false !== $request->attributes->get('module'))
        {
            $currentModule = $request->attributes->get('module');
            if (CacheHelper::isStale('moduleMenu_'.$currentModule->getName())) {
                $moduleMenuItems = ProviderFactory::create(Action::class)->moduleMenuItems($currentModule, $this->getChecker());
                $menuItems = [];
                foreach ($moduleMenuItems as $category => &$items) {
                    foreach ($items as &$item) {
                        $item['name'] = $this->translate($item['name'], [], $item['moduleName']);
                        $item['active'] = $request->attributes->get('action') ? in_array($request->attributes->get('action')->getEntryRoute(), $item['routeList']) : false;
                        $item['route'] = $item['entryRoute'];
                        $item['url'] = $this->checkURL($item);
                    }
                    $menuItems[TranslationHelper::translate($category, [], $currentModule->getName())] = $items;
                }
                CacheHelper::setCacheValue('moduleMenu_'.$currentModule->getName(), $menuItems);
            } else {
                $menuItems = CacheHelper::getCacheValue('moduleMenu_'.$currentModule->getName());
            }

            $data = ['data' => $menuItems];
            $data['showSidebar'] = $this->isShowSidebar();
            $data['trans_module_menu'] = $this->translate('Module Menu', [], 'messages');
            $this->setContent($data);
        }

        return $this;
    }

    /**
     * translate
     * @param string $key
     * @param array|null $params
     * @param string|null $domain
     * @return string
     */
    private function translate(string $key, ?array $params = [], ?string $domain = null): string
    {
        return TranslationHelper::translate($key, $params, $domain ?: $this->getDomain());
    }

    /**
     * checkURL
     * @param array $link
     * @return mixed|string
     */
    public function checkURL(array $link)
    {
            return UrlGeneratorHelper::getPath($link['route'], [], Router::ABSOLUTE_URL);
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain ?: 'messages' ;
    }

    /**
     * Domain.
     *
     * @param string $domain
     * @return ModuleMenu
     */
    public function setDomain(string $domain): ModuleMenu
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowSidebar(): bool
    {
        return $this->showSidebar;
    }

    /**
     * ShowSidebar.
     *
     * @param bool $showSidebar
     * @return ModuleMenu
     */
    public function setShowSidebar(bool $showSidebar): ModuleMenu
    {
        $this->showSidebar = $showSidebar;
        $this->execute();
        return $this;
    }

    /**
     * render
     * @return string
     */
    public function render(array $options): string
    {
        return $this->getTwig()->render('default/sidebar/module_menu.html.twig');
    }

    /**
     * getRequest
     * @return Request
     */
    private function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Request.
     *
     * @param Request $request
     * @return ModuleMenu
     */
    public function setRequest(Request $request): ModuleMenu
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes(): ArrayCollection
    {
        if (null === $this->attributes)
            $this->attributes = new ArrayCollection();
        return $this->attributes;
    }

    /**
     * setAttributes
     * @param ArrayCollection $attributes
     * @return ModuleMenu
     */
    public function setAttributes(ArrayCollection $attributes): ModuleMenu
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * addAttribute
     * @param string $name
     * @param $content
     * @return $this
     */
    public function addAttribute(string $name, $content): ModuleMenu
    {
        $this->getAttributes()->set($name, $content);

        return $this;
    }

    /**
     * hasAttribute
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return $this->getAttributes()->containsKey($name);
    }

    /**
     * getAttribute
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        return $this->hasAttribute($name) ? $this->attributes->get($name) : null;
    }

    /**
     * @var bool
     */
    private $valid = true;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid && $this->getAttributes()->count() > 0;
    }

    /**
     * getValid
     * @return bool
     */
    public function getValid(): bool
    {
        return $this->valid;
    }

    /**
     * Valid.
     *
     * @param bool $valid
     * @return ContentTrait
     */
    public function setValid(bool $valid): ContentTrait
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Twig.
     *
     * @param Environment $twig
     * @return ModuleMenu
     */
    public function setTwig(Environment $twig): ModuleMenu
    {
        $this->twig = $twig;
        return $this;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $this->execute();
        return $this->getContent() ?: [];
    }

    /**
     * @var AuthorizationCheckerInterface
     */
    private $checker;

    /**
     * @return AuthorizationCheckerInterface
     */
    public function getChecker(): AuthorizationCheckerInterface
    {
        return $this->checker;
    }

    /**
     * Checker.
     *
     * @param AuthorizationCheckerInterface $checker
     * @return ModuleMenu
     */
    public function setChecker(AuthorizationCheckerInterface $checker): ModuleMenu
    {
        $this->checker = $checker;
        return $this;
    }

}
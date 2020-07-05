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
 * Date: 15/04/2020
 * Time: 14:36
 */
namespace App\Listeners;

use App\Manager\PageManager;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\CacheHelper;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PageListener
 * @package App\Listeners
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PageListener implements EventSubscriberInterface
{
    /**
     * @var PageManager
     */
    private $pageManager;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * PageListener constructor.
     * @param PageManager $pageManager
     * @param ProviderFactory $factory Pre load to Container
     * @param TranslationHelper $helper
     * @param CacheHelper $cache
     * @param SecurityHelper $securityHelper
     * @param SettingFactory $settingFactory
     * @param ParameterBagInterface $parameterBag
     * @param ParameterBagHelper $parameterBagHelper
     */
    public function __construct(
        PageManager $pageManager,
        ProviderFactory $factory,
        TranslationHelper $helper,
        CacheHelper $cache,
        SecurityHelper $securityHelper,
        SettingFactory $settingFactory,
        ParameterBagInterface $parameterBag,
        ParameterBagHelper $parameterBagHelper
    ) {
        $this->setPageManager($pageManager);
        $this->setParameterBag($parameterBag);
        $parameterBagHelper::setParameterBag($parameterBag);
    }

    /**
     * getSubscribedEvents
     * @return array|array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 0],
        ];
    }

    /**
     * onRequest
     * @param RequestEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * 30/05/2020 15:48
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $controller = explode('::', $request->attributes->get('_controller'));

        $route = $request->attributes->get('_route');

        // Ignore Debug Screens
        if (preg_match("#(^(_(profiler|wdt|home))|css|img|build|js|login|logout|error|google|raw)#", $route) || $route === null) {
            return;
        }

        $this->getPageManager()->injectCSS($route);

        $this->getPageManager()->configurePage();

        if (preg_match("#(api)#", $route)) {
            return;
        }

        if ($request->query->has('raw_page')) {
            return;
        }
        if (preg_match("#(popup)#", $route)) {
            $this->getPageManager()->setPopup();
        }


        if ($request->getContentType() !== 'json') {
            $event->setResponse($this->getPageManager()->getBaseResponse());
        }
    }

    /**
     * @return PageManager
     */
    protected function getPageManager(): PageManager
    {
        return $this->pageManager;
    }

    /**
     * PageManager.
     *
     * @param PageManager $pageManager
     * @return PageListener
     */
    protected function setPageManager(PageManager $pageManager): PageListener
    {
        $this->pageManager = $pageManager;
        return $this;
    }

    /**
     * @return ParameterBagInterface
     */
    protected function getParameterBag(): ParameterBagInterface
    {
        return $this->parameterBag;
    }

    /**
     * ParameterBag.
     *
     * @param ParameterBagInterface $parameterBag
     * @return PageListener
     */
    protected function setParameterBag(ParameterBagInterface $parameterBag): PageListener
    {
        $this->parameterBag = $parameterBag;
        return $this;
    }
}
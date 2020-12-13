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
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
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
    private PageManager $pageManager;

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * PageListener constructor.
     * @param PageManager $pageManager
     * @param ParameterBagInterface $parameterBag
     * @param ParameterBagHelper $parameterBagHelper
     * @param SettingFactory $settingFactory
     * @param SecurityHelper $securityHelper
     * @param ProviderFactory $factory
     * @param TranslationHelper $helper
     */
    public function __construct(
        PageManager $pageManager,
        ParameterBagInterface $parameterBag,
        ParameterBagHelper $parameterBagHelper,
        SettingFactory $settingFactory,
        SecurityHelper $securityHelper,
        ProviderFactory $factory,
        TranslationHelper $helper
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
            KernelEvents::TERMINATE => ['saveSettings', 0],
        ];
    }

    /**
     * onRequest
     * @param RequestEvent $event
     * 30/05/2020 15:48
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();


        $definition = $this->getPageManager()->getDefinition();
        $route = $definition->getRoute();
        $controller = $definition->getController();


        // Ignore Debug Screens
        if (preg_match("#(^(_(profiler|wdt|home))|css|img|build|js|login|logout|error|google|raw)#", $route) || $route === null) {
            return;
        }

        $this->getPageManager()->injectCSS($route);

        $this->getPageManager()->configurePage();

        if (preg_match("#(api)#", $route)) {
            return;
        }

        if ($request->query->has('debug')) {
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

    /**
     * saveSettings
     * @param TerminateEvent $event
     * 15/08/2020 09:44
     */
    public function saveSettings(TerminateEvent $event)
    {
        if (!$this->getParameterBag()->has('install_date')) {
            if ($event->getRequest()->hasSession()) $event->getRequest()->getSession()->invalidate(0);
            if (key_exists('APP_ENV', $_SERVER) && $_SERVER['APP_ENV'] === 'prod') {
                $fs = new Filesystem();
                $x = false;
                $y = 0;
                while (!$x) {
                    try {
                        $fs->remove(__DIR__ . '/../../var/cache');
                        $x = true;
                    } catch (IOException $e) {
                        $x = false;
                        sleep(1);
                        if ($y++ > 10) $x = true;
                    }
                }
            }
        }
        $manager = SettingFactory::getSettingManager();
        $manager->writeSettings();
    }
}

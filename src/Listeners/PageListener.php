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
 * Time: 14:36
 */

namespace App\Listeners;

use App\Manager\PageManager;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PageListener
 * @package App\Listeners
 */
class PageListener implements EventSubscriberInterface
{
    /**
     * @var PageManager
     */
    private $pageManager;

    /**
     * PageListener constructor.
     * @param PageManager $pageManager
     * @param ProviderFactory $factory Pre load to Container
     */
    public function __construct(PageManager $pageManager, ProviderFactory $factory)
    {
        $this->pageManager = $pageManager;
    }

    /**
     * getSubscribedEvents
     * @return array|array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 24]
        ];
    }

    /**
     * onRequest
     * @param RequestEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $route = $request->attributes->get('_route');

        // Ignore Debug Screens
        if (preg_match("#(^(_(profiler|wdt|home))|css|img|build|js)#", $route))
            return;

        if ($request->hasSession()) {
            $session = $request->getSession();
            $person = $session->get('person');
            if ($person instanceof Person && $person->getI18nPersonal() !== null)
                $request->setLocale($person->getI18nPersonal()->getCode());
        }

        $this->pageManager->configurePage();

        if ($request->query->has('raw_page'))
            return ;

        if ($request->getContentType() !== 'json')
            $event->setResponse($this->pageManager->getBaseResponse());

    }

}
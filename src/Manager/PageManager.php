<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/02/2020
 * Time: 15:17
 */

namespace App\Manager;

use App\Manager\Entity\BreadCrumbs;
use App\Manager\Entity\HeaderManager;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Twig\FastFinder;
use App\Twig\IdleTimeout;
use App\Twig\MainMenu;
use App\Twig\MinorLinks;
use App\Twig\PageHeader;
use App\Twig\SidebarContent;
use App\Util\ErrorMessageHelper;
use App\Util\Format;
use App\Util\GlobalHelper;
use App\Util\ImageHelper;
use Doctrine\DBAL\Exception\DriverException;
use App\Modules\System\Util\LocaleHelper;
use App\Util\TranslationsHelper;
use App\Util\UrlGeneratorHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Modules\Security\Util\SecurityHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class PageManager
 * @package App\Manager
 */
class PageManager
{
    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var MinorLinks
     */
    private $minorLinks;

    /**
     * @var MainMenu
     */
    private $mainMenu;

    /**
     * @var array
     */
    private $headerLinks;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $checker;

    /**
     * @var string|null
     */
    private $route;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var SidebarContent
     */
    private $sidebar;

    /**
     * @var BreadCrumbs
     */
    private $breadCrumbs;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var IdleTimeout
     */
    private $idleTimeout;

    /**
     * @var FastFinder
     */
    private $fastFinder;

    /**
     * @var Format
     */
    private $format;

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @var PageHeader|null
     */
    private $pageHeader;

    /**
     * @var array
     */
    private $messages;

    /**
     * @var bool
     */
    private $popup = false;

    /**
     * PageManager constructor.
     * @param RequestStack $stack
     * @param MinorLinks $minorLinks
     * @param MainMenu $mainMenu
     * @param AuthorizationCheckerInterface $checker
     * @param SidebarContent $sidebar
     * @param BreadCrumbs $breadCrumbs
     * @param Environment $twig
     * @param IdleTimeout $idleTimeout
     * @param FastFinder $fastFinder
     * @param GlobalHelper $helper
     * @param Format $format
     * @param ImageHelper $imageHelper
     * @param UrlGeneratorHelper $urlGeneratorHelper
     * @param AcademicYearHelper $academicYearHelper
     * @throws \Exception
     */
    public function __construct(
        RequestStack $stack,
        MinorLinks $minorLinks,
        MainMenu $mainMenu,
        AuthorizationCheckerInterface $checker,
        SidebarContent $sidebar,
        BreadCrumbs $breadCrumbs,
        Environment $twig,
        IdleTimeout $idleTimeout,
        FastFinder $fastFinder,
        GlobalHelper $helper,
        Format $format,
        ImageHelper $imageHelper,
        UrlGeneratorHelper $urlGeneratorHelper,
        AcademicYearHelper $academicYearHelper
    ) {
        $this->stack = $stack;
        $this->minorLinks = $minorLinks;
        $this->mainMenu = $mainMenu;
        $this->checker = $checker;
        $this->sidebar = $sidebar;
        $this->breadCrumbs = $breadCrumbs;
        $this->twig = $twig;
        $this->minorLinks->execute();
        $this->idleTimeout =  $idleTimeout;
        $this->fastFinder = $fastFinder;
        $this->format = $format;
        
        $this->configurePage();
    }

    /**
     * @return RequestStack
     */
    public function getStack(): RequestStack
    {
        return $this->stack;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        if (null === $this->request)
            $this->request = $this->getStack()->getCurrentRequest();
        return $this->request;
    }

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * getSession
     * @return SessionInterface
     */
    public function getSession(): ?SessionInterface
    {
        if (null === $this->session && $this->getRequest() && $this->getRequest()->hasSession())
            return $this->session = $this->getRequest()->getSession();
        return $this->session;
    }

    /**
     * getLocale
     * @return string
     */
    public function getLocale()
    {
        return LocaleHelper::getLocale();
    }

    /**
     * writeParameters
     * @return array
     */
    public function writeProperties(): array
    {
        $this->addTranslation('Loading');
        $this->addTranslation('Close');
        $result =  [
            'pageHeader' => $this->getPageHeader(),
            'popup' => $this->isPopup(),
            'locale' => $this->getLocale(),
            'rtl' => LocaleHelper::getRtl($this->getLocale()),
            'bodyImage' => ImageHelper::getBackgroundImage(),
            'minorLinks' => $this->minorLinks->getContent(),
            'headerDetails' => $this->getHeaderDetails(),
            'route' => $this->getRoute(),
            'action' => $this->getRoute() !== 'home' ? $this->getAction() : [],
            'module' => $this->getRoute() !== 'home' ? $this->getModule() : [],
            'url' => UrlGeneratorHelper::getUrl($this->getRoute(), $this->getRequest()->get('_route_params') ?: []),
            'footer' => $this->getFooter(),
            'translations' => $this->getTranslations(),
            'messages' => $this->getMessages(),
        ];
        return $result;
    }

    /**
     * getHeaderDetails
     * @return array
     */
    public function getHeaderDetails(): array
    {
        $details = new HeaderManager($this->getRequest(), $this->checker, $this->mainMenu);
        return $details->toArray();
    }

    /**
     * getAction
     * @return array
     */
    private function getAction(): array
    {
        return SecurityHelper::getActionFromRoute($this->getRoute());
    }

    /**
     * getAction
     * @return array
     */
    private function getModule(): array
    {
        return SecurityHelper::getModuleFromRoute($this->getRoute());
    }

    /**
     * @return string|null
     */
    public function getRoute(): ?string
    {
        if (null === $this->route)
            $this->route = $this->getRequest()->get('_route');
        return $this->route;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        if (null === $this->url)
            $this->url = $this->getRequest()->server->get('REQUEST_URI');
        return $this->url;
    }

    /**
     * getFooter
     * @return array
     */
    private function getFooter(): array
    {
        return [
            'translations' => [
                'Kookaburra' => TranslationsHelper::translate('Kookaburra'),
                'Created under the' => TranslationsHelper::translate('Created under the'),
                'Powered by' => TranslationsHelper::translate('Powered by'),
                'from a fork of' => TranslationsHelper::translate('from a fork of'),
                'licence' => TranslationsHelper::translate('licence'),
            ],
            'footerLogo' => ImageHelper::getAbsoluteImageURL('File', '/build/static/logoFooter.png'),
            'footerThemeAuthor' => TranslationsHelper::translate('Theme {name} by {person}', ['{person}' => 'Craig Rayner', '{name}' => 'Default']),
            'year' => date('Y'),
        ];
    }

    /**
     * render
     * @param array $options
     * @return JsonResponse
     */
    public function render(array $options): JsonResponse
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'content' => '',
                'pagination' => [],
                'breadCrumbs' => [],
                'special' => [],
                'sidebar' => [],
                'containers' => [],
                'messages' => [],
                'title' => $this->getTitle(),
                'url' => $this->getUrl(),
                'popup' => $this->isPopup(),
            ]
        );

        return new JsonResponse(array_merge($resolver->resolve($options), $this->getSidebar()->toArray(), $this->getBreadCrumbs(), ['pageHeader' => $this->getPageHeader()], ['messages' => $this->getMessages()]));
    }

    /**
     * getTitle
     * @return string|null
     */
    private function getTitle()
    {
        if ($this->getRoute() === 'home')
            return '';

        if (strpos($this->getRoute(), 'install__') === 0)
        {
            return 'Installation';
        }

        $title = $this->getAction()['name'];
        if (mb_strpos($title, '_'))
        {
            $title = mb_substr($title, 0, mb_strpos($title, '_'));
        }

        return TranslationsHelper::translate($title, [], str_replace(' ', '', $this->getModule()['name']));
    }

    /**
     * @return SidebarContent
     */
    public function getSidebar(): SidebarContent
    {
        return $this->sidebar;
    }

    /**
     * createBreadcrumbs
     * @param string|array $title
     * @param array $crumbs
     * @return PageManager
     */
    public function createBreadcrumbs($title, array $crumbs = []): PageManager
    {
        $result = [];
        if (is_array($title))
        {
            $params = $title[1];
            $title = $title[0];
        } else {
            $params = [];
        }

        $moduleName = $this->getModule()['name'];
        $domain = str_replace(' ','',$moduleName);
        $result['title'] = TranslationsHelper::translate($title, $params, $domain);
        $result['crumbs'] = $crumbs;
        $result['baseURL'] = strtolower(str_replace(' ','_',$moduleName));
        $result['domain'] = $domain;
        $result['module'] = $moduleName;

        $this->breadCrumbs->create($result);
        return $this;
    }

    /**
     * hasBreadCrumbs
     * @return bool
     */
    private function hasBreadCrumbs(): bool
    {
        return $this->breadCrumbs->isValid();
    }

    /**
     * getBreadCrumbs
     * @return array
     */
    public function getBreadCrumbs(): array
    {
        return ['breadCrumbs' => ($this->hasBreadCrumbs() ? $this->breadCrumbs->toArray() : [])];
    }

    /**
     * writeIdleTimeout
     * @return array
     */
    public function writeIdleTimeout(): array
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY'))
            return [];

        $this->idleTimeout->execute();
        return $this->idleTimeout->getAttributes()->toArray();
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @param $attributes
     * @param null $subject
     * @return bool
     */
    protected function isGranted($attributes, $subject = null): bool
    {
        return $this->checker->isGranted($attributes, $subject);
    }

    /**
     * writeFastFinder
     * @return array
     * @throws \Exception
     */
    public function writeFastFinder(): array
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY'))
            return [];

        $this->fastFinder->execute();
        return $this->fastFinder->getAttributes()->toArray();
    }

    /**
     * configurePage
     */
    public function configurePage(): void
    {
        $this->format->setupFromSession($this->getSession());
        $this->setAddress();
    }

    /**
     * setAddress
     * @param bool $useAddress
     */
    private function setAddress(): void
    {
        $this->getRequest()->attributes->set('address', false);
        $this->getRequest()->attributes->set('module', false);
        $this->getRequest()->attributes->set('action', false);
        // Legacy Settings
        $this->getSession()->set('address', '');
        $this->getSession()->set('module', '');
        $this->getSession()->set('action', '');
        $route = $this->getRequest()->attributes->get('_route');
        if (false === strpos($route, '_ignore_address') && null !== $route) {
            $this->getRequest()->attributes->set('address', $route);
            $this->setModule($route, $this->getRequest());
            $this->getSession()->set('address', $route);
        }
    }

    /**
     * setModule
     * @param string $address
     */
    private function setModule(string $address)
    {
        if (substr($address, -4) === '.php')
        {
            $moduleName = SecurityHelper::getModuleName($address);
        } else {
            $moduleName = explode('__', $address)[0];
            $moduleName = ucwords(str_replace('_', ' ', $moduleName));
        }
        try {
            $module = ProviderFactory::getRepository(Module::class)->findOneByName($moduleName);
        } catch (DriverException $e) {
            $module = null;
        }
        $this->getRequest()->attributes->set('module', $module ?: false);
        $this->getSession()->set('module', $module ? $module->getName() : '');
        if (null !== $module)
            $this->setAction($address, $module);
    }

    /**
     * setAction
     * @param string $address
     * @param Module $module
     */
    private function setAction(string $address, Module $module)
    {
        $address = strpos($address, '__') !== false ? explode('__', $address)[1] : basename($address);
        $action = ProviderFactory::getRepository(Action::class)->findOneByModuleContainsURL($module, $address);
        $this->getRequest()->attributes->set('action', $action);
        $this->getSession()->set('action', $action ? $address : '');
    }

    /**
     * isNotReadyForJSON
     * @param bool $testing
     * @return bool
     */
    public function isNotReadyForJSON(bool $testing = true)
    {
        return $this->getRequest()->getContentType() !== 'json' && $testing;
    }

    /**
     * getBaseResponse
     * @return Response
     */
    public function getBaseResponse() {

        try {
            $content = $this->twig->render('react_base.html.twig',
                [
                    'page' => $this,
                ]
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            throw $e;
            $content = '<h1>Failed!</h1><p>'.$e->getMessage().'</p>';
        }
        return new Response($content);
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Translations.
     *
     * @param string $id
     * @param array $options
     * @param string $domain
     * @return PageManager
     */
    public function addTranslation(string $id, array $options = [], string $domain = 'messages'): PageManager
    {
        $this->translations[$id] = TranslationsHelper::translate($id,$options,$domain);
        return $this;
    }

    /**
     * @return array
     */
    public function getPageHeader(): array
    {
        return $this->pageHeader ? $this->pageHeader->toArray() : [];
    }

    /**
     * PageHeader.
     *
     * @param PageHeader|null $pageHeader
     * @return PageManager
     */
    public function setPageHeader(?PageHeader $pageHeader): PageManager
    {
        $this->pageHeader = $pageHeader;
        return $this;
    }

    /**
     * getFlashMessages
     * @return array
     */
    private function getFlashMessages(): array
    {
        $flashBag = $this->getSession()->getFlashBag();
        $messages = [];
        foreach($flashBag->All() as $status => $list) { // Read and clear
            foreach ($list as $content) {
                if (is_array($content)) {
                    $messages[] = ['class' => $status, 'message' => TranslationsHelper::translate($content[0], $content[1], $content[2])];
                } else
                    $messages[] = ['class' => $status, 'message' => TranslationsHelper::translate($content, [], 'messages')];
            }
        }

        $this->setMessages(array_merge($this->getMessages(false), $messages));
        return $messages;
    }

    /**
     * @param bool $getFlash
     * @return array
     */
    public function getMessages(bool $getFlash = true): array
    {
        if ($getFlash)
            $this->getFlashMessages();
        return $this->messages ?: [];
    }

    /**
     * Messages.
     *
     * @param array $messages
     * @return PageManager
     */
    public function setMessages(array $messages): PageManager
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPopup(): bool
    {
        return $this->popup;
    }

    /**
     * Popup.
     *
     * @param bool $popup
     * @return PageManager
     */
    public function setPopup(bool $popup): PageManager
    {
        $this->popup = $popup;
        return $this;
    }
}
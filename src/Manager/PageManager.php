<?php
/**
 * Created by PhpStorm.
 *
* Quoll
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

use App\Exception\MissingModuleException;
use App\Manager\Traits\IPTrait;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Entity\Module;
use App\Modules\System\Util\LocaleHelper;
use App\Provider\ProviderFactory;
use App\Twig\FastFinder;
use App\Twig\IdleTimeout;
use App\Twig\MainMenu;
use App\Twig\MinorLinks;
use App\Twig\ModuleMenu;
use App\Twig\PageHeader;
use App\Twig\SidebarContent;
use App\Util\Format;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class PageManager
 * @package App\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PageManager
{
    use IPTrait;

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
    private $messages = [];

    /**
     * @var TokenStorageInterface
     */
    private $storage;

    /**
     * @var bool
     */
    private $popup = false;

    /**
     * @var ModuleMenu
     */
    private $moduleMenu;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ArrayCollection
     */
    private $pageStyles;

    /**
     * @var ArrayCollection
     */
    private $pageScripts;

    /**
     * @var string|null
     */
    private $title;

    /**
     * PageManager constructor.
     * @param RequestStack $stack
     * @param MinorLinks $minorLinks
     * @param MainMenu $mainMenu
     * @param ModuleMenu $moduleMenu
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface $storage
     * @param SidebarContent $sidebar
     * @param BreadCrumbs $breadCrumbs
     * @param Environment $twig
     * @param IdleTimeout $idleTimeout
     * @param FastFinder $fastFinder
     * @param Format $format
     * @param ImageHelper $imageHelper
     * @param UrlGeneratorHelper $urlGeneratorHelper
     * @param LoggerInterface $logger
     * @param AcademicYearHelper $academicYearHelper
     */
    public function __construct(
        RequestStack $stack,
        MinorLinks $minorLinks,
        MainMenu $mainMenu,
        ModuleMenu $moduleMenu,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $storage,
        SidebarContent $sidebar,
        BreadCrumbs $breadCrumbs,
        Environment $twig,
        IdleTimeout $idleTimeout,
        FastFinder $fastFinder,
        Format $format,
        ImageHelper $imageHelper,
        UrlGeneratorHelper $urlGeneratorHelper,
        LoggerInterface $logger,
        AcademicYearHelper $academicYearHelper
    ) {
        $this->stack = $stack;
        $this->minorLinks = $minorLinks;
        $this->mainMenu = $mainMenu;
        $this->checker = $checker;
        $this->sidebar = $sidebar;
        $this->breadCrumbs = $breadCrumbs;
        $this->twig = $twig;
        $this->idleTimeout =  $idleTimeout;
        $this->fastFinder = $fastFinder;
        $this->format = $format;
        $this->storage = $storage;
        $this->moduleMenu = $moduleMenu;
        $this->logger = $logger;
        $this->setPageStyles(new ArrayCollection());
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
     * @return bool
     */
    public function hasSession(): bool
    {
        if (null === $this->session) {
            if ($this->getRequest() instanceof Request)
                return $this->getRequest()->hasSession();
        }
        return true;
    }

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
        return LocaleHelper::getLocale($this->getRequest());
    }

    /**
     * writeParameters
     * @return array
     */
    public function writeProperties(): array
    {
        $this->addTranslation('Loading');
        $this->addTranslation('Close');
        $locale = null;
        try {
            $locale = ProviderFactory::getRepository(I18n::class)->findOneByCode($this->getLocale(), $this->request);
        } catch (\PDOException | PDOException | DriverException $e) {
            // Ignore errors.
        }
        if (null === $locale) {
            $locale = new I18n();
            $locale->setCode('en_GB')->setRtl('N');
        }
        return [
            'pageHeader' => $this->getPageHeader(),
            'popup' => $this->isPopup(),
            'locale' => $locale->getCode(),
            'rtl' => $locale->isRtl(),
            'bodyImage' => ImageHelper::getBackgroundImage(),
            'minorLinks' => $this->minorLinks->getContent(),
            'headerDetails' => $this->getHeaderDetails(),
            'route' => $this->getRoute(),
            'action' => $this->getAction() ? $this->getAction()->toArray() : [],
            'module' => $this->getModule() ? $this->getModule()->toArray() : [],
            'url' => UrlGeneratorHelper::getUrl($this->getRoute(), $this->getRequest()->get('_route_params') ?: []) ?: '',
            'footer' => $this->getFooter(),
            'translations' => $this->getTranslations(),
            'messages' => $this->getMessages(),
        ];
    }

    /**
     * getHeaderDetails
     * @return array
     */
    public function getHeaderDetails(): array
    {
        $details = new HeaderManager($this->getRequest(), $this->checker, $this->storage, $this->mainMenu);
        return $details->toArray();
    }

    /**
     * getAction
     * @return Action|null
     */
    private function getAction(): ?Action
    {
        if (in_array($this->getRoute(), [null]))
            return null;
        return SecurityHelper::getActionFromRoute($this->getRoute());
    }

    /**
     * getModule
     * @return Module|null
     */
    private function getModule(): ?Module
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
     * @param string|null $url
     * @return PageManager
     */
    public function setUrl(?string $url): PageManager
    {
        $this->url = $url;
        return $this;
    }

    /**
     * getFooter
     * @return array
     */
    private function getFooter(): array
    {
        return [
            'translations' => [
                'Kookaburra' => TranslationHelper::translate('Quoll', [], 'messages'),
                'Created under the' => TranslationHelper::translate('Created under the', [], 'messages'),
                'Powered by' => TranslationHelper::translate('Powered by', [], 'messages'),
                'from a fork of' => TranslationHelper::translate('from a fork of', [], 'messages'),
                'licence' => TranslationHelper::translate('licence', [], 'messages'),
            ],
            'footerLogo' => ImageHelper::getAbsoluteImageURL('File', '/build/static/logoFooter.png'),
            'footerThemeAuthor' => TranslationHelper::translate('Theme {name} by {person}', ['{person}' => 'Craig Rayner', '{name}' => 'Default'], 'messages'),
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

        if ($this->getPageHeader() === []) {
            $crumbs = isset($this->getBreadCrumbs()['breadCrumbs']) ? $this->getBreadCrumbs()['breadCrumbs'] : [];
            if ($crumbs !== []) {
                $header = end($crumbs);
                $pageHeader = new PageHeader($header['name']);
                if (isset($options['containers'])) {
                    $x = reset($options['containers']);
                    if (isset($x['panels']) && count($x['panels'])) {
                        $pageHeader->setHeaderAttr(['className' => 'page-header with-tabs']);
                    }
                }
                $this->setPageHeader($pageHeader);
            }
        }

        $x = array_merge($resolver->resolve($options), $this->getSidebar()->toArray(), $this->getBreadCrumbs(), ['pageHeader' => $this->getPageHeader()], ['messages' => $this->getMessages()]);
        return new JsonResponse($x);
    }

    /**
     * getTitle
     * @return string|null
     */
    private function getTitle()
    {
        if (null === $this->title) {
            $this->title = 'messages';
            if ($this->getRequest()->attributes->has('_route_params')) {
                $x = $this->getRequest()->attributes->get('_route_params');
                if (!key_exists('module', $x))
                    return '';
                $this->title = ucfirst($x['module']);
            }
        }
        return TranslationHelper::translate($this->title, [], str_replace(' ', '', $this->title));
    }

    /**
     * @param string|null $title
     * @return PageManager
     */
    public function setTitle(?string $title): PageManager
    {
        $this->title = $title;
        return $this;
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

        $moduleName = $this->getModule() ? $this->getModule()->getName() : '';
        $domain = $moduleName === '' ? null : str_replace(' ','',$moduleName);
        $result['title'] = TranslationHelper::translate($title, $params, $domain);
        $result['crumbs'] = $crumbs;
        $result['domain'] = $domain;
        $result['module'] = $moduleName;

        $this->breadCrumbs->create($result, $this->getRequest()->attributes->get('action'));
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
     * @return PageManager
     * 30/05/2020 15:42
     */
    public function configurePage(): PageManager
    {
        if ($this->hasSession()) {
            $this->format->setupFromSession($this->getSession());
        }
        $this->getRequest()->attributes->set('module', false);
        $this->getRequest()->attributes->set('action', false);
        $route = $this->getRequest()->attributes->get('_route');
        if (false === strpos($route, '_ignore_address') && null !== $route) {
            $this->setModule();
        }

        $this->setModuleMenu();

        return $this;
    }

    /**
     * setModule
     * 9/06/2020 09:30
     */
    private function setModule()
    {
        $method = explode('::', $this->getRequest()->attributes->get('_controller'));
        $controller = explode('\\', $this->getRequest()->attributes->get('_controller'));
        $module = null;
        if (in_array($method[1], ['urlRedirectAction'])) {
            $module1 = null;
            $action = null;
            return;
        } else if ($controller[0] === 'App' && $controller[1] === 'Modules' && $controller[3] === 'Controller')
            $module = $this->getModuleName($controller[2]);
        else {
            $this->logger->error(sprintf('The page did not find a valid module for the route: %s', $this->getRequest()->attributes->get('_route')), [$controller]);
            throw new MissingModuleException(implode('\\', $controller));
        }

        try {
            if (! $this->getSession()->has('module') ||
                ! $this->getSession()->get('module') instanceof Module ||
                    $this->getSession()->get('module')->getName() !== $module) {
                $moduleName = $module;
                $module = ProviderFactory::getRepository(Module::class)->findOneByName($module);
                if (!$module)
                    $this->logger->error(sprintf('The page did not find a valid module for the module name: %s', $moduleName));

                $this->getSession()->set('module', $module ?: false);
            } else if ($this->getSession()->has('module') && $this->getSession()->get('module') instanceof Module)
                $module = $this->getSession()->get('module');
        } catch (DriverException $e) {
            $module = null;
        }
        $this->getRequest()->attributes->set('module', $module ?: false);
        if (null !== $module)
            $this->setAction($module);
    }

    /**
     * setAction
     * @param Module $module
     */
    private function setAction(Module $module)
    {
        $route = $this->getRequest()->attributes->get('_route');
        $action = null;
        if (!$this->getSession()->has('action') ||
                !$this->getSession()->get('action') instanceof Action ||
                    !in_array($route, $this->getSession()->get('action')->getrouteList())) {
            $action = ProviderFactory::getRepository(Action::class)->findOneByModuleRoute($module, $route);
            if (!$action)
                $this->logger->error(sprintf('The action was not found for route %s',$route));
            $this->getSession()->set('action', $action ?: false);
        } else if ($this->getSession()->has('action') && $this->getSession()->get('action') instanceof Action)
            $action = $this->getSession()->get('action');
        $this->getRequest()->attributes->set('action', $action ?: false);
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
    public function getBaseResponse()
    {
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
        $this->translations[$id] = TranslationHelper::translate($id,$options,$domain);
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
                    if (key_exists('errors', $content)) {
                        foreach($content['errors'] as $error)
                            $messages[] = ['class' => $error['class'], 'message' => TranslationHelper::translate($error['message'][0], $error['message'][1], $error['message'][2])];
                    } else
                        $messages[] = ['class' => $status, 'message' => TranslationHelper::translate($content[0], $content[1], $content[2])];
                } else
                    $messages[] = ['class' => $status, 'message' => TranslationHelper::translate($content, [], 'messages')];
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
     * addMessage
     * @param string $class
     * @param string $message
     * @return PageManager
     */
    public function addMessage(string $class, string $message): PageManager
    {
        $this->messages[] = ['class' => $class, 'message' => $message];
        return $this;
    }

    /**
     * @return bool
     */
    public function isPopup(): bool
    {
        return (bool)$this->popup;
    }

    /**
     * Popup.
     *
     * @param bool $popup
     * @return PageManager
     */
    public function setPopup(bool $popup = true): PageManager
    {
        $this->popup = $popup;
        return $this;
    }

    /**
     * @return ModuleMenu
     */
    public function getModuleMenu(): ModuleMenu
    {
        return $this->moduleMenu;
    }

    /**
     * setModuleMenu
     * @return PageManager
     */
    private function setModuleMenu(): PageManager
    {
        if (!$this->getModule() || !$this->getAction() || !$this->getAction()->isMenuShow())
            return $this;

        $this->getModuleMenu()->setRequest($this->getRequest())->setChecker($this->getChecker())->execute();

        $this->getSidebar()->addContent($this->getModuleMenu());

        return $this;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public function getChecker(): AuthorizationCheckerInterface
    {
        return $this->checker;
    }

    /**
     * @return ArrayCollection
     */
    public function getPageStyles(): ArrayCollection
    {
        if (null === $this->pageStyles)
            $this->pageStyles = new ArrayCollection();

        return $this->pageStyles;
    }

    /**
     * PageStyles.
     *
     * @param ArrayCollection $pageStyles
     * @return PageManager
     */
    public function setPageStyles(ArrayCollection $pageStyles): PageManager
    {
        $this->pageStyles = $pageStyles;
        return $this;
    }

    /**
     * addPageStyle
     * @param string $style
     * @return $this
     * 22/06/2020 11:52
     */
    public function addPageStyle(string $style): PageManager
    {
        if ($this->getPageStyles()->contains($style))
            return $this;

        $this->pageStyles->add($style);
        return $this;
    }

    /**
     * injectCSS
     * @param string $route
     * @return $this
     * 30/05/2020 15:48
     */
    public function injectCSS(string $route): PageManager
    {
        $this->addPageStyle('css/core');
        if (SecurityHelper::getCurrentUser() instanceof SecurityUser) {
            $this->addPageStyle('css/fastFinder');
        }
        return $this;
    }

    /**
     * getModuleName
     * @param string $name
     * @return string
     * 2/06/2020 14:43
     */
    private function getModuleName(string $name): string
    {
        return trim(implode(' ' , preg_split('/(?=[A-Z])/',$name)));
    }

    /**
     * getPageScripts
     * @return ArrayCollection
     * 22/06/2020 11:54
     */
    public function getPageScripts(): ArrayCollection
    {
        if (null === $this->pageScripts) {
            $this->pageScripts = new ArrayCollection();
        }
        return $this->pageScripts;
    }

    /**
     * @param ArrayCollection $pageScripts
     * @return PageManager
     */
    public function setPageScripts(ArrayCollection $pageScripts): PageManager
    {
        $this->pageScripts = $pageScripts;
        return $this;
    }

    /**
     * addPageScript
     * @param string $script
     * @param array $options
     * @return $this
     * 22/06/2020 11:56
     */
    public function addPageScript(string $script, array $options = []): PageManager
    {
        $object = new \stdClass();
        $object->script = $script;
        $object->options = $options;
        if ($this->getPageScripts()->contains($object)) {
            return $this;
        }

        $this->pageScripts->add($object);

        return $this;
    }
}

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
 * Date: 20/02/2020
 * Time: 15:17
 */
namespace App\Manager;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Manager\Traits\IPTrait;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Locale;
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
     * @var string|array
     */
    private $title = '';

    /**
     * @var string
     */
    private string $domain = '';

    /**
     * @var PageDefinition
     */
    private PageDefinition $definition;

    /**
     * @var WarningManager
     */
    private WarningManager $warningManager;

    /**
     * @var ContainerManager
     */
    private ContainerManager $containerManager;

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
     * @param PageDefinition $definition
     * @param WarningManager $warningManager
     * @param ContainerManager $containerManager
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
        AcademicYearHelper $academicYearHelper,
        PageDefinition $definition,
        WarningManager $warningManager,
        ContainerManager $containerManager
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
        $this->setPageStyles(new ArrayCollection())
            ->setWarningManager($warningManager);
        $definition->setRequest($this->getRequest());
        $this->definition = $definition;
        $this->containerManager = $containerManager;
    }

    /**
     * @return RequestStack
     */
    public function getStack(): RequestStack
    {
        return $this->stack;
    }

    /**
     * getRequest
     *
     * 2/09/2020 10:39
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        if (!isset($this->request) || null === $this->request)
            $this->request = $this->getStack()->getCurrentRequest();
        return $this->request;
    }

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @return bool
     */
    public function hasSession(): bool
    {
        if (!isset($this->session) || null === $this->session) {
            if ($this->getRequest() instanceof Request)
                return $this->getRequest()->hasSession();
        }
        return false;
    }

    /**
     * getSession
     * @return SessionInterface
     */
    public function getSession(): ?SessionInterface
    {
        if ((!isset($this->session) || null === $this->session) && $this->getRequest() && $this->hasSession())
            $this->session = $this->getRequest()->getSession();
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
        $this->addTranslation('Close Message');
        $this->addTranslation('Submit');
        $this->addTranslation('All / None');
        $this->addTranslation('Yes/No');
        $this->addTranslation('File Download');
        $this->addTranslation('Let me ponder your request');
        $this->addTranslation('File Delete');
        $this->addTranslation('Up');
        $this->addTranslation('Down');
        $this->addTranslation('Delete');
        $this->addTranslation('Add');
        $this->addTranslation('Are you sure you want to delete this record?');
        $this->addTranslation('This operation cannot be undone, and may lead to loss of vital data in your system. PROCEED WITH CAUTION!');
        $this->addTranslation('Close', [], 'messages');
        $this->addTranslation('Yes', [], 'messages');
        $this->addTranslation('Filter', [], 'messages');
        $this->addTranslation('Delete', [], 'messages');
        $this->addTranslation('All', [], 'messages');
        $this->addTranslation('Clear', [], 'messages');
        $this->addTranslation('Search for', [], 'messages');
        $this->addTranslation('Filter Select', [], 'messages');
        $this->addTranslation('There are no records to display.', [],'messages');
        $this->addTranslation('Loading Content...', [],'messages');
        $this->addTranslation('Default filtering is enforced.', [], 'messages');
        $this->addTranslation('Close Message', [], 'messages');
        $this->addTranslation('Items rows can be ordered by dragging onto another item, inserting above that item when dropped.', [], 'messages');
        $this->addTranslation('When dropping an item, ensure that the entire row is selected.', [], 'messages');
        $this->addTranslation('Loading', [], 'messages');
        $this->addTranslation('month.short.0', [], 'messages');
        $this->addTranslation('month.short.1', [], 'messages');
        $this->addTranslation('month.short.2', [], 'messages');
        $this->addTranslation('month.short.3', [], 'messages');
        $this->addTranslation('month.short.4', [], 'messages');
        $this->addTranslation('month.short.5', [], 'messages');
        $this->addTranslation('month.short.6', [], 'messages');
        $this->addTranslation('month.short.7', [], 'messages');
        $this->addTranslation('month.short.8', [], 'messages');
        $this->addTranslation('month.short.9', [], 'messages');
        $this->addTranslation('month.short.10', [], 'messages');
        $this->addTranslation('month.short.11', [], 'messages');
        $this->addTranslation('month.long.0', [], 'messages');
        $this->addTranslation('month.long.1', [], 'messages');
        $this->addTranslation('month.long.2', [], 'messages');
        $this->addTranslation('month.long.3', [], 'messages');
        $this->addTranslation('month.long.4', [], 'messages');
        $this->addTranslation('month.long.5', [], 'messages');
        $this->addTranslation('month.long.6', [], 'messages');
        $this->addTranslation('month.long.7', [], 'messages');
        $this->addTranslation('month.long.8', [], 'messages');
        $this->addTranslation('month.long.9', [], 'messages');
        $this->addTranslation('month.long.10', [], 'messages');
        $this->addTranslation('month.long.11', [], 'messages');
        $locale = null;
        try {
            $locale = ProviderFactory::getRepository(Locale::class)->findOneByCode($this->getLocale(), $this->request);
        } catch (\PDOException | PDOException | DriverException $e) {
            // Ignore errors.
        }
        if (null === $locale) {
            $locale = new Locale();
            $locale->setCode('en_GB')->setRtl('N');
        }

        $this->getWarningManager()->getWarnings();

        return [
            'pageHeader' => $this->getPageHeader(),
            'popup' => $this->isPopup(),
            'locale' => $locale->getCode(),
            'rtl' => $locale->isRtl(),
            'bodyImage' => ImageHelper::getBackgroundImage(),
            'minorLinks' => $this->minorLinks->getContent(),
            'headerDetails' => $this->getHeaderDetails(),
            'route' => $this->getRoute(),
            'action' => $this->getDefinition()->getActionArray(),
            'module' => $this->getDefinition()->getModuleArray(),
            'url' => UrlGeneratorHelper::getUrl($this->getRoute(), $this->getRequest()->get('_route_params') ?: []) ?: '',
            'footer' => $this->getFooter(),
            'translations' => $this->getTranslations(),
            'messages' => $this->getMessages(),
            'warning' => $this->getWarningManager()->getStatus(),
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
     *
     * 1/09/2020 17:23
     * @return Action|null
     */
    private function getAction(): ?Action
    {
        return $this->getDefinition()->getAction();
    }

    /**
     * getModule
     *
     * 1/09/2020 17:22
     * @return Module|null
     */
    private function getModule(): ?Module
    {
        return $this->getDefinition()->getModule();
    }

    /**
     * getRoute
     *
     * 1/09/2020 15:51
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->getDefinition()->getRoute();
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
                'translations' => $this->getTranslations(),
                'messages' => [],
                'title' => $this->getTitle(),
                'url' => $this->getUrl(),
                'popup' => $this->isPopup(),
                'redirect' => null,
                'warning' => $this->getWarningManager()->getStatus(),
            ]
        );

        $resolver->setAllowedTypes('redirect', ['null','string']);

        $options = $resolver->resolve($options);

        $this->getWarningManager()->getWarnings();

        if ($this->getPageHeader() === []) {
            $crumbs = isset($this->getBreadCrumbs()['breadCrumbs']) ? $this->getBreadCrumbs()['breadCrumbs'] : [];
            if ($crumbs !== []) {
                $header = end($crumbs);
                $pageHeader = new PageHeader($header['name']);
                $pageHeader->setHeaderAttr(['className' => 'page-header']);
                if (isset($options['containers'])) {
                    $x = reset($options['containers']);
                    if (isset($x['panels']) && count($x['panels']) > 1) {
                        $pageHeader->setHeaderAttr(['className' => 'page-header with-tabs']);
                    }
                }
                $this->setPageHeader($pageHeader);
            }
        }

        $x = array_merge($options, $this->getSidebar()->toArray(), $this->getBreadCrumbs(), ['pageHeader' => $this->getPageHeader()], ['messages' => $this->getMessages()]);
        return new JsonResponse($x);
    }

    /**
     * getTitle
     *
     * 18/10/2020 09:00
     * @return string
     */
    private function getTitle(): string
    {
        if (($this->title === '' || $this->title === []) && isset($this->getBreadCrumbs()['title'])) {
            $this->title = $this->getBreadCrumbs()['title'];
        }

        if (is_array($this->title) && count($this->title) === 3) return TranslationHelper::translate($this->title[0], $this->title[1], $this->title[2]);

        $domain = $this->getModule() ? str_replace(' ', '', $this->getModule()->getName()) : 'messages';
        if (is_array($this->title) && count($this->title) === 2) return TranslationHelper::translate($this->title[0], $this->title[1], $domain);

        return TranslationHelper::translate($this->title, [], $domain);
    }

    /**
     * @param string|array $title
     * @return PageManager
     */
    public function setTitle($title): PageManager
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
     *
     * Will set the title
     * @param string|array $title
     * @param array $crumbs
     * @return PageManager
     */
    public function createBreadcrumbs($title, array $crumbs = []): PageManager
    {
        $result = [];
        if (is_array($title) && count($title) === 3)
        {
            $params = $title[1];
            $domain = $title[2];
            $title = $title[0];
        } else if (is_array($title) && count($title) === 2) {
                $params = $title[1];
                $domain = $this->getModule() instanceof Module ? str_replace(' ', '', $this->getModule()->getName()) : 'messages';
                $title = $title[0];
        } else {
            $params = [];
            $domain = $this->getModule() instanceof Module ? str_replace(' ', '', $this->getModule()->getName()) : 'messages';
        }

        $result['title'] = [$title, $params, $domain];
        $result['crumbs'] = $crumbs;
        $result['domain'] = $domain;
        $result['module'] = $this->getModule() ? $this->getModule()->getName() : '';

        $this->breadCrumbs->create($result, $this->getDefinition());
        if (empty($this->title)) $this->setTitle([$title,$params,$domain]);
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
        $this->getRequest()->attributes->set('_definition', $this->getDefinition());
        $route = $this->getRoute();

        if (false === strpos($route, '_ignore_address') && null !== $route) {
            $this->getModule();
        }

        $this->setModuleMenu();
        TranslationHelper::setDomain($this->getModule() ? $this->getModule()->getName() : 'messages');
        return $this;
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
            $content = $this->twig->render('error/base_response_error.html.twig', ['dumpError' => $e]);
        }
        return new Response($content);
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations = $this->translations === [] ? TranslationHelper::getTranslations() : $this->translations;
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
                    } else {
                        if (count($content) === 3) {
                            try {
                                $messages[] = ['class' => $status, 'message' => TranslationHelper::translate($content[0], $content[1], $content[2])];
                            } catch (\TypeError $e) {
                                throw new \InvalidArgumentException(sprintf('Invalid translation content: %s', serialize($content)));
                            }
                        } else {
                            throw new \InvalidArgumentException(sprintf('Invalid translation content: %s', serialize($content)));
                        }
                    }
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
        if (!$this->getDefinition()->isValidPage() || !$this->getAction()->isMenuShow())
            return $this;

        $this->getModuleMenu()->setRequest($this->getRequest())->setChecker($this->getChecker())->execute();

        $this->getSidebar()->addContent($this->getModuleMenu());
        $this->getWarningManager()->getWarnings();

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

    /**
     * setActionModuleByRoute
     * 29/06/2020 11:52
     */
    private function setActionModuleByRoute()
    {
        $route = $this->getRoute();
        $action = ProviderFactory::getRepository(Action::class)->findOneByLikeRoute($route);
        if ($action !== null) {
            $this->getRequest()->attributes->set('action', $action);
            $this->getRequest()->attributes->set('module', $action->getModule());
            $this->getSession()->set('action', $action);
            $this->getSession()->set('module', $action->getModule());
            SecurityHelper::setAction($action);
            SecurityHelper::setModule($action->getModule());
            $this->logger->debug(sprintf('The action %s was set by the route alone.', $action->getName()));
        }
    }

    /**
     * @return PageDefinition
     */
    public function getDefinition(): PageDefinition
    {
        return $this->definition;
    }

    /**
     * @return WarningManager
     */
    public function getWarningManager(): WarningManager
    {
        return $this->warningManager;
    }

    /**
     * @param WarningManager $warningManager
     * @return PageManager
     */
    public function setWarningManager(WarningManager $warningManager): PageManager
    {
        $this->warningManager = $warningManager;
        return $this;
    }

    /**
     * renderContainer
     *
     * 9/10/2020 08:55
     * @param Container $container
     * @param array $options
     * @return JsonResponse
     */
    public function renderContainer(Container $container, array $options = []): JsonResponse
    {
        $this->getContainerManager()->addContainer($container);
        return $this->render(array_merge($options, ['containers' => $this->getContainerManager()->getBuiltContainers()]));
    }

    /**
     * @return ContainerManager
     */
    public function getContainerManager(): ContainerManager
    {
        return $this->containerManager;
    }

    /**
     * getDomain
     *
     * 12/10/2020 09:49
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain = $this->domain !== '' ? $this->domain : 'messages';
    }

    /**
     * getTwig
     *
     * 16/10/2020 14:16
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }
}

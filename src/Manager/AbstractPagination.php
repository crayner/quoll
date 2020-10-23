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
 * Date: 14/09/2019
 * Time: 11:38
 */
namespace App\Manager;

use App\Manager\Hidden\PaginationGroup;
use App\Manager\Hidden\PaginationRow;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class AbstractPagination
 *
 * 18/10/2020 09:53
 * @package App\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
abstract class AbstractPagination implements PaginationInterface
{
    /**
     * @var integer
     */
    private $pageMax;

    /**
     * @var PaginationRow
     */
    private $row;

    /**
     * @var PaginationGroup
     */
    private PaginationGroup $group;

    /**
     * @var array
     */
    private $content;

    /**
     * @var string
     */
    private $targetElement = 'paginationContent';

    /**
     * @var string|bool
     */
    private $contentLoader = false;

    /**
     * @var array
     */
    private $initialFilter = [];

    /**
     * @var string
     */
    private $initialSearch = '';

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var string|null
     */
    private $storeFilterURL;

    /**
     * @var boolean
     */
    private $sortList = false;

    /**
     * @var boolean
     */
    private $draggableSort = false;

    /**
     * @var string
     */
    private $draggableRoute = '';

    /**
     * @var array|null
     */
    private $addElementRoute;

    /**
     * @var array|null
     */
    private $returnRoute;

    /**
     * @var array|null
     */
    private $refreshRoute;

    /**
     * @var null|string
     */
    private $preContent;

    /**
     * @var array
     */
    private array $context = [];

    /**
     * @var string
     */
    private string $token;

    /**
     * AbstractPaginationManager constructor.
     */
    public function __construct()
    {
        $this->getPageMax();
    }

    /**
     * @return int
     */
    public function getPageMax(): int
    {
        if (null === $this->pageMax)
            $this->pageMax = SettingFactory::getSettingManager()->get('System', 'pagination', 50);
        return $this->pageMax;
    }

    /**
     * PageMax.
     *
     * @param int $pageMax
     * @return AbstractPagination
     */
    public function setPageMax(int $pageMax): AbstractPagination
    {
        $this->pageMax = $pageMax;
        return $this;
    }

    /**
     * @return PaginationRow
     */
    public function getRow(): PaginationRow
    {
        return $this->row = $this->row ?: new PaginationRow();
    }

    /**
     * Row.
     *
     * @param PaginationRow $row
     * @return AbstractPagination
     */
    public function setRow(PaginationRow $row): AbstractPagination
    {
        $this->row = $row;
        return $this;
    }

    /**
     * getGroup
     *
     * 3/09/2020 08:54
     * @return PaginationGroup|null
     */
    public function getGroup(): ?PaginationGroup
    {
        return isset($this->group) ? $this->group : null;
    }

    /**
     * @param PaginationGroup $group
     * @return AbstractPagination
     */
    public function setGroup(PaginationGroup $group): AbstractPagination
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Content.
     *
     * @param array $content
     * @param string|null $serialisationName
     * @return AbstractPagination
     */
    public function setContent(array $content, ?string $serialisationName = null): AbstractPagination
    {
        $this->content = $content;
        return $this->translateContent($serialisationName);
    }

    /**
     * translateContent
     * @param string|null $serialisationName
     * @return AbstractPagination
     */
    private function translateContent(?string $serialisationName): AbstractPagination
    {
        $this->execute();
        foreach($this->getContent() as $q=>$content) {
            $this->content[$q] = gettype($content) === 'object' ? array_merge(['id' => $content->getId()], $content->toArray($serialisationName)) : $content;
            $content = $this->content[$q];

            foreach($this->getRow()->getActions() as $action)
            {
                $params = [];
                foreach($action->getRouteParams() as $name=>$contentName)
                {
                    if (gettype($content) === 'array' && isset($content[$contentName])) {
                        $params[$name] = $content[$contentName];
                    } else if (gettype($content) === 'object') {
                        throw new \InvalidArgumentException(sprintf('The content must be serialised for %s in %s', $contentName, get_class($content)));
                    } else {
                        throw new InvalidOptionsException(sprintf('Not able to correctly collect the content %s ', $contentName));
                    }
                }
                $this->content[$q]['actions'][] = array_merge($action->getRoute(), ['url' => UrlGeneratorHelper::getPath($action->getRoute()['url'], $params)]);
            }
        }
        $columns = new ArrayCollection();
        foreach($this->getRow()->getColumns() as $column)
        {
            if ($column->getContentType() === 'link') {
                $options = $column->getOptions();
                $ro = [];
                foreach((isset($options['route_options']) ? $options['route_options'] : []) as $q=>$w) {
                    $ro[$q] = '__'.$w.'__';
                }
                $options['link'] =  UrlGeneratorHelper::getPath($options['route'], $ro);
                $column->setOptions($options);
            }
            $column->setLabel(TranslationHelper::translate($column->getLabel()));
            $columns->add($column->toArray());
        }
        $this->row->setColumns($columns);

        $actions = new ArrayCollection();
        foreach($this->getRow()->getActions() as $action)
        {
            $actions->add($action->toArray());
        }
        $this->row->setActions($actions);

        $filters = new ArrayCollection();
        foreach($this->row->getFilters() as $filter)
        {
            $filters->set($filter->getName(), $filter->toArray());
        }
        $this->row->setFilters($filters);

        return $this;
    }

    /**
     * toArray
     *
     * 19/10/2020 10:09
     * @return array
     */
    final public function toArray(): array
    {
        $this->getRow()->setToken($this->getToken());
        return [
            'pageMax' => $this->getPageMax(),
            'row' => $this->getRow()->toArray(),
            'group' => $this->getGroup() ? $this->getGroup()->toArray() : ['name' => '', 'contentKey' => ''],
            'context' => $this->getContext(),
            'addElementRoute' => $this->getAddElementRoute(),
            'returnRoute' => $this->getReturnRoute(),
            'refreshRoute' => $this->getRefreshRoute(),
            'sortList' => $this->isSortList(),
            'draggableSort' => $this->isDraggableSort(),
            'draggableRoute' => $this->isDraggableSort() ? UrlGeneratorHelper::getPath($this->getDraggableRoute(), ['target' => '__target__', 'source' => '__source__']) : '',
            'content' => $this->getContent(),
            'contentLoader' => $this->getContentLoader(),
            'translations' => $this->getTranslations(),
            'targetElement' => $this->getTargetElement(),
            'storeFilterURL' => $this->getStoreFilterURL(),
            'initialFilter' => $this->getInitialFilter(),
            'initialSearch' => $this->getInitialSearch(),
            'name' => $this->getPaginationName(),
        ];
    }

    /**
     * getTranslations
     * @return array
     * 9/06/2020 16:47
     */
    public function getTranslations(): array
    {
        TranslationHelper::addTranslation('Are you sure you want to delete this record?', [], 'messages');
        TranslationHelper::addTranslation('This operation cannot be undone, and may lead to loss of vital data in your system. PROCEED WITH CAUTION!', [], 'messages');
        TranslationHelper::addTranslation('Close', [], 'messages');
        TranslationHelper::addTranslation('Yes', [], 'messages');
        TranslationHelper::addTranslation('Filter', [], 'messages');
        TranslationHelper::addTranslation('Delete', [], 'messages');
        TranslationHelper::addTranslation('All', [], 'messages');
        TranslationHelper::addTranslation('Disabled', [], 'messages');
        TranslationHelper::addTranslation('Clear', [], 'messages');
        TranslationHelper::addTranslation('Search for', [], 'messages');
        TranslationHelper::addTranslation('Filter Select', [], 'messages');
        TranslationHelper::addTranslation('There are no records to display.', [],'messages');
        TranslationHelper::addTranslation('Loading Content...', [],'messages');
        TranslationHelper::addTranslation('Default filtering is enforced.', [], 'messages');
        TranslationHelper::addTranslation('Close Message', [], 'messages');
        TranslationHelper::addTranslation('Items rows can be ordered by dragging onto another item, inserting above that item when dropped.', [], 'messages');
        TranslationHelper::addTranslation('When dropping an item, ensure that the entire row is selected.', [], 'messages');
        TranslationHelper::addTranslation('Loading', [], 'messages');
        TranslationHelper::addTranslation('Select action...', [], 'messages');
        TranslationHelper::addTranslation('Selected Row Action', [], 'messages');
        TranslationHelper::addTranslation('Toggle Selection', [], 'messages');
        TranslationHelper::addTranslation('Deselect All', [], 'messages');
        TranslationHelper::addTranslation('Select All', [], 'messages');
        TranslationHelper::addTranslation('This action will result in multiple changes to the database that may not be corrected easily. Do you wish to proceed?', [], 'messages');
        return TranslationHelper::getTranslations();
    }

    /**
     * @return string
     */
    public function getTargetElement(): string
    {
        return $this->targetElement;
    }

    /**
     * TargetElement.
     *
     * @param string $targetElement
     * @return AbstractPagination
     */
    public function setTargetElement(string $targetElement): AbstractPagination
    {
        $this->targetElement = $targetElement;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getContentLoader()
    {
        return $this->contentLoader;
    }

    /**
     * ContentLoader.
     *
     * Url of the content loader
     * @param bool|string $contentLoader
     * @return AbstractPagination
     */
    public function setContentLoader(string $contentLoader)
    {
        $this->contentLoader = $contentLoader;
        $this->setContent([]);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStoreFilterURL(): ?string
    {
        return $this->storeFilterURL;
    }

    /**
     * StoreFilterURL.
     *
     * @param string|null $storeFilterURL
     * @return AbstractPagination
     */
    public function setStoreFilterURL(?string $storeFilterURL): AbstractPagination
    {
        $this->storeFilterURL = $storeFilterURL;
        $this->readFilter();
        return $this;
    }

    /**
     * getStoredFilter
     * @return array
     */
    public function getInitialFilter(): array
    {
        return $this->initialFilter;
    }

    /**
     * InitialFilter.
     *
     * @param array $initialFilter
     * @return AbstractPagination
     */
    public function setInitialFilter(array $initialFilter): AbstractPagination
    {
        $this->initialFilter = $initialFilter;
        return $this;
    }

    /**
     * @return string
     */
    public function getInitialSearch(): string
    {
        return $this->initialSearch;
    }

    /**
     * InitialSearch.
     *
     * @param string $initialSearch
     * @return AbstractPagination
     */
    public function setInitialSearch(string $initialSearch): AbstractPagination
    {
        $this->initialSearch = $initialSearch;
        return $this;
    }

    /**
     * @return RequestStack
     */
    public function getStack(): RequestStack
    {
        if (null === $this->stack)
            trigger_error(sprintf('The request stack has not been injected into the class %s.  Use the calls function to setStack in the service configuration for this class.', get_class($this)), E_USER_ERROR);
        return $this->stack;
    }

    /**
     * Stack.
     *
     * Example in services.yaml:
     *  Kookaburra\Library\Manager\CataloguePagination:
     *      calls:
     *          -   method: setStack
     *      arguments:
     *          - '@request_stack'
     *
     * @param RequestStack $stack
     * @return AbstractPagination
     */
    public function setStack(RequestStack $stack): AbstractPagination
    {
        $this->stack = $stack;
        return $this;
    }

    /**
     * getRequest
     * @return Request|null
     */
    private function getRequest(): Request
    {
        return $this->getStack()->getCurrentRequest();
    }

    /**
     * getSession
     * @return SessionInterface
     */
    private function getSession(): SessionInterface
    {
        return $this->getRequest()->getSession();
    }

    /**
     * readInitialFilter
     * @return array
     */
    public function readFilter(): array
    {
        $session = $this->getSession();
        $name = StringHelper::toSnakeCase(basename(get_class($this)));
        if ($session->has($name)) {
            $data = $session->get($name);
            $this->setInitialFilter(isset($data['filter']) ? $data['filter'] : []);
            $this->setInitialSearch(isset($data['search']) ? $data['search'] : '');
        }
        return $this->getInitialFilter();
    }

    /**
     * writeInitialFilter
     * @param array $filter
     * @return AbstractPagination
     */
    public function writeFilter(array $filter): AbstractPagination
    {
        $session = $this->getSession();
        $name = StringHelper::toSnakeCase(basename(get_class($this)));

        $session->set($name, $filter);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSortList(): bool
    {
        return $this->sortList;
    }

    /**
     * SortList.
     *
     * @param bool $sortList
     * @return AbstractPagination
     */
    public function setSortList(bool $sortList = true): AbstractPagination
    {
        $this->sortList = $sortList;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDraggableSort(): bool
    {
        return $this->draggableSort;
    }

    /**
     * DraggableSort.
     *
     * @param bool $draggableSort
     * @return AbstractPagination
     */
    public function setDraggableSort(bool $draggableSort = true): AbstractPagination
    {
        $this->draggableSort = $draggableSort;
        return $this;
    }

    /**
     * @return string
     */
    public function getDraggableRoute(): string
    {
        return $this->draggableRoute;
    }

    /**
     * DraggableRoute.
     *
     * @param string $draggableRoute
     * @return AbstractPagination
     */
    public function setDraggableRoute(string $draggableRoute): AbstractPagination
    {
        $this->draggableRoute = $draggableRoute;
        if ($draggableRoute !== '') {
            $this->setDraggableSort();
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAddElementRoute(): ?array
    {
        return $this->addElementRoute;
    }

    /**
     * AddElementRoute.
     *
     * @param string|array $addElementRoute
     * @param string|null $addElementPrompt
     * @return AbstractPagination
     */
    public function setAddElementRoute($addElementRoute, ?string $addElementPrompt = null): AbstractPagination
    {
        $addElementRoute = is_string($addElementRoute) ? ['url' => $addElementRoute] : $addElementRoute;

        $this->addElementRoute = self::resolveRoute($addElementRoute);
        if (null !== $addElementPrompt) {
            $addElementPrompt = [$addElementPrompt, [], TranslationHelper::getDomain()];
            $this->getRow()->setAddElement($addElementPrompt);
        }
        return $this;
    }

    /**
     * resolveRoute
     * @param array|null $route
     * @return array|null
     * 6/06/2020 11:19
     */
    public static function resolveRoute(?array $route)
    {
        if (null === $route) {
            return $route;
        }
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'url',
            ]
        );
        $resolver->setDefaults(
            [
                'target' => '_self',
                'options' => '',
                'prompt' => '',
            ]
        );

        $resolver->setAllowedTypes('url', ['string']);
        $resolver->setAllowedTypes('prompt', ['string']);
        $resolver->setAllowedTypes('target', ['string']);
        $resolver->setAllowedTypes('options', ['string']);
        $route = $resolver->resolve($route);
        if ($route['prompt'] !== '') {
            $route['prompt'] = TranslationHelper::translate($route['prompt']);
        }
        return $route;
    }

    /**
     * getPaginationName
     * @return string
     */
    private function getPaginationName(): string
    {
        return basename(get_class($this));
    }

    /**
     * @return array
     */
    public function getReturnRoute(): ?array
    {
        return $this->returnRoute;
    }

    /**
     * ReturnRoute.
     *
     * @param string|array $returnRoute
     * @return AbstractPagination
     */
    public function setReturnRoute($returnRoute): AbstractPagination
    {
        $returnRoute = is_string($returnRoute) ? ['url' => $returnRoute] : $returnRoute;
        $this->returnRoute = self::resolveRoute($returnRoute);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRefreshRoute(): ?array
    {
        return $this->refreshRoute;
    }

    /**
     * RefreshRoute.
     *
     * @param array|string|null $refreshRoute
     * @return AbstractPagination
     */
    public function setRefreshRoute($refreshRoute, ?string $refreshPrompt = null): AbstractPagination
    {
        $refreshRoute = is_string($refreshRoute) ? ['url' => $refreshRoute] : $refreshRoute;
        $this->refreshRoute = self::resolveRoute($refreshRoute);
        if ($refreshPrompt !== null) {
            $this->getRow()->setRefreshPrompt($refreshPrompt);      }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreContent(): string
    {
        return $this->preContent ?: '';
    }

    /**
     * PreContent.
     *
     * @param string|null $preContent
     * @return AbstractPagination
     */
    public function setPreContent(?string $preContent): AbstractPagination
    {
        $this->preContent = $preContent;
        return $this;
    }

    /**
     * getToken
     *
     * 14/09/2020 13:39
     * @return string
     */
    public function getToken(): string
    {
        return $this->token = isset($this->token) ? $this->token : '';
    }

    /**
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @return AbstractPagination
     */
    public function setToken(CsrfTokenManagerInterface  $csrfTokenManager): AbstractPagination
    {
        $this->token = $csrfTokenManager->getToken($this->getPaginationTokenName());
        return $this;
    }

    /**
     * getPaginationTokenName
     *
     * 14/09/2020 13:38
     * @return string
     */
    public function getPaginationTokenName(): string
    {
        return StringUtil::fqcnToBlockPrefix(static::class) ?: '';
    }

    /**
     * getContext
     *
     * 19/10/2020 10:13
     * @param string|null $name
     * @return array|mixed
     */
    public function getContext(?string $name = null)
    {
        return $name && key_exists($name, $this->context) ? $this->context[$name] : $this->context;
    }

    /**
     * setContext
     *
     * 19/10/2020 10:08
     * @param array $context
     * @return AbstractPagination
     */
    public function setContext(array $context): AbstractPagination
    {
        $this->context = $context;
        return $this;
    }

    /**
     * addContent
     *
     * 19/10/2020 10:08
     * @param string $name
     * @param $context
     * @return AbstractPagination
     */
    public function addContext(string $name, $context): AbstractPagination
    {
        $this->context[$name] = $context;
        return $this;
    }
}

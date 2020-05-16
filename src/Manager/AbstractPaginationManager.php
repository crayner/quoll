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
 * Date: 14/09/2019
 * Time: 11:38
 */
namespace App\Manager;

use App\Manager\Entity\PaginationRow;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractPaginationManager
 * @package App\Manager
 */
abstract class AbstractPaginationManager implements PaginationInterface
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
            $this->pageMax = ProviderFactory::create(Setting::class)->getSettingByScopeAsInteger('System', 'pagination', 50);
        return $this->pageMax;
    }

    /**
     * PageMax.
     *
     * @param int $pageMax
     * @return AbstractPaginationManager
     */
    public function setPageMax(int $pageMax): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setRow(PaginationRow $row): AbstractPaginationManager
    {
        $this->row = $row;
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
     * @return AbstractPaginationManager
     */
    public function setContent(array $content): AbstractPaginationManager
    {
        $this->content = $content;
        return $this->translateContent();
    }

    /**
     * translateContent
     * @return AbstractPaginationManager
     * @throws InvalidOptionsException
     */
    private function translateContent(): AbstractPaginationManager
    {
        $this->execute();
        foreach($this->getContent() as $q=>$content) {
            $this->content[$q] = gettype($content) === 'object' ? array_merge(['id' => $content->getId()], $content->toArray()) : $content;
            foreach($this->getRow()->getActions() as $action)
            {
                $action->setTitle(TranslationHelper::translate($action->getTitle()));
                $params = [];
                foreach($action->getRouteParams() as $name=>$contentName)
                {
                    if (gettype($content) === 'array' && isset($content[$contentName])) {
                        $params[$name] = $content[$contentName];
                    } else if (gettype($content) === 'object') {
                        $contentName = 'get' . ucfirst($contentName);
                        if (method_exists($content,$contentName))
                            $params[$name] = $content->$contentName();
                        else
                            throw new InvalidOptionsException(sprintf('The method %s was not found in %s ', $contentName, get_class($content)));
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
     * @return array
     */
    final public function toArray(): array
    {
        return [
            'pageMax' => $this->getPageMax(),
            'row' => $this->getRow()->toArray(),
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
            'preContent' => $this->getPreContent(),
        ];
    }

    /**
     * getTranslations
     * @return array
     */
    public function getTranslations(): array
    {
        TranslationHelper::addTranslation('Are you sure you want to delete this record?', [], 'messages');
        TranslationHelper::addTranslation('This operation cannot be undone, and may lead to loss of vital data in your system. PROCEED WITH CAUTION!', [], 'messages');
        TranslationHelper::addTranslation('Close', [], 'messages');
        TranslationHelper::addTranslation('Yes', [], 'messages');
        TranslationHelper::addTranslation('Filter', [], 'messages');
        TranslationHelper::addTranslation('All', [], 'messages');
        TranslationHelper::addTranslation('Clear', [], 'messages');
        TranslationHelper::addTranslation('Search for', [], 'messages');
        TranslationHelper::addTranslation('Filter Select', [], 'messages');
        TranslationHelper::addTranslation('There are no records to display.', [],'messages');
        TranslationHelper::addTranslation('Loading Content...', [],'messages');
        TranslationHelper::addTranslation('Default filtering is enforced.', [], 'messages');
        TranslationHelper::addTranslation('Close Message', [], 'messages');
        TranslationHelper::addTranslation('Items rows can be dragged into the correct position.', [], 'messages');
        TranslationHelper::addTranslation('Loading', [], 'messages');
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
     * @return AbstractPaginationManager
     */
    public function setTargetElement(string $targetElement): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setContentLoader(string $contentLoader)
    {
        $this->contentLoader = $contentLoader;
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
     * @return AbstractPaginationManager
     */
    public function setStoreFilterURL(?string $storeFilterURL): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setInitialFilter(array $initialFilter): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setInitialSearch(string $initialSearch): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setStack(RequestStack $stack): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function writeFilter(array $filter): AbstractPaginationManager
    {
        $session = $this->getSession();
        $name = StringHelper::toSnakeCase(basename(get_class($this)));
        // @todo Filter modification ???

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
     * @return AbstractPaginationManager
     */
    public function setSortList(bool $sortList): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setDraggableSort(bool $draggableSort = true): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setDraggableRoute(string $draggableRoute): AbstractPaginationManager
    {
        $this->draggableRoute = $draggableRoute;
        return $this;
    }

    /**
     * @return array
     */
    public function getAddElementRoute(): array
    {
        return $this->addElementRoute;
    }

    /**
     * AddElementRoute.
     *
     * @param string|array $addElementRoute
     * @return AbstractPaginationManager
     */
    public function setAddElementRoute($addElementRoute): AbstractPaginationManager
    {
        $addElementRoute = is_string($addElementRoute) ? ['url' => $addElementRoute] : $addElementRoute;

        $this->addElementRoute = self::resolveRoute($addElementRoute);
        return $this;
    }

    /**
     * resolveRoute
     * @param array $route
     * @return array
     */
    public static function resolveRoute(array $route)
    {
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
            ]
        );

        $resolver->setAllowedTypes('url', ['string']);
        $resolver->setAllowedTypes('target', ['string']);
        $resolver->setAllowedTypes('options', ['string']);
        return $resolver->resolve($route);
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
     * @return AbstractPaginationManager
     */
    public function setReturnRoute($returnRoute): AbstractPaginationManager
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
     * @return AbstractPaginationManager
     */
    public function setRefreshRoute($refreshRoute): AbstractPaginationManager
    {
        $refreshRoute = is_string($refreshRoute) ? ['url' => $refreshRoute] : $refreshRoute;
        $this->refreshRoute = self::resolveRoute($refreshRoute);
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
     * @return AbstractPaginationManager
     */
    public function setPreContent(?string $preContent): AbstractPaginationManager
    {
        $this->preContent = $preContent;
        return $this;
    }
}
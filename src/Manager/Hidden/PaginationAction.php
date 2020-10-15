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
 * Time: 12:12
 */

namespace App\Manager\Hidden;

use App\Manager\AbstractPaginationManager;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class PaginationAction
 * @package App\Manager\Entity
 */
class PaginationAction
{
    /**
     * @var array
     */
    private $route;

    /**
     * @var array
     */
    private array $route_params = [];

    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private $aClass = '';

    /**
     * @var string
     */
    private $spanClass;

    /**
     * @var string
     */
    private $columnClass = '';

    /**
     * @var string|boolean
     */
    private $onClick = '';

    /**
     * @var string
     */
    private string $displayWhen = '';

    /**
     * @var array|string|null
     */
    private $options;

    /**
     * @var bool
     */
    private bool $selectRow = false;

    /**
     * @var string
     */
    private string $domain;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $sectionActions;

    /**
     * PaginationAction constructor.
     *
     * 13/10/2020 11:06
     * @param string $domain
     */
    public function __construct(string $domain = 'messages')
    {
        $this->setAClass('p-3 sm:p-0');
        $this->setDomain($domain);
    }

    /**
     * @return array
     */
    public function getRoute(): array
    {
        return $this->route = isset($this->route) ? $this->route : ['url' => 'home'];
    }

    /**
     * Route.
     *
     * @param string|array $route
     * @return PaginationAction
     */
    public function setRoute($route): PaginationAction
    {
        $route = is_string($route) ? ['url' => $route] : $route;
        $this->route = AbstractPaginationManager::resolveRoute($route);
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->route_params;
    }

    /**
     * RouteParams.
     *
     * @param array $route_params
     * @return PaginationAction
     */
    public function setRouteParams(array $route_params): PaginationAction
    {
        $this->route_params = $route_params;
        return $this;
    }

    /**
     * getTitle
     *
     * 7/09/2020 14:43
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title = isset($this->title) ? $this->title : '';
    }

    /**
     * Title.
     *
     * @param string $title
     * @return PaginationAction
     */
    public function setTitle(string $title): PaginationAction
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getAClass(): string
    {
        return $this->aClass;
    }

    /**
     * AClass.
     *
     * @param string $aClass
     * @return PaginationAction
     */
    public function setAClass(string $aClass): PaginationAction
    {
        $this->aClass = $aClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpanClass(): string
    {
        return $this->spanClass = $this->spanClass ?: '';
    }

    /**
     * SpanClass.
     *
     * @param string $spanClass
     * @return PaginationAction
     */
    public function setSpanClass(string $spanClass): PaginationAction
    {
        $this->spanClass = $spanClass;
        return $this;
    }

    /**
     * toArray
     *
     * 13/10/2020 11:24
     * @return array
     */
    public function toArray(): array
    {
       return [
           'spanClass' => $this->getSpanClass(),
           'aClass' => $this->getAClass(),
           'title' => TranslationHelper::translate($this->getTitle(), [], $this->getDomain()),
           'columnClass' => $this->getColumnClass(),
           'onClick' => $this->getOnClick(),
           'displayWhen' => $this->getDisplayWhen(),
           'options' => $this->getOptions(),
           'selectRow' => $this->isSelectRow(),
           'selectActions' => $this->getSectionActionsArray(),
       ];
    }

    /**
     * @return string
     */
    public function getColumnClass(): string
    {
        return $this->columnClass;
    }

    /**
     * ColumnClass.
     *
     * @param string $columnClass
     * @return PaginationAction
     */
    public function setColumnClass(string $columnClass): PaginationAction
    {
        $this->columnClass = $columnClass;
        return $this;
    }

    /**
     * @return string|boolean
     */
    public function getOnClick()
    {
        return $this->onClick;
    }

    /**
     * OnClick.
     *
     * @param string|boolean $onClick
     * @return PaginationAction
     */
    public function setOnClick($onClick): PaginationAction
    {
        if ($onClick === false || is_string($onClick)) {
            $this->onClick = $onClick;
        } else {
            throw new \InvalidArgumentException('$onClick must be a string OR (bool) false.');
        }
        return $this;
    }

    /**
     * getDisplayWhen
     *
     * 13/10/2020 10:56
     * @return string
     */
    public function getDisplayWhen(): string
    {
        return $this->displayWhen ?: '';
    }

    /**
     * DisplayWhen.
     *
     * @param string $displayWhen
     * @return PaginationAction
     */
    public function setDisplayWhen(?string $displayWhen): PaginationAction
    {
        $this->displayWhen = $displayWhen;
        return $this;
    }

    /**
     * @return array|string|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Options.
     *
     * @param array|string|null $options
     * @return PaginationAction
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSelectRow(): bool
    {
        return $this->selectRow;
    }

    /**
     * @param bool $selectRow
     * @return PaginationAction
     */
    public function setSelectRow(bool $selectRow = true): PaginationAction
    {
        $this->selectRow = $selectRow;
        return $this;
    }

    /**
     * getSectionActions
     *
     * 7/09/2020 09:07
     * @return ArrayCollection
     */
    public function getSectionActions()
    {
        return $this->sectionActions = isset($this->sectionActions) ? $this->sectionActions : new ArrayCollection();
    }

    /**
     * @param ArrayCollection $sectionActions
     * @return PaginationAction
     */
    public function setSectionActions(ArrayCollection $sectionActions): PaginationAction
    {
        $this->sectionActions = $sectionActions;
        return $this;
    }

    /**
     * addSectionAction
     *
     * 7/09/2020 09:05
     * @param PaginationSelectAction $sectionAction
     * @return $this
     */
    public function addSectionAction(PaginationSelectAction $sectionAction): PaginationAction
    {
        if ($this->getSectionActions()->contains($sectionAction)) return $this;

        $this->sectionActions->add($sectionAction);
        return $this;
    }

    /**
     * getSectionActionsArray
     *
     * 13/10/2020 10:57
     * @return array
     */
    public function getSectionActionsArray(): array
    {
        $result = [];
        foreach ($this->getSectionActions() as $sectionAction) {
            $result[] = $sectionAction->toArray();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * setDomain
     *
     * 13/10/2020 11:01
     * @param string|null $domain
     * @return $this
     */
    public function setDomain(string $domain = 'messages'): PaginationAction
    {
        if ($domain === 'messages') $domain = TranslationHelper::getDomain();

        $this->domain =$domain;
        return $this;
    }
}

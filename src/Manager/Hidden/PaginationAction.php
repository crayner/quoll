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
    private $route_params = [];

    /**
     * @var string
     */
    private $title;

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
     * @var null|string
     */
    private $displayWhen;

    /**
     * @var array|string|null
     */
    private $options;

    /**
     * @var bool
     */
    private $selectRow = false;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $sectionActions;

    /**
     * PaginationAction constructor.
     */
    public function __construct()
    {
        $this->setAClass('thickbox p-3 sm:p-0');
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
     * @return array
     */
    public function toArray() {
       return [
           'spanClass' => $this->getSpanClass(),
           'aClass' => $this->getAClass(),
           'title' => $this->getTitle(),
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
     * @return bool
     */
    public function getDisplayWhen(): string
    {
        return $this->displayWhen ?: '';
    }

    /**
     * DisplayWhen.
     *
     * @param bool $displayWhen
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
     * getSectionActions
     *
     * 7/09/2020 09:07
     * @return ArrayCollection
     */
    public function getSectionActionsArray(): array
    {
        $result = [];
        foreach ($this->getSectionActions() as $sectionAction) {
            $result[] = $sectionAction->toArray();
        }
        return $result;
    }
}

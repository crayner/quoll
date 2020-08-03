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
 * Time: 11:43
 */

namespace App\Manager\Hidden;

use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaginationRow
 * @package App\Manager\Entity
 */
class PaginationRow
{
    /**
     * @var Collection|PaginationColumn[]
     */
    private $columns;

    /**
     * @var Collection|PaginationAction[]
     */
    private $actions;

    /**
     * @var Collection|PaginationFilter[]
     */
    private $filters;

    /**
     * @var array|null
     */
    private $defaultFilter;

    /**
     * @var array
     */
    private $addElement = ['Add', [], 'messages'];

    /**
     * @var bool|string
     */
    private $special = false;

    /**
     * @var bool|array
     */
    private $highlight = false;

    /**
     * @return PaginationColumn[]|Collection
     */
    public function getColumns(): ArrayCollection
    {
        return $this->columns = $this->columns ?: new ArrayCollection();
    }

    /**
     * Columns.
     *
     * @param PaginationColumn[]|Collection $columns
     * @return PaginationRow
     */
    public function setColumns($columns): PaginationRow
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * addColumn
     * @param PaginationColumn $column
     * @return PaginationRow
     */
    public function addColumn(PaginationColumn $column): PaginationRow
    {
        if (!$this->getColumns()->contains($column)) {
            $this->columns->add($column);
        }
        return $this;
    }

    /**
     * getActions
     * @return ArrayCollection
     */
    public function getActions(): ArrayCollection
    {
        return $this->actions = $this->actions ?: new ArrayCollection();
    }

    /**
     * Actions.
     *
     * @param ArrayCollection $actions
     * @return PaginationRow
     */
    public function setActions(ArrayCollection $actions): PaginationRow
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * Add Action.
     *
     * @param array $actions
     * @return PaginationRow
     */
    public function addAction(PaginationAction $action): PaginationRow
    {
        if (!$this->getActions()->contains($action))
            $this->actions->add($action);
        return $this;
    }

    /**
     * @return PaginationFilter[]|ArrayCollection
     */
    public function getFilters(): ArrayCollection
    {
        return $this->filters = $this->filters ?: new ArrayCollection();
    }

    /**
     * Filters.
     *
     * @param PaginationFilter[]|ArrayCollection $filters
     * @return PaginationRow
     */
    public function setFilters(ArrayCollection $filters): PaginationRow
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Add Filter.
     *
     * @param array $actions
     * @return PaginationRow
     */
    public function addFilter(PaginationFilter $filter): PaginationRow
    {
        if (!$this->getFilters()->contains($filter)) {
            $this->filters->set($filter->getName(), $filter);
            if ($filter->isDefaultFilter()) {
                $this->addDefaultFilter($filter->getName());
            }
        }
        return $this;
    }

    /**
     * toArray
     * @return array
     * 3/08/2020 08:51
     */
    public function toArray(): array
    {
        return [
            'columns' => $this->getColumns()->toArray(),
            'actions' => $this->getActions()->toArray(),
            'filters' => $this->getFilters()->toArray(),
            'actionTitle' => TranslationHelper::translate('Actions', [], 'messages'),
            'emptyContent' => TranslationHelper::translate('There are no records to display.', [], 'messages'),
            'caption' => TranslationHelper::translate('Records {start}-{end} of {total}', [], 'messages'),
            'firstPage' => TranslationHelper::translate('First Page', [], 'messages'),
            'prevPage' => TranslationHelper::translate('Previous Page', [], 'messages'),
            'nextPage' => TranslationHelper::translate('Next Page', [], 'messages'),
            'lastPage' => TranslationHelper::translate('Last Page', [], 'messages'),
            'addElement' => $this->getAddElement(),
            'returnPrompt' => TranslationHelper::translate('Return', [], 'messages'),
            'refreshPrompt' => TranslationHelper::translate('Refresh', [], 'messages'),
            'search' => $this->isSearch(),
            'filterGroups' => $this->isFilterGroups(),
            'defaultFilter' => $this->getDefaultFilter(),
            'special' => $this->getSpecial(),
            'highlight' => $this->getHighlight(),
        ];
    }

    /**
     * @return bool
     */
    public function isSearch(): bool
    {
        foreach($this->getColumns() as $column)
        {
            if ($column['search'])
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isFilterGroups(): bool
    {
        foreach($this->getFilters() as $filter)
        {
            if ($filter['group'] === null)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array|null
     */
    public function getDefaultFilter(): ?array
    {
        return $this->defaultFilter;
    }

    /**
     * DefaultFilter.
     *
     * @param array $defaultFilter
     * @return PaginationRow
     */
    public function setDefaultFilter(array $defaultFilter): PaginationRow
    {
        $this->defaultFilter = [];
        foreach($defaultFilter as $w)
        {
            if (!$this->getFilters()->containsKey($w)) {
                throw new MissingOptionsException(sprintf('The filter name "%s" has not been defined.', $w));
            }
            $this->defaultFilter[$w] = $this->getFilters()->get($w)->toArray();
        }

        return $this;
    }

    /**
     * addDefaultFilter
     * @param string $name
     * @return PaginationRow
     */
    public function addDefaultFilter(string $name) : PaginationRow
    {
        $defaultFilter = $this->getDefaultFilter() ?: [];
        if (!$this->getFilters()->containsKey($name)) {
            throw new MissingOptionsException(sprintf('The filter name "%s" has not been defined.', $w));
        }
        $this->defaultFilter[$name] = $this->getFilters()->get($name)->toArray();

        return $this;
    }

    /**
     * getAddElement
     * @return string
     * 1/06/2020 15:29
     */
    public function getAddElement(): string
    {
        return TranslationHelper::translate($this->addElement[0],$this->addElement[1], $this->addElement[2]);
    }

    /**
     * @param array $addElement
     * @return PaginationRow
     */
    public function setAddElement(array $addElement): PaginationRow
    {
        $this->addElement = $addElement;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getSpecial()
    {
        return $this->special;
    }

    /**
     * @param bool|string $special
     * @return PaginationRow
     */
    public function setSpecial($special): PaginationRow
    {
        $this->special = $special;
        return $this;
    }

    /**
     * getHighlight
     * @return array|bool
     * 3/08/2020 08:50
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * setHighlight
     * @param array $highlight
     * @return $this
     * 3/08/2020 08:50
     */
    public function addHighlight(array $highlight)
    {
        if (is_bool($this->getHighlight())) $this->highlight = [];
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'className',
                'columnKey',
                'columnValue',
            ]
        );
        $resolver->setAllowedValues('className', ['error','warning','success','info','primary']);

        $this->highlight[] = $resolver->resolve($highlight);
        return $this;
    }
}
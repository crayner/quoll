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
     * @var string
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
        return $this->route;
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
        return $this->spanClass;
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
     * @return string
     */
    public function getOnClick(): string
    {
        return $this->onClick;
    }

    /**
     * OnClick.
     *
     * @param string $onClick
     * @return PaginationAction
     */
    public function setOnClick(string $onClick): PaginationAction
    {
        $this->onClick = $onClick;
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
}
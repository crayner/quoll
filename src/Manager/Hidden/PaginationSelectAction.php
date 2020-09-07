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
 * Date: 7/09/2020
 * Time: 08:59
 */
namespace App\Manager\Hidden;

use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;

/**
 * Class PaginationSelectAction
 * @package App\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PaginationSelectAction
{
    /**
     * @var string
     */
    private string $route;

    /**
     * @var array
     */
    private array $routeParams = [];

    /**
     * @var string
     */
    private string $prompt;

    /**
     * @var array
     */
    private array $promptParams = [];

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return PaginationSelectAction
     */
    public function setRoute(string $route): PaginationSelectAction
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @param array $routeParams
     * @return PaginationSelectAction
     */
    public function setRouteParams(array $routeParams): PaginationSelectAction
    {
        $this->routeParams = $routeParams;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrompt(): string
    {
        return $this->prompt;
    }

    /**
     * @param string $prompt
     * @return PaginationSelectAction
     */
    public function setPrompt(string $prompt): PaginationSelectAction
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * @return array
     */
    public function getPromptParams(): array
    {
        return $this->promptParams;
    }

    /**
     * @param array $promptParams
     * @return PaginationSelectAction
     */
    public function setPromptParams(array $promptParams): PaginationSelectAction
    {
        $this->promptParams = $promptParams;
        return $this;
    }

    /**
     * toArray
     *
     * 7/09/2020 09:33
     * @return array
     */
    public function toArray(): array
    {
        return [
            'route' => UrlGeneratorHelper::getUrl($this->getRoute(), $this->getRouteParams()),
            'prompt' => TranslationHelper::translate($this->getPrompt(), $this->getPromptParams()),
        ];
    }
}

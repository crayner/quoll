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
 * Date: 13/03/2020
 * Time: 12:13
 */
namespace App\Twig;

/**
 * Class PageHeader
 * @package App\Twig
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PageHeader
{
    /**
     * @var string
     */
    private $header;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var array
     */
    private $headerAttr;

    /**
     * @var array
     */
    private $contentAttr;

    /**
     * @var string|null
     */
    private $returnRoute;

    /**
     * @var string|null
     */
    private $addElementRoute;

    /**
     * PageHeader constructor.
     * @param string $header
     */
    public function __construct(string $header)
    {
        $this->header = $header;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header ?: '';
    }

    /**
     * Header.
     *
     * @param string $header
     * @return PageHeader
     */
    public function setHeader(string $header): PageHeader
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content ?: '';
    }

    /**
     * Content.
     *
     * @param string|null $content
     * @return PageHeader
     */
    public function setContent(?string $content): PageHeader
    {
        $this->content = $content;
        return $this;
    }

    /**
     * getHeaderAttr
     * @return array|string[]
     * 5/06/2020 09:50
     */
    public function getHeaderAttr(): array
    {
        return $this->headerAttr ?: ['className' => 'page-header'];
    }

    /**
     * HeaderAttr.
     *
     * @param array $headerAttr
     * @return PageHeader
     */
    public function setHeaderAttr(array $headerAttr): PageHeader
    {
        $this->headerAttr = $headerAttr;
        return $this;
    }

    /**
     * @return array
     */
    public function getContentAttr(): array
    {
        return $this->contentAttr ?: [];
    }

    /**
     * ContentAttr.
     *
     * @param array $contentAttr
     * @return PageHeader
     */
    public function setContentAttr(array $contentAttr): PageHeader
    {
        $this->contentAttr = $contentAttr;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnRoute(): string
    {
        return $this->returnRoute ?: '';
    }

    /**
     * ReturnRoute.
     *
     * @param string|null $returnRoute
     * @return PageHeader
     */
    public function setReturnRoute(?string $returnRoute): PageHeader
    {
        $this->returnRoute = $returnRoute;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddElementRoute(): string
    {
        return $this->addElementRoute ?: '';
    }

    /**
     * AddElementRoute.
     *
     * @param string|null $addElementRoute
     * @return PageHeader
     */
    public function setAddElementRoute(?string $addElementRoute): PageHeader
    {
        $this->addElementRoute = $addElementRoute;
        return $this;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        return [
            'header' => $this->getHeader(),
            'headerAttr' => $this->getHeaderAttr(),
            'content' => $this->getContent(),
            'contentAttr' => $this->getContentAttr(),
            'returnRoute' => $this->getReturnRoute(),
            'addElementRoute' => $this->getAddElementRoute(),
        ];
    }
}
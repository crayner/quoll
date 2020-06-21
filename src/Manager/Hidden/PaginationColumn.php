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
 * Time: 11:44
 */

namespace App\Manager\Hidden;

/**
 * Class PaginationColumn
 * @package App\Manager\Entity
 */
class PaginationColumn
{
    /**
     * @var string|null
     */
    private $label;

    /**
     * @var string|null
     */
    private $help;

    /**
     * @var string|array|null
     */
    private $contentKey;

    /**
     * @var bool
     */
    private $sort = false;

    /**
     * @var string
     */
    private $headerClass = '';

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var string
     */
    private $contentType = 'standard';

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var bool
     */
    private $search = false;

    /**
     * @var bool
     */
    private $dataOnly = false;

    /**
     * @var array|null
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $translate = false;

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Label.
     *
     * @param string|null $label
     * @return PaginationColumn
     */
    public function setLabel(?string $label): PaginationColumn
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * Help.
     *
     * @param string|null $help
     * @return PaginationColumn
     */
    public function setHelp(?string $help): PaginationColumn
    {
        $this->help = $help;
        return $this;
    }

    /**
     * getContentKey
     * @return array|string
     */
    public function getContentKey()
    {
        return $this->contentKey;
    }

    /**
     * ContentKey.
     *
     * @param string|array|null $contentKey
     * @return PaginationColumn
     */
    public function setContentKey($contentKey): PaginationColumn
    {
        $this->contentKey = is_string($contentKey) ? [$contentKey] : $contentKey ;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSort(): bool
    {
        return $this->sort;
    }

    /**
     * Sort.
     *
     * @param bool $sort
     * @return PaginationColumn
     */
    public function setSort(bool $sort = true): PaginationColumn
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Class.
     *
     * @param string $class
     * @return PaginationColumn
     */
    public function setClass(string $class): PaginationColumn
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * ContentType.
     *
     * @param string $contentType
     * @return PaginationColumn
     */
    public function setContentType(string $contentType): PaginationColumn
    {
        $this->contentType = in_array($contentType, ['image','standard','link']) ? $contentType : 'standard';
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Options.
     *
     * @param array $options
     * @return PaginationColumn
     */
    public function setOptions(array $options): PaginationColumn
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeaderClass(): string
    {
        return $this->headerClass;
    }

    /**
     * HeaderClass.
     *
     * @param string $headerClass
     * @return PaginationColumn
     */
    public function setHeaderClass(string $headerClass): PaginationColumn
    {
        $this->headerClass = $headerClass;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSearch(): bool
    {
        return $this->search;
    }

    /**
     * Search.
     *
     * @param bool $search
     * @return PaginationColumn
     */
    public function setSearch(bool $search = true): PaginationColumn
    {
        $this->search = $search ?: true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDataOnly(): bool
    {
        return $this->dataOnly;
    }

    /**
     * DataOnly.
     *
     * @param bool $dataOnly
     * @return PaginationColumn
     */
    public function setDataOnly(bool $dataOnly = true): PaginationColumn
    {
        $this->dataOnly = $dataOnly;
        return $this;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $result = (array) $this;
        $x = [];
        foreach($result as $q=>$w)
            $x[str_replace("\x00App\Manager\Hidden\PaginationColumn\x00", '', $q)] = $w;
        return $x;
    }

    /**
     * @return array|null
     */
    public function getDefaultValue(): ?array
    {
        return $this->defaultValue;
    }

    /**
     * DefaultValue.
     *
     * @param array|null $defaultValue
     * @return PaginationColumn
     */
    public function setDefaultValue(?array $defaultValue): PaginationColumn
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTranslate(): bool
    {
        return $this->translate;
    }

    /**
     * Translate.
     *
     * @param bool $translate
     * @return PaginationColumn
     */
    public function setTranslate(bool $translate = true): PaginationColumn
    {
        $this->translate = $translate;
        return $this;
    }
}
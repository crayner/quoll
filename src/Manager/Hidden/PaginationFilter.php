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
 * Date: 12/12/2019
 * Time: 12:47
 */

namespace App\Manager\Hidden;
use App\Util\TranslationHelper;

/**
 * Class PaginationFilter
 * @package App\Manager\Entity
 */
class PaginationFilter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $contentKey;

    /**
     * @var string|null
     */
    private $group;

    /**
     * @var bool
     */
    private $defaultFilter = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Name.
     *
     * @param string $name
     * @return PaginationFilter
     */
    public function setName(string $name): PaginationFilter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Label.
     *
     * @param string|array $label
     * @return PaginationFilter
     */
    public function setLabel($label): PaginationFilter
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Value.
     *
     * @param mixed $value
     * @return PaginationFilter
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentKey(): string
    {
        return $this->contentKey;
    }

    /**
     * ContentKey.
     *
     * @param string $contentKey
     * @return PaginationFilter
     */
    public function setContentKey(string $contentKey): PaginationFilter
    {
        $this->contentKey = $contentKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Group.
     *
     * @param string $group
     * @return PaginationFilter
     */
    public function setGroup(string $group): PaginationFilter
    {
        $this->group = $group;
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
            $x[str_replace("\x00App\Manager\Hidden\PaginationFilter\x00", '', $q)] = $w;
        if (is_array($x['label']))
            $x['label'] = TranslationHelper::translate($x['label'][0],$x['label'][1],$x['label'][2]);
        else
            $x['label'] = TranslationHelper::translate($x['label'] ?: $x['name']);
        $x['group'] = $x['group'] ? TranslationHelper::translate($x['group']) : null;
        return $x;
    }

    /**
     * @return bool
     */
    public function isDefaultFilter(): bool
    {
        return $this->defaultFilter;
    }

    /**
     * DefaultFilter.
     *
     * @param bool $defaultFilter
     * @return PaginationFilter
     */
    public function setDefaultFilter(bool $defaultFilter = true): PaginationFilter
    {
        $this->defaultFilter = $defaultFilter;
        return $this;
    }
}
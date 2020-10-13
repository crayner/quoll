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
    private string $name;

    /**
     * @var string|array
     */
    private $label;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private string $contentKey;

    /**
     * @var string|null
     */
    private string $group;

    /**
     * @var bool
     */
    private bool $defaultFilter = false;

    /**
     * @var bool
     */
    private bool $softMatch = true;

    /**
     * @var bool
     */
    private bool $translated = false;

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
     * getLabel
     *
     * 12/10/2020 13:55
     * @return string
     */
    public function getLabel(): string
    {
        if ($this->isTranslated()) return $this->label;

        if (isset($this->label) && is_array($this->label)) {
            $this->label = TranslationHelper::translate($this->label[0],$this->label[1],$this->label[2]);
        }

        if (!isset($this->label)) $this->label = TranslationHelper::translate($this->name);

        $this->setTranslated(true);

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
        $x['label'] = $this->getLabel();
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

    /**
     * @return bool
     */
    public function isSoftMatch(): bool
    {
        return $this->softMatch;
    }

    /**
     * @param bool $softMatch
     * @return PaginationFilter
     */
    public function setSoftMatch(bool $softMatch = true): PaginationFilter
    {
        $this->softMatch = $softMatch;
        return $this;
    }

    /**
     * setExactMatch
     * @return PaginationFilter
     * 21/07/2020 09:48
     */
    public function setExactMatch(): PaginationFilter
    {
        return $this->setSoftMatch(false);
    }

    /**
     * @return bool
     */
    public function isTranslated(): bool
    {
        return $this->translated;
    }

    /**
     * @param bool $translated
     * @return PaginationFilter
     */
    public function setTranslated(bool $translated): PaginationFilter
    {
        $this->translated = $translated;
        return $this;
    }

}

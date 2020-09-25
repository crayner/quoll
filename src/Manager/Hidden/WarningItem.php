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
 * Date: 25/09/2020
 * Time: 15:38
 */
namespace App\Manager\Hidden;


class WarningItem
{
    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $label;

    /**
     * @var string
     */
    private string $level = 'warning';

    /**
     * @var string
     */
    private string $link;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return isset($this->title) ? $this->title : null;
    }

    /**
     * @param string $title
     * @return WarningItem
     */
    public function setTitle(string $title): WarningItem
    {
        $this->title = $title;
        if ($this->getLabel() === null) $this->setLabel($title);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return isset($this->label) ? $this->label : null;
    }

    /**
     * @param string $label
     * @return WarningItem
     */
    public function setLabel(string $label): WarningItem
    {
        $this->label = $label;
        if ($this->getTitle() === null) $this->setTitle($label);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return isset($this->level) ? $this->level : null;
    }

    /**
     * @param string $level
     * @return WarningItem
     */
    public function setLevel(string $level): WarningItem
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return isset($this->link) ? $this->link : null;
    }

    /**
     * @param string $link
     * @return WarningItem
     */
    public function setLink(string $link): WarningItem
    {
        $this->link = $link;
        return $this;
    }

    /**
     * toArray
     *
     * 25/09/2020 15:45
     * @return array
     */
    public function toArray(): array
    {
        return [
            'label' => $this->getLabel(),
            'title' => $this->getTitle(),
            'level' => $this->getLevel(),
            'link' => $this->getLink(),
        ];
    }
}

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
 * Date: 8/11/2019
 * Time: 12:02
 */

namespace App\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Twig\Environment;

/**
 * Class SidebarContent
 * @package App\Twig
 */
class SidebarContent
{
    /**
     * @var ArrayCollection
     */
    private $content;

    /**
     * @var bool
     */
    private $contentSorted = false;

    /**
     * @var bool
     */
    private $noSidebar = false;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var bool
     */
    private $minimised = false;

    /**
     * @var bool
     */
    private $docked = false;

    /**
     * @var array
     */
    private static $positionList = [
        'top','middle','bottom'
    ];

    /**
     * SidebarContent constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * execute
     */
    public function execute(): void
    {
        $this->getContent(true);
    }

    /**
     * getContent
     * @param bool $refresh
     * @return ArrayCollection
     */
    public function getContent(bool $refresh = false): ArrayCollection
    {
        $this->content = $this->content ?: new ArrayCollection();
        if ($this->content->count() > 0 && (!$this->isContentSorted() || $refresh)) {
            try {
                $iterator = $this->content->getIterator();
            } catch (\Exception $e) {
            }
            $iterator->uasort(
                function ($a, $b) {
                    $ap = str_pad(1000 - $a->getPriority(), 5, '0', STR_PAD_LEFT);
                    $bp = str_pad(1000 - $b->getPriority(), 5, '0', STR_PAD_LEFT);
                    return $a->getPosition() . $ap < $b->getPosition() . $bp ? 1 : -1;
                }
            );
            $this->content  = new ArrayCollection(iterator_to_array($iterator, true));
            $this->contentSorted = true;
        }

        return $this->content;
    }

    /**
     * Content.
     *
     * @param ArrayCollection $content
     * @return SidebarContent
     */
    public function setContent(ArrayCollection $content): SidebarContent
    {
        $this->content = $content;
        $this->setContentSorted(false);
        return $this;
    }

    /**
     * addContent
     * @param SidebarContentInterface $content
     * @return SidebarContent
     */
    public function addContent(SidebarContentInterface $content): SidebarContent
    {
        if (! in_array($content->getName(), [null, ''])) {
            $content->setTwig($this->getTwig());
            $this->getContent()->set($content->getName(), $content);
            $this->setContentSorted(false);
        }
        return $this;
    }

    /**
     * getSectionContent
     * @param string $position
     * @return ArrayCollection
     */
    public function getSectionContent(string $position): ArrayCollection
    {
        return $this->getContent()->filter(function($entry) use ($position) {
            return $entry->getPosition() == $position;
        });
    }

    /**
     * hasContentMember
     * @param string $name
     * @return bool
     */
    public function hasContentMember(string $name): bool
    {
        return $this->getContent()->containsKey($name);
    }

    /**
     * getContentMember
     * @param string $name
     * @return SidebarContentInterface|null
     */
    public function getContentMember(string $name): ?SidebarContentInterface
    {
        return $this->getContent()->get($name);
    }

    /**
     * hasSectionContent
     * @param string $name
     * @return bool
     */
    public function hasSectionContent(string $name): bool
    {
        return $this->getSectionContent($name)->count() > 0;
    }

    /**
     * @return bool
     */
    public function isContentSorted(): bool
    {
        return $this->contentSorted;
    }

    /**
     * ContentSorted.
     *
     * @param bool $contentSorted
     * @return SidebarContent
     */
    public function setContentSorted(bool $contentSorted): SidebarContent
    {
        $this->contentSorted = $contentSorted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNoSidebar(): bool
    {
        return $this->noSidebar;
    }

    /**
     * NoSidebar.
     *
     * @param bool $noSidebar
     * @return SidebarContent
     */
    public function setNoSidebar(bool $noSidebar): SidebarContent
    {
        $this->noSidebar = $noSidebar;
        if ($this->hasContentMember('Module Menu')) {
            $this->getContentMember('Module Menu')->setShowSidebar(!$noSidebar);
        }
        return $this;
    }

    private $valid = false;
    /**
     * isValid
     * @return bool
     */
    public function isValid(): bool
    {
        $this->valid = (! $this->isNoSidebar() && $this->getContent()->count() > 0);
        return $this->valid;
    }

    /**
     * @return array
     */
    public static function getPositionList(): array
    {
        return self::$positionList;
    }

    /**
     * getTwig
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return bool
     */
    public function isMinimised(): bool
    {
        return $this->minimised;
    }

    /**
     * Minimised.
     *
     * On a large screen, allow the user to close the dock.
     * Initial display of sidebar is minimised.
     * This setting overrides the docked setting.
     * @param bool $minimised
     * @return SidebarContent
     */
    public function setMinimised(bool $minimised = true): SidebarContent
    {
        if ($this->hasContentMember('Module Menu')) {
            $this->getContentMember('Module Menu')->setShowSidebar(!$minimised);
        }
        $this->minimised = $minimised;
        return $this;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $content = [];
        $result = [];

        foreach($this->sortContent()->toArray() as $name=>$element)
            $content[$name] = array_merge($element->toArray(), $element->getCoreArray());

        $result['content'] = $content;
        $result['minimised'] = $this->isMinimised();
        $result['docked'] = $this->isDocked();
        $content = [];
        $content['sidebar'] = $result;
        return $content;
    }

    /**
     * sortContent
     * @return ArrayCollection
     */
    private function sortContent(): ArrayCollection
    {
        if ($this->isContentSorted())
            return $this->getContent();
        try {
            $iterator = $this->getContent()->getIterator();
        } catch (\Exception $e) {
            return $this->getContent();
        }
        $iterator->uasort(
            function ($a, $b) {
                return $a->sortResult() > $b->sortResult() ? 1 : -1;
            }
        );

        $this->setContent(new ArrayCollection(iterator_to_array($iterator, true)));
        $this->setContentSorted(true);
        return $this->getContent();
    }

    /**
     * @return bool
     */
    public function isDocked(): bool
    {
        return $this->docked;
    }

    /**
     * Docked.
     *
     * Sidebar remains open when the screen changes from large to small width. <br/>
     * Does NOT inhibit ability of user to close sidebar when small screen.
     * This setting is overridden by the minimised setting.
     * @param bool $docked
     * @return SidebarContent
     */
    public function setDocked(bool $docked = true): SidebarContent
    {
        $this->docked = $docked;
        return $this;
    }
}
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
 * Date: 19/08/2019
 * Time: 13:31
 */
namespace App\Container;

use App\Manager\PaginationInterface;
use App\Manager\SpecialInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

/**
 * Class Panel
 * @package App\Container
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Panel
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $content;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @var null|integer
     */
    private $index;

    /**
     * @var array|null
     */
    private $preContent;

    /**
     * @var array|null
     */
    private $postContent;

    /**
     * @var PaginationInterface
     */
    private $pagination;

    /**
     * @var SpecialInterface|null
     */
    private $special;

    /**
     * @var ArrayCollection
     */
    private $sections;

    /**
     * Panel constructor.
     * @param null|string $name
     * @param string|null $translationDomain
     * @param Section|null $section
     */
    public function __construct(?string $name = null, ?string $translationDomain = null, ?Section $section = null)
    {
        $this->setName($name);
        $this->setTranslationDomain($translationDomain);
        $this->setSections(new ArrayCollection());
        if (null !== $section) {
            $this->addSection($section);
        }
    }

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
     * @param null|string $name
     * @return Panel
     */
    public function setName(?string $name): Panel
    {
        $this->name = $name;
        return $this->setLabel($name);
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Disabled.
     *
     * @param bool $disabled
     * @return Panel
     */
    public function setDisabled(bool $disabled): Panel
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Label.
     *
     * @param null|string $label
     * @return Panel
     */
    public function setLabel(?string $label): Panel
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @var array|null
     */
    private ?array $toArrayResult = null;

    /**
     * toArray
     *
     * 18/08/2020 16:11
     * @param bool $refresh
     * @return array
     */
    public function toArray(bool $refresh = false): array
    {
        return $this->toArrayResult = $this->toArrayResult && !$refresh ? $this->toArrayResult : [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'disabled' => $this->isDisabled(),
            'index' => $this->getIndex(),
            'translationDomain' => $this->getTranslationDomain(),
            'sections' => $this->sectionsToArray(),
        ];
    }

    /**
     * @return string|null
     */
    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    /**
     * TranslationDomain.
     *
     * @param string|null $translationDomain
     * @return Panel
     */
    public function setTranslationDomain(?string $translationDomain): Panel
    {
        $this->translationDomain = $translationDomain;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * Index.
     *
     * @param int|null $index
     * @return Panel
     */
    public function setIndex(?int $index): Panel
    {
        $this->index = $index;
        return $this;
    }

    /**
     * debugError
     *
     * 18/08/2020 09:22
     */
    private function debugError()
    {
        $x = debug_backtrace();
        $result = [];
        foreach ($x as $item) {
            $result[] = $item['class'] . ':' . $item['function'] . ' @ ' . $item['line'];
        }
        trigger_error('Use Sections to manage all content in a panel. 20th June 2020 ' . implode(", ",$result), E_USER_DEPRECATED);
    }

    /**
     * @return ArrayCollection
     */
    public function getSections(): ArrayCollection
    {
        if (null === $this->sections) {
            $this->sections = new ArrayCollection();
        }
        return $this->sections;
    }

    /**
     * @param ArrayCollection $sections
     * @return Panel
     */
    public function setSections(ArrayCollection $sections): Panel
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * addSection
     * @param Section $section
     * @return $this
     * 20/06/2020 10:24
     */
    public function addSection(Section $section): Panel
    {
        if ($this->getSections()->contains($section)) {
            return $this;
        }

        $this->sections->add($section);

        return $this;
    }

    /**
     * sectionsToArray
     *
     * 18/08/2020 16:11
     * @return array
     */
    private function sectionsToArray(): array
    {
        $result = [];
        try {
            foreach ($this->getSections()->getIterator() as $section) {
                $result[] = $section->toArray();
            }
        } catch (Exception $e) {}

        return $result;
    }
}
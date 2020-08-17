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
    private $toArrayResult;

    /**
     * toArray
     * @return array
     */
    public function toArray(bool $refresh = false): array
    {
        $result =  $this->toArrayResult = $this->toArrayResult && !$refresh ? $this->toArrayResult : [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'disabled' => $this->isDisabled(),
            'content' => $this->getContent(),
            'index' => $this->getIndex(),
            'translationDomain' => $this->getTranslationDomain(),
            'preContent' => $this->getPreContent(),
            'postContent' => $this->getPostContent(),
            'pagination' => $this->getPagination() ? $this->getPagination()->toArray() : [],
            'special' => $this->getSpecial() ? $this->getSpecial()->toArray() : null,
            'sections' => $this->sectionsToArray(),
        ];

        return $result;
    }

    /**
     * @return null|string
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     */
    public function getContent(): ?string
    {
        $this->debugError();
        return $this->content;
    }

    /**
     * Content.
     *
     * @param string $content
     * @param string|null $contentLoaderTarget
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     * @return Panel
     */
    public function setContent(string $content, ?string $contentLoaderTarget = null): Panel
    {
        $section = new Section('postLoad', $content);
        $this->addSection($section);
        $this->debugError();
        if (is_string($contentLoaderTarget))
            $this->setPreContent([$contentLoaderTarget]);

        $this->content = $content;
        return $this;
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
     * @return array|null
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     */
    public function getPreContent(): ?array
    {
        $this->debugError();
        return $this->preContent;
    }

    /**
     * PreContent.
     * Inject the names of containers for content.
     * @param array|null $preContent
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     * @return Panel
     */
    public function setPreContent(?array $preContent): Panel
    {
        $section = new Section('html', $preContent);
        $this->addSection($section);
        $this->debugError();
        $this->preContent = $preContent;
        return $this;
    }

    /**
     * @return array|null
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     */
    public function getPostContent(): ?array
    {
        $this->debugError();
        return $this->postContent;
    }

    /**
     * PostContent.
     * Inject the names of containers for content.
     * @param array|null $postContent
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     * @return Panel
     */
    public function setPostContent(?array $postContent): Panel
    {
        $section = new Section('html', $postContent);
        $this->addSection($section);
        $this->debugError();
        $this->postContent = $postContent;
        return $this;
    }

    /**
     * getPagination
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     * @return PaginationInterface|null
     */
    public function getPagination(): ?PaginationInterface
    {
        $this->debugError();
        return $this->pagination;
    }

    /**
     * Pagination.
     *
     * @param PaginationInterface $pagination
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     * @return Panel
     */
    public function setPagination(PaginationInterface $pagination): Panel
    {
        $section = new Section('pagination', $pagination);
        $this->addSection($section);
        $this->debugError();
//        $this->content = null;
//        $this->pagination = $pagination;
        return $this;
    }

    /**
     * @return SpecialInterface|null
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     */
    public function getSpecial(): ?SpecialInterface
    {
        $this->debugError();
        return $this->special;
    }

    /**
     * Special.
     *
     * @param SpecialInterface|null $special
     * @deprecated Use Sections to manage all content in a panel. 20th June 2020
     * @return Panel
     */
    public function setSpecial(?SpecialInterface $special): Panel
    {
        $this->debugError();
        $section = new Section('special', $special);
        $this->addSection($section);
        $this->special = $special;
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
     * @return array
     * @throws \Exception
     * 20/06/2020 10:41
     */
    private function sectionsToArray(): array
    {
        $result = [];
        foreach($this->getSections()->getIterator() as $section) {
            $result[] = $section->toArray();
        }

        return $result;
    }
}
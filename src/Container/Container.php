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
 * Date: 19/08/2019
 * Time: 11:21
 */

namespace App\Container;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Container
 * @package App\Container
 */
class Container
{
    /**
     * @var string
     */
    private $target;

    /**
     * @var string
     */
    private $content;

    /**
     * @var ArrayCollection|Panel[]
     */
    private $panels;

    /**
     * @var ArrayCollection
     */
    private $forms;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @var null|string
     */
    private $selectedPanel;

    /**
     * @var null|string
     */
    private $application;

    /**
     * @var array|null
     */
    private $contentLoader;

    /**
     * Container constructor.
     * @param string|null $selectedPanel
     */
    public function __construct(?string $selectedPanel = null)
    {
        $this->setSelectedPanel($selectedPanel);
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target ?: 'formContent';
    }

    /**
     * Target.
     *
     * @param string $target
     * @return Container
     */
    public function setTarget(string $target): Container
    {
        $this->target = $target;
        return $this;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        return [
            'target' => $this->getTarget(),
            'content' => $this->getContent(),
            'panels' => $this->getPanels(),
            'forms' => $this->getForms(),
            'contentLoader' => $this->getContentLoader(),
            'selectedPanel' => $this->getSelectedPanel(),
            'application' => $this->getApplication(),
        ];
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Content.
     *
     * @param string $content
     * @return Container
     */
    public function setContent(string $content): Container
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Panel[]|ArrayCollection
     */
    public function getPanels()
    {
        if (null === $this->panels)
            $this->panels =  new ArrayCollection();

        return $this->panels;
    }

    /**
     * Panels.
     *
     * @param Panel[]|ArrayCollection $panels
     * @return Container
     */
    public function setPanels($panels)
    {
        $this->panels = $panels;
        return $this;
    }

    /**
     * addPanel
     * @param Panel $panel
     * @return Container
     */
    public function addPanel(Panel $panel): Container
    {
        if (null === $panel->getTranslationDomain())
            $panel->setTranslationDomain($this->getTranslationDomain());

        $panel->setIndex($this->getPanels()->count());
        $panel = $this->resolvePanel($panel);

        $this->getPanels()->set($panel->getName(), $panel->toArray());

        return $this;
    }

    /**
     * resolvePanel
     * @param Panel $panel
     * @return Panel
     */
    private function resolvePanel(Panel $panel): Panel
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired(
            [
                'name',
                'label',
                'index',
                'sections',
            ]
        );

        $resolver->setDefaults(
            [
                'disabled' => false,
                'content' => null,
                'preContent' => null,
                'postContent' => null,
                'translationDomain' => 'messages',
                'pagination' => [],
                'special' => null,
            ]
        );

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('disabled', 'boolean');
        $resolver->setAllowedTypes('content', ['string', 'null']);
        $resolver->setAllowedTypes('index', 'integer');
        $resolver->setAllowedTypes('preContent', ['array', 'null']);
        $resolver->setAllowedTypes('postContent', ['array', 'null']);
        $resolver->setAllowedTypes('pagination', ['array', 'null']);
        $resolver->setAllowedTypes('special', ['array', 'null']);
        $resolver->setAllowedTypes('sections', ['array']);

        $resolver->resolve($panel->toArray());

        if ('' === $panel->getName())
            throw new \InvalidArgumentException(sprintf('The panel name is empty!'));

        return $panel;
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
     * @return Container
     */
    public function setTranslationDomain(?string $translationDomain): Container
    {
        $this->translationDomain = $translationDomain;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSelectedPanel(): ?string
    {
        return $this->selectedPanel ?: $this->getPanels()->first()['name'];
    }

    /**
     * SelectedPanel.
     *
     * @param string|null $selectedPanel
     * @return Container
     */
    public function setSelectedPanel(?string $selectedPanel): Container
    {
        $this->selectedPanel = $selectedPanel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApplication(): ?string
    {
        return $this->application;
    }

    /**
     * Application.
     *
     * @param string|null $application
     * @return Container
     */
    public function setApplication(?string $application): Container
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getForms()
    {
        return $this->forms = $this->forms ?: new ArrayCollection();
    }

    /**
     * Forms.
     *
     * @param ArrayCollection $forms
     * @return Container
     */
    public function setForms(ArrayCollection $forms): Container
    {
        $this->forms = $forms;
        return $this;
    }

    /**
     * addForm
     * @param string $name
     * @param FormView $form
     * @return Container
     */
    public function addForm(string $name, FormView $form): Container
    {
        $this->getForms()->set($name, $form->vars['toArray']);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getContentLoader(): ?array
    {
        return $this->contentLoader;
    }

    /**
     * ContentLoader.
     *
     * @param array|null $contentLoader
     * @return Container
     */
    public function setContentLoader(?array $contentLoader): Container
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'route',
                'target',
            ]);
        $resolver->setDefaults([
            'timer' => 0,
            'delay' => 50,
            'type' => 'text',
        ]);
        $resolver->setAllowedTypes('route', 'string');
        $resolver->setAllowedTypes('target', 'string');
        $resolver->setAllowedTypes('timer', 'integer');
        $resolver->setAllowedTypes('delay', 'integer');
        $resolver->setAllowedValues('type', ['text', 'pagination', 'html']);
        $resolver->setAllowedValues('delay', function($value) {
            return $value >= 50;
        });
        foreach($contentLoader as $q=>$content)
            $contentLoader[$q] = $resolver->resolve($content);
        $this->contentLoader = $contentLoader;
        return $this;
    }
}
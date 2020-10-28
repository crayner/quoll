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
 * Time: 11:19
 */
namespace App\Container;

use App\Manager\AbstractPagination;
use App\Util\ReactFormHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContainerManager
 * @package App\Container
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ContainerManager
{
    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var bool
     */
    private $showSubmitButton = false;

    /**
     * @var array
     */
    private $addElementRoute;

    /**
     * @var array
     */
    private $returnRoute;

    /**
     * @var boolean
     */
    private $hideSingleFormWarning = false;

    /**
     * ContainerManager constructor.
     * @param RequestStack $stack
     */
    public function __construct(RequestStack $stack)
    {
        $this->stack = $stack;

        TranslationHelper::addTranslation('Errors on Tab', [], 'messages');
        TranslationHelper::addTranslation('All fields on all panels are saved together.', [], 'messages');
        TranslationHelper::addTranslation('Return', [], 'messages');
        TranslationHelper::addTranslation('Add', [], 'messages');
        TranslationHelper::addTranslation('Erase Content', [], 'messages');
        TranslationHelper::addTranslation('Yes/No', [], 'messages');
        TranslationHelper::addTranslation('Let me ponder your request', [], 'messages');
        TranslationHelper::addTranslation('Submit', [], 'messages');
    }

    /**
     * @var ArrayCollection
     */
    private $containers;

    /**
     * @return ArrayCollection
     */
    public function getContainers(): ArrayCollection
    {
        if (null === $this->containers)
            $this->containers = new ArrayCollection();

        return $this->containers;
    }

    /**
     * Containers.
     *
     * @param ArrayCollection $containers
     * @return ContainerManager
     */
    public function setContainers(ArrayCollection $containers): ContainerManager
    {
        $this->containers = $containers;
        return $this;
    }

    /**
     * addContainer
     * @param Container $container
     * @return ContainerManager
     */
    public function addContainer(Container $container): ContainerManager
    {
        if (null === $container->getTranslationDomain())
            $container->setTranslationDomain($this->getTranslationDomain());

        $container = $this->resolveContainer($container);

        $this->getContainers()->set($container->getTarget(), $container->toArray());

        return $this;
    }

    /**
     * resolveContainer
     * @param Container $container
     * @return Container
     */
    private function resolveContainer(Container $container): Container
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired(
            [
                'target',
            ]
        );
        $resolver->setDefaults(
            [
                'content' => null,
                'panels' => null,
                'forms' => null,
                'selectedPanel' => null,
                'application' => null,
                'contentLoader' => null,
            ]
        );

        $resolver->setAllowedTypes('target', 'string');
        $resolver->setAllowedTypes('content', ['string', 'null']);
        $resolver->setAllowedTypes('selectedPanel', ['string', 'null']);
        $resolver->setAllowedTypes('panels', [ArrayCollection::class, 'null']);
        $resolver->setAllowedTypes('forms', [ArrayCollection::class, 'null']);
        $resolver->setAllowedTypes('application', ['string', 'null']);
        $resolver->setAllowedTypes('contentLoader', ['array', 'null']);

        $resolver->resolve($container->toArray());

        if ('' === $container->getTarget())
            throw new \InvalidArgumentException(sprintf('The container target is empty!'));

        return $container;
    }

    /**
     * @var array|null
     */
    private ?array $builtContainers;

    /**
     * getBuiltContainers
     *
     * 9/10/2020 09:40
     * @param bool $refresh
     * @return array|null
     */
    public function getBuiltContainers(bool $refresh = false): ?array
    {
        if (!isset($this->builtContainers) || null === $this->builtContainers || [] === $this->builtContainers || $refresh)
            $this->buildContainers();
        return $this->builtContainers;
    }

    /**
     * BuildContainers.
     *
     * @param array $builtContainers
     * @return ContainerManager
     */
    public function setBuiltContainers(array $builtContainers): ContainerManager
    {
        $this->builtContainers = $builtContainers;
        return $this;
    }

    /**
     * buildContainers
     * @return ContainerManager
     */
    public function buildContainers(): ContainerManager
    {
        $containers = [];
        foreach($this->getContainers() as $target=>$container) {
            foreach($container['panels'] as $q=>$panel) {
                $panel['label'] = TranslationHelper::translate($panel['name'], [], $this->getTranslationDomain($panel));
                $container['panels'][$q] = $panel;
            }
            $container['panels'] = $container['panels']->toArray();
            $container['forms'] = $container['forms']->toArray();
            $container['translations'] = TranslationHelper::getTranslations();
            $container['showSubmitButton'] = $this->isShowSubmitButton();
            $container['actionRoute'] = $this->stack->getCurrentRequest()->attributes->get('_route');
            $container['extras'] = ReactFormHelper::getExtras();
            $container['returnRoute'] = AbstractPagination::resolveRoute($this->getReturnRoute());
            $container['addElementRoute'] = AbstractPagination::resolveRoute($this->getAddElementRoute());
            $container['hideSingleFormWarning'] = $this->isHideSingleFormWarning();
            $containers[$target] = $container;
        }

        $this->setBuiltContainers($containers);

        return $this;
    }

    /**
     * getTranslationDomain
     *
     * 9/10/2020 09:36
     * @param array $trans
     * @return string|null
     */
    public function getTranslationDomain(array $trans = []): ?string
    {
        return !isset($trans['translationDomain']) || is_null($trans['translationDomain']) ? $this->translationDomain : $trans['translationDomain'];
    }

    /**
     * setTranslationDomain
     *
     * 9/10/2020 09:36
     * @param string|null $translationDomain
     * @return $this
     */
    public function setTranslationDomain(?string $translationDomain): ContainerManager
    {
        $this->translationDomain = $translationDomain;
        return $this;
    }

    /**
     * DefaultPanel.
     *
     * @param string|null $defaultPanel
     * @return ContainerManager
     */
    public function setDefaultPanel(?string $defaultPanel): ContainerManager
    {
        $this->defaultPanel = $defaultPanel;
        return $this;
    }

    /**
     * singlePanel
     *
     * 25/08/2020 12:57
     * @param $view
     * @param string|null $application
     * @param string $target
     * @param string $domain
     * @return $this
     */
    public function singlePanel($view, ?string $application = null, string $target = 'formContent', string $domain = 'messages'): ContainerManager
    {
        if ($view instanceof FormInterface) $view = $view->createView();
        if (!$view instanceof FormView) throw new \TypeError('You must provide a ' . FormView::class);
        $container = new Container();
        $container->addForm('single', $view);
        $panel = new Panel('single');
        $section = new Section('form', 'single');
        $container->addPanel($panel->addSection($section))->setTarget($target)->setApplication($application);
        $this->setTranslationDomain($domain)->addContainer($container);
        return $this;
    }

    /**
     * setContent
     * @param string $content
     * @param string $domain
     */
    public function setContent(string $content, string $domain = 'messages')
    {
        $container = new Container();
        $container->setContent($content);
        $this->setTranslationDomain($domain)->addContainer($container);
    }

    /**
     * getFormFromContainer
     * @param string $containerName
     * @param string $panelName
     * @return array
     */
    public function getFormFromContainer(string $containerName = 'formContent', string $panelName = 'single'): array
    {
        $container = $this->getContainers()->get($containerName);
        return $container['forms']->get($panelName);
    }

    /**
     * @return bool
     */
    public function isShowSubmitButton(): bool
    {
        return $this->showSubmitButton;
    }

    /**
     * ShowSubmitButton.
     *
     * @param bool $showSubmitButton
     * @return ContainerManager
     */
    public function setShowSubmitButton(bool $showSubmitButton): ContainerManager
    {
        $this->showSubmitButton = $showSubmitButton;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAddElementRoute(): ?array
    {
        return $this->addElementRoute;
    }

    /**
     * setAddElementRoute
     * @param $addElementRoute
     * @param string $prompt
     * @return $this
     * 6/06/2020 10:58
     */
    public function setAddElementRoute($addElementRoute, string $prompt = 'Add'): ContainerManager
    {
        if (is_string($addElementRoute)) {
            $addElementRoute = ['url' => $addElementRoute];
        }

        $addElementRoute['prompt'] = $prompt;
        $this->addElementRoute = $addElementRoute;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getReturnRoute(): ?array
    {
        return $this->returnRoute;
    }

    /**
     * setReturnRoute
     * @param $returnRoute
     * @param string $prompt
     * @return $this
     * 6/06/2020 10:58
     */
    public function setReturnRoute($returnRoute, string $prompt = 'Return'): ContainerManager
    {
        if (is_string($returnRoute)) {
            $returnRoute = ['url' => $returnRoute];
        }

        $returnRoute['prompt'] = $prompt;
        $this->returnRoute = $returnRoute;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHideSingleFormWarning(): bool
    {
        return $this->hideSingleFormWarning;
    }

    /**
     * HideSingleFormWarning.
     *
     * @param bool $hideSingleFormWarning
     * @return ContainerManager
     */
    public function setHideSingleFormWarning(bool $hideSingleFormWarning = true): ContainerManager
    {
        $this->hideSingleFormWarning = $hideSingleFormWarning;
        return $this;
    }
}

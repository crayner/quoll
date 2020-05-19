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
 * Time: 11:19
 */

namespace App\Container;

use App\Manager\AbstractPaginationManager;
use App\Util\ReactFormHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ContainerManager
 * @package App\Container
 */
class ContainerManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

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
     * @param TranslatorInterface $translator
     * @param RequestStack $stack
     */
    public function __construct(TranslatorInterface $translator, RequestStack $stack)
    {
        $this->translator = $translator;
        $this->stack = $stack;

        TranslationHelper::addTranslation('Errors on Tab', [], 'messages');
        TranslationHelper::addTranslation('All fields on all panels are saved together.', [], 'messages');
        TranslationHelper::addTranslation('Return', [], 'messages');
        TranslationHelper::addTranslation('Add', [], 'messages');
        TranslationHelper::addTranslation('Erase Content', [], 'messages');
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
    private $builtContainers;

    /**
     * @param bool $refresh
     * @return array|null
     */
    public function getBuiltContainers(bool $refresh = false): ?array
    {
        if (null === $this->builtContainers || [] === $this->builtContainers || $refresh)
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
                $panel['label'] = $this->translator->trans($panel['name'], [], $this->getTranslationDomain($panel));
                $container['panels'][$q] = $panel;
            }
            $container['panels'] = $container['panels']->toArray();
            $container['forms'] = $container['forms']->toArray();
            $container['translations'] = TranslationHelper::getTranslations();
            $container['showSubmitButton'] = $this->isShowSubmitButton();
            $container['actionRoute'] = $this->stack->getCurrentRequest()->attributes->get('_route');
            $container['extras'] = ReactFormHelper::getExtras();
            $container['returnRoute'] = AbstractPaginationManager::resolveRoute($this->getReturnRoute());
            $container['addElementRoute'] = AbstractPaginationManager::resolveRoute($this->getAddElementRoute());
            $container['hideSingleFormWarning'] = $this->isHideSingleFormWarning();
            $containers[$target] = $container;
        }

        $this->setBuiltContainers($containers);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTranslationDomain(array $trans = []): ?string
    {
        return !isset($trans['translationDomain']) || is_null($trans['translationDomain']) ? $this->translationDomain : $trans['translationDomain'];
    }

    /**
     * TranslationDomain.
     *
     * @param string $translationDomain
     * @return ContainerManager
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
     * @param FormView $view
     * @param string|null $application
     * @param string $target
     * @param string $domain
     */
    public function singlePanel(FormView $view, ?string $application = null, string $target = 'formContent', string $domain = 'messages')
    {
        $container = new Container();
        $container->addForm('single', $view);
        $panel = new Panel('single');
        $container->addPanel($panel)->setTarget($target)->setApplication($application);
        $this->setTranslationDomain($domain)->addContainer($container);
    }

    /**
     * setContent
     * @param string $content
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
     * AddElementRoute.
     *
     * @param string $addElementRoute
     * @return ContainerManager
     */
    public function setAddElementRoute($addElementRoute): ContainerManager
    {
        if (is_string($addElementRoute))
            $addElementRoute = ['url' => $addElementRoute];
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
     * ReturnRoute.
     *
     * @param string|array $returnRoute
     * @return ContainerManager
     */
    public function setReturnRoute($returnRoute): ContainerManager
    {
        if (is_string($returnRoute))
            $returnRoute = ['url' => $returnRoute];
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
    public function setHideSingleFormWarning(bool $hideSingleFormWarning): ContainerManager
    {
        $this->hideSingleFormWarning = $hideSingleFormWarning;
        return $this;
    }
}
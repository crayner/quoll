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
 * Date: 27/07/2019
 * Time: 08:43
 */

namespace App\Manager\Entity;

use App\Modules\System\Entity\Action;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Serializer;

/**
 * Class BreadCrumbs
 * @package App\Manager\Entity
 */
class BreadCrumbs
{
    /**
     * @var ArrayCollection|BreadCrumbItem[]
     */
    private $items;

    /**
     * @var
     */
    private $title;

    /**
     * @var array
     */
    private $trans_params = [];

    /**
     * @var string
     */
    private $domain;

    /**
     * @var Action
     */
    private $action;

    /**
     * @return BreadCrumbItem[]|ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        if (null === $this->items)
            $this->items = new ArrayCollection();

        return $this->items;
    }

    /**
     * Items.
     *
     * @param BreadCrumbItem[]|ArrayCollection|null $items
     * @return BreadCrumbs
     */
    public function setItems(?ArrayCollection $items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * addItem
     * @param BreadCrumbItem $item
     * @return BreadCrumbs
     */
    public function addItem(BreadCrumbItem $item): self
    {
        if ($this->getItems()->containsKey($item->getName()))
            return $this;

        $this->items->set($item->getName(),$item);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Title.
     *
     * @param mixed $title
     * @return BreadCrumbs
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * add
     * @param array $module
     * @return BreadCrumbItem[]|ArrayCollection
     */
    public function create(array $module, Action $action)
    {
        $this->setAction($action);
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'crumbs',
            'title',
            'module',
        ]);
        $resolver->setDefaults([
            'trans_params' => [],
            'domain' => $this->getAction()->getModule()->getName(),
        ]);

        $module = $resolver->resolve($module);

        $this->setItems(new ArrayCollection());
        $item = new BreadCrumbItem();
        $item->setName('Home')->setUri('home')->setDomain('messages');
        $this->addItem($item);
        $this->setTitle($module['title']);
        $this->setTransParams($module['trans_params']);
        $this->setDomain($module['domain']);

        $item = new BreadCrumbItem();
        $item->setName($this->getAction()->getModule()->getName())->setUri($this->getAction()->getModule()->getentryRoute())->setDomain($this->getDomain());
        $this->addItem($item);

        foreach($module['crumbs'] as $crumb) {
            $crumb['domain'] = $this->getDomain();
            $item = new BreadCrumbItem($crumb);
            $this->addItem($item);
        }

        $item = new BreadCrumbItem();
        $item->setName($this->getTitle())->setUri(null)->setTransParams($this->getTransParams())->setDomain($this->getDomain());
        $this->addItem($item);

        return $this->getItems();
    }

    private $crumbs = [];

    /**
     * Add a named route to the trail.
     *
     * @param string $title Name to display on this route's link
     * @param string $route Actually URL ?
     * @param array $uriParams
     * @param array $transParams
     * @return self
     */
    public function add(string $title, string $route = '', array $uriParams = [], array $transParams = []): self
    {
        if (count($this->getCrumbs()) === 0 && $title !== 'Home')
            $this->add('Home', 'home', []);

        if ($title === 'Home')
        {
            $this->crumbs = ['crumbs' => ['Home' => UrlGeneratorHelper::getPath('home')], 'title' => $title];
            return $this;
        }

        $this->addCrumb($title, $route, $uriParams, $transParams);

        return $this;
    }

    /**
     * addCrumb
     * @param string $title
     * @param string $route
     * @param array $uriParams
     * @param array $transParams
     * @return BreadCrumbs
     */
    private function addCrumb(string $title, string $route = '', array $uriParams = [], array $transParams = []): BreadCrumbs
    {
        if ('' !== $route) {
            $this->crumbs['crumbs'][$title] = UrlGeneratorHelper::getPath($route, $uriParams);
        }

        $this->crumbs['title'] = $title;
        $this->crumbs['trans_params'] = $transParams;
        $this->crumbs['domain'] = $this->getDomain();

        return $this;
    }

    /**
     * getCrumbs
     * @return ArrayCollection
     */
    public function getCrumbs(): ArrayCollection
    {
        $result = new ArrayCollection(isset($this->crumbs['crumbs']) ? $this->crumbs['crumbs'] : []);
        if (isset($this->crumbs['title']))
            $result->set($this->crumbs['title'], '', [], $this->crumbs['trans_params']);
        return $result;
    }

    /**
     * @var string|null
     */
    private $module;

    /**
     * @return string|null
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param string|null $module
     * @return BreadCrumbs
     */
    public function setModule(?string $module): BreadCrumbs
    {
        if (0 === strpos($module, 'index.php?q='))
        {
            $module = explode('/', trim($module, '/'));
            $module = array_pop($module);
        }

        $this->module = strtolower(trim($module, '/'));

        return $this;
    }

    /**
     * @return array
     */
    public function getTransParams(): array
    {
        return $this->trans_params;
    }

    /**
     * TransParams.
     *
     * @param array $trans_params
     * @return BreadCrumbs
     */
    public function setTransParams(array $trans_params): BreadCrumbs
    {
        $this->trans_params = $trans_params;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain ?: 'messages';
    }

    /**
     * Domain.
     *
     * @param string $domain
     * @return BreadCrumbs
     */
    public function setDomain(?string $domain): BreadCrumbs
    {
        $this->domain = $domain ?: 'messages';
        return $this;
    }

    /**
     * isValid
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->getItems()->count() === 0)
            return false;
        if (empty($this->getTitle()))
            return false;
        return true;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach($this->getItems() as $item) {
            $crumb = [];
            $crumb['name'] = TranslationHelper::translate($item->getName(), $item->getTransParams(), $item->getDomain());

            $crumb['url'] = $item->getUri() ? UrlGeneratorHelper::getUrl($item->getUri(), $item->getUriParams(), true) : '';
            $result[$item->getName()] = $crumb;
        }
        return $result;
    }

    /**
     * @return Action
     */
    public function getAction(): Action
    {
        return $this->action;
    }

    /**
     * Action.
     *
     * @param Action $action
     * @return BreadCrumbs
     */
    public function setAction(Action $action): BreadCrumbs
    {
        $this->action = $action;
        return $this;
    }
}
<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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
     * @var null:string
     */
    private $baseURL;

    /**
     * @var
     */
    private $title;

    /**
     * @var array
     */
    private $trans_params = [];

    /**
     * @var bool
     */
    private $legacy = false;

    /**
     * @var string
     */
    private $domain;

    /**
     * @return BreadCrumbItem[]|ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        if ($this->isLegacy())
            return $this->getCrumbs();

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
     * @return null
     */
    public function getBaseURL()
    {
        return trim($this->baseURL, '/');
    }

    /**
     * BaseURL.
     *
     * @param null $baseURL
     * @return BreadCrumbs
     */
    public function setBaseURL($baseURL): BreadCrumbs
    {
        $this->baseURL = $baseURL;
        return $this->setModule($baseURL);
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
    public function create(array $module)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'baseURL',
            'crumbs',
            'title',
            'module',
        ]);
        $resolver->setDefaults([
            'trans_params' => [],
            'domain' => 'messages',
        ]);

        $module = $resolver->resolve($module);


        $this->setItems(new ArrayCollection());
        $item = new BreadCrumbItem();
        $item->setName('Home')->setUri('home')->setDomain('messages');
        $this->addItem($item);
        $this->setTitle($module['title']);
        $this->setTransParams($module['trans_params']);
        $this->setBaseURL($module['baseURL']);
        $this->setDomain($module['domain']);

        $item = new BreadCrumbItem();
        $item->setName($module['module'])->setUri($this->getBaseURL() . '__default')->setDomain($this->getDomain());
        $this->addItem($item);

        foreach($module['crumbs'] as $crumb) {
            if (false === strpos($crumb['uri'], '__'))
                $crumb['uri'] =  $this->getModule() . '__' . $crumb['uri'];
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
     * @param string $title   Name to display on this route's link
     * @param string $route   URL relative to the trail's BaseURL
     * @param array  $params  Additional URL params to append to the route
     * @return self
     */
    public function add(string $title, string $route = '', array $uriParams = [], array $transParams = [])
    {
        if (count($this->getCrumbs()) === 0 && $title !== 'Home')
            $this->add('Home', 'home', []);

        if ($title === 'Home')
        {
            $this->crumbs = ['baseURL' => $this->getBaseURL(), 'crumbs' => ['Home' => UrlGeneratorHelper::getPath('home')], 'title' => $title];
            return $this;
        }

        $this->addCrumb($title, $route, $uriParams, $transParams);

        return $this->setLegacy(true);
    }

    /**
     * addCrumb
     * @param string $title
     * @param string $route
     * @param array $params
     * @return BreadCrumbs
     */
    private function addCrumb(string $title, string $route = '', array $uriParams = [], array $transParams = []): BreadCrumbs
    {
        if ('' !== $route) {
            if (strpos($route, '.php') !== false) {
                $this->crumbs['crumbs'][$title] = UrlGeneratorHelper::getPath('legacy', array_merge(['q' => str_replace('index.php?q=','', $this->getBaseURL()) . '/' . $route], $uriParams));
            } else {
                if (false === strpos($route, '__'))
                    $route = strtolower(str_replace(' ', '_', $this->getModule())) . '__' . $route;
                $this->crumbs['crumbs'][$title] = UrlGeneratorHelper::getPath($route, $uriParams);
            }
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
     * @return bool
     */
    public function isLegacy(): bool
    {
        return $this->legacy;
    }

    /**
     * Legacy.
     *
     * @param bool $legacy
     * @return BreadCrumbs
     */
    public function setLegacy(bool $legacy): BreadCrumbs
    {
        $this->legacy = $legacy;
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
    public function setDomain(string $domain): BreadCrumbs
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * isValid
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->isLegacy())
            return false;
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
}
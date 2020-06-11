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
 * Date: 29/07/2019
 * Time: 12:05
 */

namespace App\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Traits ContentTrait
 * @package App\Twig
 */
trait ContentTrait
{
    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var ArrayCollection
     */
    private $attributes;

    /**
     * MainMenu constructor.
     * @param RequestStack $stack
     */
    public function __construct(RequestStack $stack)
    {
        $this->stack = $stack;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        if (null === $this->request)
            $this->request = $this->stack->getCurrentRequest();

        return $this->request;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        if (null === $this->session)
            $this->session = $this->getRequest()->getSession();

        return $this->session;
    }

    /**
     * @return bool
     */
    public function hasSession(): bool
    {
        if (null === $this->session) {
            if ($this->getRequest() instanceof Request)
                return $this->getRequest()->hasSession();
        }

        return true;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes(): ArrayCollection
    {
        if (null === $this->attributes)
            $this->attributes = new ArrayCollection();
        return $this->attributes;
    }

    /**
     * Attributes.
     *
     * @param ArrayCollection $attributes
     * @return ContentTrait
     */
    public function setAttributes(ArrayCollection $attributes): ContentTrait
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * addAttribute
     * @param string $name
     * @param $content
     * @return $this
     */
    public function addAttribute(string $name, $content): ContentInterface
    {
        $this->getAttributes()->set($name, $content);

        return $this;
    }

    /**
     * removeAttribute
     * @param string $name
     * @return $this|ContentInterface
     */
    public function removeAttribute(string $name): ContentInterface
    {
        $this->getAttributes()->remove($name);
        return $this;
    }

    /**
     * hasAttribute
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return $this->getAttributes()->containsKey($name);
    }

    /**
     * getAttribute
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        return $this->hasAttribute($name) ? $this->attributes->get($name) : null;
    }

    /**
     * @var bool
     */
    private $valid = true;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->getValid() && $this->getAttributes()->count() > 0;
    }

    /**
     * getValid
     * @return bool
     */
    public function getValid(): bool
    {
        return $this->valid;
    }

    /**
     * Valid.
     *
     * @param bool $valid
     * @return ContentTrait
     */
    public function setValid(bool $valid): ContentTrait
    {
        $this->valid = $valid;
        return $this;
    }
}
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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BreadCrumbItem
 * @package App\Manager\Entity
 */
class BreadCrumbItem
{
    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $uri;

    /**
     * @var array
     */
    private $uri_params = [];

    /**
     * @var array
     */
    private $trans_params = [];

    /**
     * @var string
     */
    private $domain;

    /**
     * BreadCrumbItem constructor.
     * @param array $crumb
     */
    public function __construct(array $crumb = [])
    {
        if ([] !== $crumb) {
            $resolver = new OptionsResolver();
            $resolver->setRequired([
                'name',
                'uri',
            ]);
            $resolver->setDefaults([
                'uri_params' => [],
                'trans_params' => [],
                'domain' => 'messages',
            ]);

            if (isset($crumb['params'])) {
                $crumb['uri_params'] = $crumb['params'];
                unset($crumb['params']);
                trigger_error('The params option has been replaced by the uri_params option.', E_USER_DEPRECATED);
            }
            $crumb = $resolver->resolve($crumb);

            $this->setDomain($crumb['domain'])->setName($crumb['name'])->setUri($crumb['uri'])->setTransParams($crumb['trans_params'])->setUriParams($crumb['uri_params']);
        }
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Name.
     *
     * @param string|null $name
     * @return BreadCrumbItem
     */
    public function setName(?string $name): BreadCrumbItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * Uri.
     *
     * @param string|null $uri
     * @return BreadCrumbItem
     */
    public function setUri(?string $uri): BreadCrumbItem
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return array
     */
    public function getUriParams(): array
    {
        return $this->uri_params;
    }

    /**
     * UriParams.
     *
     * @param array $uri_params
     * @return BreadCrumbItem
     */
    public function setUriParams(array $uri_params): BreadCrumbItem
    {
        $this->uri_params = $uri_params;
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
     * @return BreadCrumbItem
     */
    public function setTransParams(array $trans_params): BreadCrumbItem
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
     * @return BreadCrumbItem
     */
    public function setDomain(string $domain): BreadCrumbItem
    {
        $this->domain = $domain;
        return $this;
    }
}
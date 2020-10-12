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
 * Date: 27/07/2019
 * Time: 08:43
 */

namespace App\Manager\Hidden;

use App\Util\TranslationHelper;
use InvalidArgumentException;
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
    private ?string $name;

    /**
     * @var null|string
     */
    private ?string $uri;

    /**
     * @var array
     */
    private array $uri_params = [];

    /**
     * @var array
     */
    private array $trans_params = [];

    /**
     * @var string|null
     */
    private ?string $domain;

    /**
     * @var bool
     */
    private bool $translated = false;

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

            $crumb = $resolver->resolve($crumb);

            $this->setDomain($crumb['domain'])
                ->setName($crumb['name'])
                ->setUri($crumb['uri'])
                ->setTranslated(false)
                ->setTransParams($crumb['trans_params'])
                ->setUriParams($crumb['uri_params']);
        }
    }

    /**
     * getName
     *
     * 12/10/2020 13:17
     * @return string|null
     */
    public function getName(): ?string
    {
        if ($this->isTranslated()) return $this->name;

        $this->setTranslated(true);
        return $this->name = TranslationHelper::translate($this->name, $this->getTransParams(), $this->getDomain());
    }

    /**
     * Name.
     *
     * @param string|array|null $name
     * @return BreadCrumbItem
     */
    public function setName($name): BreadCrumbItem
    {
        if (is_array($name) && count($name) === 3) {
            $this->setDomain($name[2]);
            $this->setTransParams($name[1]);
            $name = $name[0];
        }
        if (is_array($name)) throw new InvalidArgumentException('The name must be a string or and array with 3 parts.');
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

    /**
     * @return bool
     */
    public function isTranslated(): bool
    {
        return $this->translated;
    }

    /**
     * @param bool $translated
     * @return BreadCrumbItem
     */
    public function setTranslated(bool $translated): BreadCrumbItem
    {
        $this->translated = $translated;
        return $this;
    }
}

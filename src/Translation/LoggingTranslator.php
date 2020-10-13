<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/06/2020
 * Time: 11:24
 */
namespace App\Translation;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Exception\InvalidArgumentException;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LoggerTranslator
 * @package App\Translation
 * This class is used to decorate the Logger Translator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LoggingTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    use FallbackDomainTrait;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var RequestStack
     */
    private RequestStack $stack;

    /**
     * Translator constructor.
     * @param TranslatorInterface $translator
     * @param RequestStack $stack
     */
    public function __construct(TranslatorInterface $translator, RequestStack $stack)
    {
        $this->translator = $translator;
        $this->stack  = $stack;
    }

    /**
     * trans
     * @param string|null $id
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        if (null === $id || '' === $id)
            return '';

        if (trim($id) === '')
            return $id;

        $id = trim($id);

        if (intval($id) > 0 || is_int($id) || $id === '0') {
            return $id;
        }

        $domain = $this->getFallBackDomain($id, $domain, $locale);

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Sets the current locale.
     *
     * @param string $locale The locale
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    public function setLocale($locale)
    {
        return $this->translator->setLocale($locale);
    }

    /**
     * Returns the current locale.
     *
     * @return string The locale
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    /**
     * Gets the catalogue by locale.
     *
     * @param string|null $locale The locale or null to use the default
     *
     * @return MessageCatalogueInterface
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    public function getCatalogue(string $locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }
}
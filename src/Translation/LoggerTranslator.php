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
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LoggerTranslator
 * @package App\Translation
 * This class is used to decorate the Logger Translator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LoggerTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var RequestStack
     */
    private RequestStack $stack;

    /**
     * @var array
     */
    private array $messages;

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
     * @param string $domain
     * @param string $locale
     * @return string
     * @throws \Exception
     */
    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        if (!isset($this->messages)) $this->messages = Yaml::parse(file_get_contents(__DIR__ . '/../../translations/messages.en_GB.yaml'));

        if (key_exists($id, $this->messages)) $domain = 'messages';

        if (null === $domain)
            $domain = 'messages';

        if (null === $id || '' === $id)
            return '';

        if (trim($id) === '')
            return $id;

        $id = trim($id);

        if (intval($id) > 0 || is_int($id) || $id === '0') {
            return $id;
        }

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
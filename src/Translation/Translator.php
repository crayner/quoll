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
 * Date: 9/08/2019
 * Time: 12:57
 */

namespace App\Translation;

use App\Modules\System\Entity\StringReplacement;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Translator
 *
 * This class is used to decorate the Translator Service
 *
 * @package App\Translation
 */
class Translator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    /**
     * trans
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param null $locale
     * @return string
     * @throws \Exception
     */
    public function trans($id, array $parameters = [], string $domain = null, string $locale = null)
    {
        if (null === $domain)
            $domain = 'messages';

        $id = trim($id);
        if (null === $id || '' === $id)
            return '';

        if ($domain === 'gibbon')
        {
            // trigger_error('The use of the Gibbon language domain is deprecated. Use null or messages.', E_USER_DEPRECATED);
            $domain = 'messages';
        }

        // Change translation domain to 'messages' if a translation can't be found in the
        // current domain
        if ('messages' !== $domain && false === $this->getCatalogue($locale)->has((string) $id, $domain)) {
            $domain = 'messages';
        }

        $id = $this->translator->trans($id, $parameters, $domain, $locale);

        return $this->getInstituteTranslation($id, $parameters);
    }

    /**
     * getInstituteTranslation
     * @param $trans
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    private function getInstituteTranslation($trans, array $parameters): string
    {
        if (empty($trans))
            return $trans;

        $strings = $this->getStrings();
        $trans = str_replace(array_keys($parameters), array_values($parameters), $trans);

        if ((! empty($strings) || $strings->count() > 0) && $strings instanceof ArrayCollection) {
            foreach ($strings->toArray() as $replacement) {
                if ($replacement->getMode() === "Partial") { //Partial match
                    if ($replacement->isCaseSensitive()) {
                        if (false !== strpos($trans, $replacement->getOriginal())) {
                            $trans = str_replace($replacement->getOriginal(), $replacement->getReplacement(), $trans);
                        }
                    }
                    else {
                        if (false !== stripos($trans, $replacement->getOriginal())) {
                            $trans = str_ireplace($replacement->getOriginal(), $replacement->getReplacement(), $trans);
                        }
                    }
                }
                else { //Whole match
                    if ($replacement->isCaseSensitive()) {
                        if ($replacement->getOriginal() === $trans) {
                            $trans = $replacement->getReplacement();
                        }
                    }
                    else {
                        if (strtolower($replacement->getOriginal()) === strtolower($trans)) {
                            $trans = $replacement->getReplacement();
                        }
                    }
                }
            }
        }

        return $trans;
    }

    /**
     * @var null|Collection
     */
    private $strings;

    /**
     * getStrings
     *
     * @param bool $refresh
     * @return Collection|null
     * @throws \Exception
     */
    public function getStrings($refresh = false): ?Collection
    {
        if (null === $this->stack->getCurrentRequest())
            return new ArrayCollection();
        if (strpos($this->stack->getCurrentRequest()->get('_route'), 'install__') === 0)
            return new ArrayCollection();
        $provider = ProviderFactory::create(StringReplacement::class);
        if (null === $this->strings && ! $refresh)
            $this->strings = $this->stack->getCurrentRequest()->getSession()->get('stringReplacement', null);
        else
            return $this->strings;

        if ((empty($this->strings) || $refresh) && $provider->isValidEntityManager())
            try {
                $this->strings = new ArrayCollection($provider->getRepository()->findBy([], ['priority' => 'DESC', 'original' => 'ASC']));
            } catch (TableNotFoundException $e) {
                $this->strings = new ArrayCollection();
            }
        else
            return $this->strings = $this->strings instanceof ArrayCollection ? $this->strings : new ArrayCollection();

        $sr = new StringReplacement();
        $sr->setOriginal('Gibbon')->setReplacement('Kookaburra')->setMode('Whole')->setCaseSensitive('N')->setPriority(1);
        $this->strings->add($sr);

        $this->stack->getCurrentRequest()->getSession()->set('stringReplacement', $this->strings);

        return $this->strings;
    }

    /**
     * setStrings
     * @param Collection|null $strings
     * @return Translator
     */
    public function setStrings(?Collection $strings): Translator
    {
        if (empty($strings))
            $strings = new ArrayCollection();

        $this->strings = $strings;

        return $this;
    }

    /**
     * Translates the given choice message by choosing a translations according to a number.
     *
     * @param string $id The message id (may also be an object that can be cast to string)
     * @param int $number The number to use to find the indice of the message
     * @param array $parameters An array of parameters for the message
     * @param string|null $domain The domain for the message or null to use the default
     * @param string|null $locale The locale or null to use the default
     *
     * @return string The translated string
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     * @throws \Exception
     * @deprecated Since Symfony 4.2  Use trans with a %count%
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        trigger_error(sprintf('%s is deprecated since Symfony 4.2  Use trans with a %count%', __METHOD__), E_USER_DEPRECATED);
        if (is_array($number))
            $trans = $this->multipleTransChoice($id, $number, $parameters, $domain, $locale);
        else
            $trans = $this->translator->transChoice($id, $number, $parameters, $domain, $locale);

        return $this->getInstituteTranslation($trans, $locale);
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

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * Translator constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator, RequestStack $stack)
    {
        $this->translator = $translator;
        $this->stack  = $stack;
    }
}
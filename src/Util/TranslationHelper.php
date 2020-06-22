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
 * Date: 29/08/2019
 * Time: 12:41
 */

namespace App\Util;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TranslationHelper
 * @package App\Util
 */
class TranslationHelper
{
    /**
     * @var TranslatorInterface
     */
    private static $translator;

    /**
     * @var array
     */
    private static $translations;

    /**
     * @var string
     */
    private static $domain = 'messages';

    /**
     * TranslationHelper constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        self::$translator = $translator;
    }

    /**
     * getTranslations
     * @return array
     */
    public static function getTranslations(): array
    {
        return self::$translations = self::$translations ?: [];
    }

    /**
     * setTranslations
     * @param array $translations
     */
    public static function setTranslations(array $translations)
    {
        self::$translations = $translations;
    }

    /**
     * addTranslation
     * @param string|null $id
     * @param array $options
     * @param string|null $domain
     */
    public static function addTranslation(?string $id, array $options = [], ?string $domain = null)
    {
        if (!in_array($id, [null,''])) {
            self::getTranslations();
            self::$translations[$id] = self::translate($id, $options, $domain ?: self::getDomain());
        }
    }

    /**
     * addTranslation
     * @param string|null $id
     * @param string $value
     * @param array $options
     * @param string|null $domain
     */
    public static function setTranslation(string $id, string $value, array $options = [], ?string $domain = null)
    {
        if (!in_array($value , [null, ''])) {
            self::getTranslations();
            self::$translations[$id] = self::translate($value, $options, $domain ?: self::getDomain());
        }
    }

    /**
     * translate
     * @param string|null $id
     * @param array $params
     * @param string|null $domain
     * @return string|null
     */
    public static function translate(?string $id, array $params = [], ?string $domain = null): ?string
    {
        if (null === self::$translator || null === $id)
            return $id;
        return self::$translator->trans($id, $params, str_replace(' ', '', $domain ?: self::getDomain()));
    }

    /**
     * @return string
     */
    public static function getDomain(): string
    {
        return self::$domain ?: 'messages';
    }

    /**
     * @param string $domain
     */
    public static function setDomain(string $domain): void
    {
        self::$domain = $domain;
    }

    /**
     * getTranslator
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return self::$translator;
    }
}
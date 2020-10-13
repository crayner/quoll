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
 * Date: 12/10/2020
 * Time: 14:17
 */
namespace App\Translation;

use Symfony\Component\Yaml\Yaml;

/**
 * Trait FallbackDomainTrait
 * @package App\Translation
 * @author Craig Rayner <craig@craigrayner.com>
 */
trait FallbackDomainTrait
{
    /**
     * @var array
     */
    private array $messages;

    /**
     * getFallBackDomain
     *
     * 12/10/2020 14:19
     * @param string $id
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function getFallBackDomain(string $id, ?string $domain, ?string $locale): string
    {
        $locale = $locale ?: 'en_GB';
        $this->messages = isset($this->messages) ? $this->messages : Yaml::parse(file_get_contents(__DIR__ . '/../../translations/messages.'.$locale.'.yaml'));

        $domain = is_string($domain) ? $domain : 'messages';

        if (key_exists($id, $this->messages)) $domain = 'messages';

        return $domain;
    }
}

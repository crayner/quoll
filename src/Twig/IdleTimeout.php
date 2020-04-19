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
 * Time: 13:43
 */

namespace App\Twig;

use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class IdleTimeout
 * @package App\Twig
 */
class IdleTimeout implements ContentInterface
{
    use ContentTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * execute
     */
    public function execute(): void
    {
        $this->addAttribute('route', UrlGeneratorHelper::getUrl('logout', ['timeout' => 'true'], true));
        $this->addAttribute('duration',ProviderFactory::create(Setting::class)->getSettingByScopeAsInteger('System', 'sessionDuration', 1200));
        $this->addAttribute('trans_sessionExpire',$this->translate('Your session is about to expire: you will be logged out shortly.'));
        $this->addAttribute('trans_stayConnected', $this->translate('Stay Connected'));
    }

    /**
     * translate
     * @param string $key
     * @param array|null $params
     * @param string|null $domain
     * @return string
     */
    private function translate(string $key, ?array $params = [], ?string $domain = 'messages'): string
    {
        return TranslationHelper::translate($key, $params, $domain);
    }
}
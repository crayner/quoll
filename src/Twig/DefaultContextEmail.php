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
 * Date: 28/05/2020
 * Time: 08:11
 */
namespace App\Twig;

use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

/**
 * Class TemplatedContextEmail
 * @package App\Twig
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DefaultContextEmail
{
    /**
     * @var TemplatedEmail
     */
    private $email;

    /**
     * DefaultContextEmail constructor.
     * @param Headers|null $headers
     * @param AbstractPart|null $body
     */
    public function __construct(Headers $headers = null, AbstractPart $body = null)
    {
        $this->email = new TemplatedEmail($headers, $body);
        $this->context([]);
    }

    /**
     * Context.
     *
     * @param array $context
     * @return DefaultContextEmail
     */
    public function context(array $context): DefaultContextEmail
    {
        $constants = [
            'system_name' => SettingFactory::getSettingManager()->get('System', 'systemName'),
            'organisation_name' => SettingFactory::getSettingManager()->get('System', 'organisationName'),
            'title' => 'Kookaburra',
        ];
        $this->getEmail()->context(array_merge($constants, $context));

        return $this;
    }

    /**
     * __call
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args): DefaultContextEmail
    {
        call_user_func_array([$this->getEmail(), $name], $args);

        return $this;
    }

    /**
     * getEmail
     * @return TemplatedEmail
     */
    public function getEmail()
    {
        return $this->email;
    }
}
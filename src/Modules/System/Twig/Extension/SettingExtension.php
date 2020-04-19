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
 * Date: 7/08/2019
 * Time: 14:39
 */

namespace App\Modules\System\Twig\Extension;

use App\Modules\People\Entity\Person;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class SettingExtension
 * @package App\Twig\Extension
 */
class SettingExtension extends AbstractExtension
{

    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getSettingByScope', [$this, 'getSettingByScope']),
            new TwigFunction('hasSettingByScope', [$this, 'hasSettingByScope']),
            new TwigFunction('getPersonFromSetting', [$this, 'getPersonFromSetting']),
        ];
    }

    /**
     * getSettingByScope
     * @param string $name
     * @param null $default
     */
    public function getSettingByScope(string $scope, string $name, bool $returnRow = false)
    {
        return ProviderFactory::create(Setting::class)->getSettingByScope($scope, $name, $returnRow);
    }

    /**
     * hasSettingByScope
     * @param string $scope
     * @param string $name
     * @return bool
     */
    public function hasSettingByScope(string $scope, string $name): bool
    {
        return ProviderFactory::create(Setting::class)->hasSettingByScope($scope, $name);
    }

    /**
     * getPersonFromSetting
     * @param string $scope
     * @param string $name
     * @param string|null $detail
     */
    public function getPersonFromSetting(string $scope, string $name, ?string $method = null)
    {
        $person = ProviderFactory::getRepository(Person::class)->find(ProviderFactory::create(Setting::class)->getSettingByScopeAsInteger($scope, $name));
        if (!$person instanceof Person || null === $method || !method_exists($person, $method))
            return $person;

        return $person->$method();
    }
}
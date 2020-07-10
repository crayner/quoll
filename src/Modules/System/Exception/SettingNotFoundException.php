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
 * Date: 23/05/2020
 * Time: 16:06
 */

namespace App\Modules\System\Exception;

use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Manager\SettingManager;

/**
 * Class SettingNotFoundException
 * @package App\Modules\System\Exception
 */
class SettingNotFoundException extends \RuntimeException
{
    /**
     * SettingNotFoundException constructor.
     * @param string $scope
     * @param string $name
     * @param string $message
     */
    public function __construct(string $scope, string $name, string $message = "")
    {
        if ('' === $message)
            $message = sprintf('The Setting defined by "%s:%s" was not found.', $scope, $name);
        $sm = SettingFactory::getSettingManager();
        dump($sm->getSettings());

        parent::__construct($message);
    }
}
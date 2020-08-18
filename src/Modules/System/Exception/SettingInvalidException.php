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
 * Date: 16/08/2020
 * Time: 11:31
 */
namespace App\Modules\System\Exception;

use App\Modules\System\Manager\SettingFactory;
use RuntimeException;

/**
 * Class SettingInvalidException
 * @package App\Modules\System\Exception
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingInvalidException extends RuntimeException
{
    /**
     * SettingInvalidException constructor.
     * @param string $scope
     * @param string $name
     * @param string $type
     * @param string $message
     */
    public function __construct(string $scope, string $name, string $type, string $message = "")
    {
        if ('' === $message)
            $message = sprintf('The Setting defined by "%s:%s" is not a valid %s.', $scope, $name, $type);
        $sm = SettingFactory::getSettingManager();
        dump($sm->getSettings());

        parent::__construct($message);
    }
}
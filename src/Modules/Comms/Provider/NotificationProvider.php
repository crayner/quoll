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
 * Date: 6/08/2019
 * Time: 15:06
 */

namespace App\Modules\Comms\Provider;

use App\Provider\AbstractProvider;
use App\Modules\Comms\Entity\Notification;

/**
 * Class NotificationProvider
 * @package App\Modules\Comms\Provider
 */
class NotificationProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Notification::class;
}
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
 * Date: 12/06/2020
 * Time: 13:16
 */
namespace App\Modules\School\Provider;

use App\Modules\School\Entity\AlertLevel;
use App\Provider\AbstractProvider;

/**
 * Class AlertLevelProvider
 * @package App\Modules\School\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AlertLevelProvider extends AbstractProvider
{
    protected $entityName = AlertLevel::class;
}
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
 * Date: 9/06/2020
 * Time: 15:37
 */
namespace App\Modules\IndividualNeed\Provider;

use App\Modules\IndividualNeed\Entity\INArchive;
use App\Provider\AbstractProvider;

/**
 * Class INArchiveProvider
 * @package App\Modules\IndividualNeed\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INArchiveProvider extends AbstractProvider
{
    protected $entityName = INArchive::class;
}
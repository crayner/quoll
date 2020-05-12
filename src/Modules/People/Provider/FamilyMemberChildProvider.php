<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 12/05/2020
 * Time: 08:55
 */
namespace App\Modules\People\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\People\Entity\FamilyMemberChild;
use App\Provider\EntityProviderInterface;

/**
 * Class FamilyMemberChildProvider
 * @package App\Modules\People\Provider
 */
class FamilyMemberChildProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = FamilyMemberChild::class;
}
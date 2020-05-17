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
 * Time: 08:57
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\FamilyMember;
use App\Provider\AbstractProvider;

/**
 * Class FamilyMemberProvider
 * @package App\Modules\People\Provider
 */
class FamilyMemberProvider extends AbstractProvider
{

    protected $entityName = FamilyMember::class;
}
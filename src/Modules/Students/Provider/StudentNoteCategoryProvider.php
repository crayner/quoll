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
 * Date: 3/05/2020
 * Time: 13:54
 */

namespace App\Modules\Students\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\Students\Entity\StudentNoteCategory;
use App\Provider\EntityProviderInterface;

/**
 * Class StudentNoteCategoryProvider
 * @package App\Modules\Students\Provider
 */
class StudentNoteCategoryProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = StudentNoteCategory::class;
}
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
 * Date: 3/05/2020
 * Time: 13:54
 */

namespace App\Modules\Student\Provider;

use App\Modules\Student\Entity\StudentNoteCategory;
use App\Provider\AbstractProvider;

/**
 * Class StudentNoteCategoryProvider
 * @package App\Modules\Student\Provider
 */
class StudentNoteCategoryProvider extends AbstractProvider
{

    protected $entityName = StudentNoteCategory::class;
}
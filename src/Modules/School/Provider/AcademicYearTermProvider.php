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
 * Date: 21/12/2019
 * Time: 20:42
 */

namespace App\Modules\School\Provider;

use App\Provider\AbstractProvider;
use App\Modules\School\Entity\AcademicYearTerm;

/**
 * Class AcademicYearTermProvider
 * @package App\Modules\School\Provider
 */
class AcademicYearTermProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = AcademicYearTerm::class;
}
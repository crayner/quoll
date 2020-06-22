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
 * Date: 21/12/2019
 * Time: 20:42
 */
namespace App\Modules\School\Provider;

use App\Provider\AbstractProvider;
use App\Modules\School\Entity\AcademicYearTerm;

/**
 * Class AcademicYearTermProvider
 * @package App\Modules\School\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearTermProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = AcademicYearTerm::class;

    /**
     * canDelete
     * @param AcademicYearTerm $term
     * @return bool
     * 31/05/2020 13:22
     */
    public function canDelete(AcademicYearTerm $term): bool
    {
        // @todo Activities / MarkBookColumns
        return true;
    }
}
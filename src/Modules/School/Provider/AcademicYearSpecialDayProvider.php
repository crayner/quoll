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
 * Date: 24/12/2019
 * Time: 17:29
 */

namespace App\Modules\School\Provider;

use App\Provider\AbstractProvider;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearSpecialDay;

/**
 * Class AcademicYearSpecialDayProvider
 * @package App\Modules\School\Provider
 */
class AcademicYearSpecialDayProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = AcademicYearSpecialDay::class;

    /**
     * dateExists
     * @param \DateTimeImmutable $date
     * @param AcademicYear $year
     * @return bool
     */
    public function dateExists(\DateTimeImmutable $date, AcademicYear $year): bool
    {
        return $this->getRepository()->findOneBy(['date' => $date, 'academicYear' => $year]) !== null;
    }
}
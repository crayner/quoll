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
 * Date: 29/12/2019
 * Time: 14:29
 */
namespace App\Modules\School\Manager;

use App\Modules\School\Entity\AcademicYearSpecialDay;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;

/**
 * Class SpecialDayManager
 * @package App\Modules\School\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SpecialDayManager
{
    /**
     * canDuplicate
     * @param AcademicYearSpecialDay $specialDay
     * @return bool
     * @throws \Exception
     */
    public static function canDuplicate(AcademicYearSpecialDay $specialDay): bool
    {
        $nextYear = AcademicYearHelper::getNextAcademicYear($specialDay->getAcademicYear());
        if (null === $nextYear) return false;

        return ! ProviderFactory::create(AcademicYearSpecialDay::class)->dateExists(self::getDuplicateDate($specialDay), $nextYear);
    }

    /**
     * getDuplicateDate
     * @param AcademicYearSpecialDay $specialDay
     * @return \DateTimeImmutable
     */
    public static function getDuplicateDate(AcademicYearSpecialDay $specialDay): \DateTimeImmutable
    {
        try {
            $date = new \DateTime($specialDay->getDate()->format('Y-m-d'));
        } catch (\Exception $e) {
        }
        $date->add(new \DateInterval('P1Y'));

        try {
            return new \DateTimeImmutable($date->format('Y-m-d'));
        } catch (\Exception $e) {
        }
    }
}
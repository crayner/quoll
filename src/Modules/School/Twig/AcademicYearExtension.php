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
 * Date: 31/08/2020
 * Time: 13:09
 */
namespace App\Modules\School\Twig;

use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AcademicYearExtension
 * @package App\Modules\School\Twig
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearExtension extends AbstractExtension
{
    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isCurrentAcademicYear', [$this, 'isCurrentAcademicYear']),
            new TwigFunction('isPastAcademicYear', [$this, 'isPastAcademicYear']),
            new TwigFunction('isFutureAcademicYear', [$this, 'isFutureAcademicYear']),
            new TwigFunction('getAcademicYear', [$this, 'getAcademicYear']),
        ];
    }

    /**
     * isCurrentAcademicYear
     *
     * 31/08/2020 13:12
     * @return bool
     */
    public function isCurrentAcademicYear(): bool
    {
        return AcademicYearHelper::getCurrentAcademicYear()->getStatus() === 'Current';
    }

    /**
     * isPastAcademicYear
     *
     * 31/08/2020 13:18
     * @return bool
     */
    public function isPastAcademicYear(): bool
    {
        return AcademicYearHelper::getCurrentAcademicYear()->getStatus() === 'Past';
    }

    /**
     * isFutureAcademicYear
     *
     * 31/08/2020 13:18
     * @return bool
     */
    public function isFutureAcademicYear(): bool
    {
        return AcademicYearHelper::getCurrentAcademicYear()->getStatus() === 'Upcoming';
    }

    /**
     * getCurrentAcademicYear
     *
     * 31/08/2020 13:12
     * @return AcademicYear
     */
    public function getAcademicYear(): AcademicYear
    {
        return AcademicYearHelper::getCurrentAcademicYear();
    }
}
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
 * Date: 3/09/2019
 * Time: 10:01
 */

namespace App\Modules\School\Util;

use App\Modules\School\Entity\AcademicYear;
use App\Provider\ProviderFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AcademicYearHelper
 * @package App\Util
 */
class AcademicYearHelper
{
    /**
     * @var RequestStack
     */
    private static $stack;

    /**
     * AcademicYearHelper constructor.
     * @param RequestStack $stack
     */
    public function __construct(RequestStack $stack)
    {
        self::$stack = $stack;
    }

    /**
     * getCurrentAcademicYear
     * @return AcademicYear|mixed
     * 26/06/2020 08:58
     */
    public static function getCurrentAcademicYear()
    {
        $session = self::$stack->getCurrentRequest()->getSession();
        if ($session->has('academicYear')) {
            return $session->get('academicYear');
        }
        $current = ProviderFactory::getRepository(AcademicYear::class)->findOneByStatus('Current');
        if (null === $current) {
            $current = new AcademicYear();
            $current->setName(date('Y'))
                ->setFirstDay(new \DateTimeImmutable(date('Y-01-01')))
                ->setLastDay(new \DateTimeImmutable(date('Y-12-31')))
                ->setStatus('Current')
            ;
            ProviderFactory::getEntityManager()->persist($current);
            ProviderFactory::getEntityManager()->flush();
        }
        $session->set("academicYear", $current);
        return $current;
    }

    /**
     * getNextAcademicYear
     * @param AcademicYear|null $year
     * @return mixed
     * @throws \Exception
     */
    public static function getNextAcademicYear(?AcademicYear $year = null)
    {
        if (null === $year)
            $year = self::getCurrentAcademicYear();

        return ProviderFactory::getRepository(AcademicYear::class)->findOneByNext($year);
    }
}
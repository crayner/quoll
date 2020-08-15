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
use App\Modules\System\Manager\DemoDataInterface;
use App\Provider\ProviderFactory;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Driver\PDOException;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AcademicYearHelper
 * @package App\Modules\School\Util
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearHelper implements DemoDataInterface
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
     * @param bool $refresh
     * @return AcademicYear|mixed
     * 26/06/2020 08:58
     */
    public static function getCurrentAcademicYear(bool $refresh = false)
    {
        $session = self::$stack->getCurrentRequest()->getSession();
        if ($session->has('academicYear')) {
            if ($refresh) {
                $current =  ProviderFactory::getRepository(AcademicYear::class)->find($session->get('academicYear')->getId());
                $session->set('academicYear', $current);
                return $current;
            }
            return $session->get('academicYear');
        }
        $current = ProviderFactory::getRepository(AcademicYear::class)->findOneByStatus('Current');
        if (null === $current) {
            $current = new AcademicYear();
            $current->setName(date('Y'))
                ->setFirstDay(new DateTimeImmutable(date('Y-01-01')))
                ->setLastDay(new DateTimeImmutable(date('Y-12-31')))
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
     * @throws Exception
     */
    public static function getNextAcademicYear(?AcademicYear $year = null)
    {
        if (null === $year)
            $year = self::getCurrentAcademicYear();

        return ProviderFactory::getRepository(AcademicYear::class)->findOneByNext($year);
    }

    /**
     * createNextYear
     * 13/08/2020 10:08
     */
    public static function createNextYear()
    {
        $year = new AcademicYear();
        $date = new DateTime();
        $date->add(new DateInterval('P1Y'));

        try {
            $year->setName($date->format('Y'))
                ->setStatus('Upcoming')
                ->setFirstDay(new DateTimeImmutable($date->format('Y') . '-01-01'))
                ->setLastDay(new DateTimeImmutable($date->format('Y') . '-01-01'));
            ProviderFactory::create(AcademicYear::class)->persistFlush($year);
        } catch (Exception | PDOException $e) {
        }
    }
}
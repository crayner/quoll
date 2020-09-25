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

use App\Container\Panel;
use App\Container\Section;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\System\Manager\DemoDataInterface;
use App\Provider\ProviderFactory;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Driver\PDOException;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
    private static RequestStack $stack;

    /**
     * @var Environment
     */
    private static Environment $twig;

    /**
     * AcademicYearHelper constructor.
     * @param RequestStack $stack
     * @param Environment $twig
     */
    public function __construct(RequestStack $stack, Environment $twig)
    {
        self::$stack = $stack;
        self::$twig = $twig;
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
        if ($session->has('academicYear') && $session->get('academicYear') !== null) {
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
                ->setLastDay(new DateTimeImmutable($date->format('Y') . '-12-31'));
            ProviderFactory::create(AcademicYear::class)->persistFlush($year);
        } catch (Exception | PDOException $e) {
        }
    }

    /**
     * academicYearWarning
     *
     * 1/09/2020 12:18
     * @param Panel $panel
     * @return Panel
     */
    public static function academicYearWarning(Panel $panel): Panel
    {
        try {
            return $panel->addSection(new Section('html', self::$twig->render('school/academic_year_warning.html.twig')));
        } catch (LoaderError | RuntimeError | SyntaxError $e) {}
        return $panel;
    }

    /**
     * setCurrentYear
     *
     * 24/09/2020 15:04
     * @param string $id
     */
    public function setCurrentYear(string $id)
    {
        $current = self::getCurrentAcademicYear();
        if ($current->getId() === $id) return;

        $new = empty($id) ? ProviderFactory::getRepository(AcademicYear::class)->findOneBy(['status' => 'Current']) : ProviderFactory::getRepository(AcademicYear::class)->find($id);

        if ($new instanceof AcademicYear) self::getSession()->set('academicYear', $new);
    }

    /**
     * getSession
     *
     * 24/09/2020 15:09
     * @return SessionInterface
     */
    private static function getSession(): SessionInterface
    {
        return self::$stack->getCurrentRequest()->getSession();
    }
}

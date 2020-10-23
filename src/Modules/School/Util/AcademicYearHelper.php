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
use App\Manager\Hidden\WarningItem;
use App\Manager\PageManager;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\System\Manager\DemoDataInterface;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Manager\Hidden\Day;
use App\Provider\ProviderFactory;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AcademicYearHelper
 *
 * 3/09/2019 10:01
 * @package App\Modules\School\Util
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearHelper implements DemoDataInterface
{
    /**
     * @var PageManager 
     */
    private static PageManager $pageManager;

    /**
     * getCurrentAcademicYear
     * @param bool $refresh
     * @return AcademicYear|null
     * 26/06/2020 08:58
     */
    public static function getCurrentAcademicYear(bool $refresh = false)
    {
        $session = self::getStack()->getCurrentRequest()->getSession();
        if ($session->has('academicYear') && $session->get('academicYear') !== null) {
            if ($refresh) {
                $current =  ProviderFactory::getRepository(AcademicYear::class)->find($session->get('academicYear')->getId());
                $session->set('academicYear', $current);
                return $current;
            }
            return $session->get('academicYear');
        }
        try {
            $current = ProviderFactory::getRepository(AcademicYear::class)->findOneByStatus('Current');
            if (null === $current) {
                $current = new AcademicYear();
                $current->setName(date('Y'))
                    ->setFirstDay(new DateTimeImmutable(date('Y-01-01')))
                    ->setLastDay(new DateTimeImmutable(date('Y-12-31')))
                    ->setStatus('Current');
                ProviderFactory::getEntityManager()->persist($current);
                ProviderFactory::getEntityManager()->flush();
            }
            $session->set("academicYear", $current);
        } catch (TableNotFoundException | ConnectionException $e) {
            $current = new AcademicYear();
            $current->setName(date('Y'))
                ->setFirstDay(new DateTimeImmutable(date('Y-01-01')))
                ->setLastDay(new DateTimeImmutable(date('Y-12-31')))
                ->setStatus('Current');
            return $current;
        }

        return $current;
    }

    /**
     * getNextAcademicYear
     * @param AcademicYear|null $year
     * @return mixed
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
            $warning = new WarningItem();
            $warning
                ->setPriority(75)
                ->setContent(self::getTwig()->render('school/academic_year_sidebar_warning.html.twig'))
                ->setPosition('bottom')
            ;
            self::getPageManager()
                ->getSidebar()
                ->addContent($warning);
            return $panel->addSection(new Section('html', self::getTwig()->render('school/academic_year_warning.html.twig')));
        } catch (LoaderError | RuntimeError | SyntaxError $e) {}
        return $panel;
    }

    /**
     * setCurrentYear
     *
     * 24/09/2020 15:04
     * @param string $id
     * @return bool
     */
    public function setCurrentYear(string $id): bool
    {
        $current = self::getCurrentAcademicYear();
        if ($current->getId() === $id) return false;

        $new = empty($id) ? ProviderFactory::getRepository(AcademicYear::class)->findOneBy(['status' => 'Current']) : ProviderFactory::getRepository(AcademicYear::class)->find($id);

        if ($new instanceof AcademicYear && $current->getId() === $new->getId()) return false;

        if ($new instanceof AcademicYear) self::getSession()->set('academicYear', $new);

        return true;
    }

    /**
     * getSession
     *
     * 24/09/2020 15:09
     * @return SessionInterface
     */
    private static function getSession(): SessionInterface
    {
        return self::getStack()->getCurrentRequest()->getSession();
    }

    /**
     * isCurrentYear
     *
     * 9/10/2020 10:31
     * @return bool
     */
    public static function isCurrentYear(): bool
    {
        return self::getCurrentAcademicYear() && self::getCurrentAcademicYear()->getStatus() === 'Current';
    }

    /**
     * hasNextYear
     *
     * 9/10/2020 10:32
     * @return bool
     */
    public static function hasNextYear(): bool
    {
        try {
            return self::getNextAcademicYear() instanceof AcademicYear;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * PageManager
     *
     * 16/10/2020 14:14
     * @return PageManager
     */
    public static function getPageManager(): PageManager
    {
        return self::$pageManager;
    }

    /**
     * @param PageManager $pageManager
     */
    public static function setPageManager(PageManager $pageManager): void
    {
        self::$pageManager = $pageManager;
    }

    /**
     * getStack
     *
     * 16/10/2020 14:14
     * @return RequestStack
     */
    private static function getStack(): RequestStack
    {
        return self::getPageManager()->getStack();
    }

    /**
     * getTwig
     *
     * 16/10/2020 14:16
     * @return Environment
     */
    private static function getTwig(): Environment
    {
        return self::getPageManager()->getTwig();
    }

    /**
     * isDateInCurrentYear
     *
     * 17/10/2020 09:31
     * @param DateTimeImmutable $date
     * @return bool
     */
    public static function isDateInCurrentYear(DateTimeImmutable $date): bool
    {
        return $date >= self::getCurrentAcademicYear()->getFirstDay() && $date <= self::getCurrentAcademicYear()->getLastDay();
    }

    /**
     * getPreviousSchoolDays
     *
     * 21/10/2020 11:31
     * @param DateTimeImmutable|null $date
     * @param int $days
     * @return array
     */
    public static function getPreviousSchoolDays(?DateTimeImmutable $date = null, int $days = 5): array
    {
        $date = $date ?: new DateTimeImmutable();
        $result = [];
        if (!self::isDateInCurrentYear($date)) return $result;
        $period = new DateInterval('P1D');
        for ($i=0; $i<$days; $i++) {
            $date = $date->sub($period);
            $day = new Day(clone $date);
            while (self::isDateInCurrentYear($day->getDate()) && !$day->isSchoolOpen()) {
                $date = $date->sub($period);
                $day = new Day(clone $date);
            }
            if (self::isDateInCurrentYear($day->getDate()) && $day->isSchoolOpen()) {
                array_unshift($result, $day->toArray());
            }

            if (!self::isDateInCurrentYear($day->getDate())) return $result;
        }

        return $result;
    }
}

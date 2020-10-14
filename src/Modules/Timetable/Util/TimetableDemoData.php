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
 * Date: 13/08/2020
 * Time: 13:35
 */
namespace App\Modules\Timetable\Util;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Entity\Facility;
use App\Modules\System\Manager\DemoDataInterface;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Modules\Timetable\Manager\MappingManager;
use App\Provider\ProviderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DemoData
 * @package App\Modules\Timetable\Util
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDemoData implements DemoDataInterface
{
    /**
     * @var MappingManager
     */
    private static $manager;

    /**
     * DemoData constructor.
     * @param MappingManager $manager
     */
    public function __construct(MappingManager $manager)
    {
        self::$manager = $manager;
    }

    /**
     * createTimetableDates
     * @param LoggerInterface $logger
     * 13/08/2020 13:42
     */
    public static function createTimetableDates(LoggerInterface $logger)
    {

        self::$manager->execute();
        $logger->notice(sprintf('%s records added to %s from a total of %s', self::$manager->getDayDates()->count(), TimetableDate::class, self::$manager->getDayDates()->count()));
    }

    /**
     * @var array
     */
    private static array $facilities = [];

    /**
     * @var array
     */
    private static array $classes = [];

    /**
     * @var array
     */
    private static array $periods = [];

    /**
     * createTimetablePeriodClass
     *
     * 14/10/2020 14:20
     * @param array $data
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     */
    public static function createTimetablePeriodClass(array $data, LoggerInterface $logger, ValidatorInterface $validator): int
    {
        $provider = ProviderFactory::create(TimetablePeriodClass::class);
        $statusManager = $provider->getMessageManager();
        $count = 0;
        foreach ($data as $q=>$item)         {
            if (!key_exists('period',$item) && 'Friday Red' === $item['day']) {
                foreach ($data as $datum) {
                    if ($datum['facility'] === $item['facility'] && $datum['day'] === 'Friday Blue' && $datum['course'] === $item['course'] && $datum['class'] === $item['class']) {
                        ++$count;
                        $data[$q]['period'] = $datum['period'];
                    }
                }
            }
        }
        if ($count > 0) file_put_contents(__DIR__ . '/../../../../Demo/timetable_period_class.yaml', Yaml::dump($data, 8));

        $count = 0;
        foreach ($data as $item)             {
            $periodClass = new TimetablePeriodClass();

            $facility = self::findFacility($item);
            if (null === $facility) {
                $logger->error(sprintf('The facility was not found for the TimetablePeriodClass %s',implode(', ', $item)));
                continue;
            }

            $class = self::findClass($item);
            if (null === $class) {
                $logger->error(sprintf('The course class was not found for the TimetablePeriodClass %s',implode(', ', $item)));
                continue;
            }

            $period = self::findPeriod($item);
            if (null === $period) {
                $logger->error(sprintf('The period was not found for the TimetablePeriodClass %s',implode(', ', $item)));
                continue;
            }

            $periodClass->setPeriod($period)
                ->setCourseClass($class)
                ->setFacility($facility)
            ;

            $statusManager->resetStatus();
            $provider->persist($periodClass);
            if ($statusManager->isStatusSuccess()) {
                if (++$count % 50 === 0) {
                    $statusManager->resetStatus();
                    $provider->flush();
                    if (!$statusManager->isStatusSuccess()) {
                        foreach ($statusManager->getMessageArray() as $message) {
                            $logger->error($message['message']);
                        }
                    }
                    $logger->notice(sprintf('50 (to %s) records pushed to the database for %s from %s', $count, TimetablePeriodClass::class, strval(count($data))));
                    ini_set('max_execution_time', 10);
                }
            }
        }
        $provider->flush();
        return $count;
    }

    /**
     * findPeriod
     *
     * 14/10/2020 14:35
     * @param array $item
     * @return TimetablePeriod|null
     */
    private static function findPeriod(array $item): ?TimetablePeriod
    {
        if (!key_exists('period',$item) || !key_exists('day', $item)) return null;

        $key = $item['period'].$item['day'];
        if (key_exists($key, self::$periods)) return self::$periods[$key];

        self::$periods[$key] = ProviderFactory::getRepository(TimetablePeriod::class)->findOneByPeriodNameDayName($item['period'],$item['day']);

        return self::$periods[$key];
    }

    /**
     * findFacility
     *
     * 14/10/2020 14:38
     * @param array $item
     * @return Facility|null
     */
    private static function findFacility(array $item): ?Facility
    {
        if (!key_exists('facility',$item)) return null;

        if (key_exists($item['facility'], self::$facilities)) return self::$facilities[$item['facility']];

        self::$facilities[$item['facility']] = ProviderFactory::getRepository(Facility::class)->findOneBy(['name' => $item['facility']]);

        return self::$facilities[$item['facility']];
    }

    /**
     * findPeriod
     *
     * 14/10/2020 14:35
     * @param array $item
     * @return TimetablePeriod|null
     */
    private static function findClass(array $item): ?CourseClass
    {
        if (!key_exists('course',$item) || !key_exists('class', $item)) return null;

        if (key_exists($item['course'].$item['class'], self::$classes)) return self::$classes[$item['course'].$item['class']];

        self::$classes[$item['course'].$item['class']] = ProviderFactory::getRepository(CourseClass::class)->findOneByCourseAbbreviationClassName($item['course'],$item['class']);

        return self::$classes[$item['course'].$item['class']];
    }
}

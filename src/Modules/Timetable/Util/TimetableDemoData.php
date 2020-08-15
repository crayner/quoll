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

use App\Modules\System\Manager\DemoDataInterface;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Manager\MappingManager;
use Psr\Log\LoggerInterface;

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
}

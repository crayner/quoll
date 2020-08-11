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
 * Date: 5/08/2020
 * Time: 09:56
 */
namespace App\Modules\Timetable\Manager;

use App\Modules\Timetable\Entity\TimetableDay;
use App\Provider\ProviderFactory;

/**
 * Class TimetableColumnManager
 * @package App\Modules\Timetable\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayManager
{
    /**
     * duplicateDayPeriods
     * @param TimetableDay $source
     * @param string $target
     * 5/08/2020 10:01
     */
    public function duplicateDayPeriods(TimetableDay $source, string $target)
    {
        $provider = ProviderFactory::create(TimetableDay::class);
        $target = $provider->getRepository()->find($target);

        foreach($source->getPeriods() as $period) {
            $period = clone($period);
            $period->setId(null);
            $target->addPeriod($period);

        }

        return $provider->persistFlush($target);
    }
}

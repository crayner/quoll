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
 * Date: 27/10/2020
 * Time: 13:30
 */
namespace App\Modules\Attendance\Manager;

/**
 * Class AttendanceLogger
 *
 * 27/10/2020 13:32
 * @package App\Modules\Attendance\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceLogger
{
    /**
     * @var array
     */
    private array $events = [];

    /**
     * Events
     *
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Events
     *
     * @param array $events
     * @return AttendanceLogger
     */
    public function setEvents(array $events): AttendanceLogger
    {
        $this->events = $events;
        return $this;
    }

    /**
     * addEvent
     *
     * 27/10/2020 13:34
     * @param array $event
     * @return AttendanceLogger
     */
    public function addEvent(array $event): AttendanceLogger
    {
        $this->events[] = $event;
        return $this;
    }
}

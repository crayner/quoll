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
 * Date: 2/10/2020
 * Time: 16:04
 */
namespace App\Modules\Attendance\Manager\Hidden;

use App\Modules\Enrolment\Entity\CourseClass;
use DateTimeImmutable;

/**
 * Class AttendanceByCode
 * @package App\Modules\Attendance\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByClass
{
    /**
     * @var CourseClass
     */
    private CourseClass $class;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $date;

    /**
     * @return CourseClass|null
     */
    public function getClass(): ?CourseClass
    {
        return isset($this->class) ? $this->class : null;
    }

    /**
     * @param CourseClass $class
     * @return AttendanceByClass
     */
    public function setClass(CourseClass $class): AttendanceByClass
    {
        $this->class = $class;
        return $this;
    }

    /**
     * getDate
     *
     * 3/11/2020 13:55
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date = isset($this->date) ? $this->date : new DateTimeImmutable();
    }

    /**
     * @param DateTimeImmutable $date
     * @return AttendanceByClass
     */
    public function setDate(DateTimeImmutable $date): AttendanceByClass
    {
        $this->date = $date;
        return $this;
    }

}

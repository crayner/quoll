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
 * Date: 6/08/2020
 * Time: 07:40
 */
namespace App\Modules\Timetable\Manager\Hidden;

use App\Modules\School\Entity\AcademicYearTerm;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Week
 * @package App\Modules\Timetable\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Week
{
    /**
     * @var ArrayCollection|Day[]
     */
    private $days;

    /**
     * @var AcademicYearTerm
     */
    private $term;

    /**
     * @var integer
     */
    private $number;

    /**
     * Week constructor.
     * @param int $number
     */
    public function __construct(int $number)
    {
        $this->setNumber($number);
    }

    /**
     * @return Day[]|ArrayCollection
     */
    public function getDays()
    {
        return $this->days = $this->days ?: new ArrayCollection();
    }

    /**
     * @param Day[]|ArrayCollection $days
     * @return Week
     */
    public function setDays($days): Week
    {
        $this->days = $days;
        return $this;
    }

    public function addDay(Day $day): Week
    {
        $this->getDays()->set($day->getDate()->format('Ymd'),$day);
        return $this;
    }

    /**
     * @return AcademicYearTerm
     */
    public function getTerm(): AcademicYearTerm
    {
        return $this->term;
    }

    /**
     * @param AcademicYearTerm $term
     * @return Week
     */
    public function setTerm(AcademicYearTerm $term): Week
    {
        $this->term = $term;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return Week
     */
    public function setNumber(int $number): Week
    {
        $this->number = $number;
        return $this;
    }

    /**
     * toArray
     * @return array
     * 8/08/2020 10:36
     */
    public function toArray(): array
    {
        return [
            'number' => $this->getNumber(),
            'days' => $this->getDaysArray(),
        ];
    }

    /**
     * getDaysArray
     * @return array
     * 8/08/2020 11:02
     */
    private function getDaysArray(): array
    {
        $days = [];
        foreach ($this->getDays() as $day) {
            $days[] = $day->toArray();
        }
        return $days;
    }
}

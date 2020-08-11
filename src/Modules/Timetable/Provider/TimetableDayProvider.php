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
 * Date: 4/08/2020
 * Time: 11:39
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\TimetableDay;
use App\Provider\AbstractProvider;
use Doctrine\Common\Collections\Collection;

/**
 * Class TimetableColumnProvider
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = TimetableDay::class;

    /**
     * @var Collection|TimetableDay[]|null
     */
    private $columns;

    /**
     * isFixed
     * @param TimetableDay $column
     * @return bool
     * 10/08/2020 11:00
     */
    public function isFixed(TimetableDay $column): bool
    {
        if ($this->columns === null) {
            $this->columns = $this->getRepository()->findAll();
        }

        $dow = [];
        foreach ($this->columns as $item) {
            foreach ($item->getDaysOfWeek() as $d) {
                if (!key_exists($d->getSortOrder(), $dow)) {
                    $dow[$d->getSortOrder()] = 0;
                }
                $dow[$d->getSortOrder()]++;
            }
        }

        if ($column->getDaysOfWeek()->count() === 0) {
            return false;
        }

        foreach ($column->getDaysOfWeek() as $d) {
            if (key_exists($d->getSortOrder(), $dow) && $dow[$d->getSortOrder()] > 1) {
                return false;
            }
        }

        return true;
    }
}

<?php
/**
 * Created by PhpStorm.
 *
 * This file is part of the Busybee Project.
 *
 * (c) Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/09/2018
 * Time: 16:03
 */
namespace App\Modules\Timetable\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class TimetableColumnValidator
 * @package App\Modules\Timetable\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableColumnPeriodValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     * @return void
     * @throws \Exception
     * 4/08/2020 14:30
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Modules\Timetable\Entity\TimetableColumnPeriod) return $value;

        $column = $value->getTimetableColumn();
        $rows = $column->getTimetableColumnPeriods();

        $iterator = $rows->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getTimeStart() < $b->getTimeStart()) ? -1 : 1;
        });

        $rows = new ArrayCollection(iterator_to_array($iterator, false));

        $row = $rows->first();
        $previous = null;
        do {
            if ($row === $value || $previous === $value) {
                if ($previous instanceof \App\Modules\Timetable\Entity\TimetableColumnPeriod && $row === $value) {
                    if ($previous->getTimeEnd()->format('Hi') > $row->getTimeStart()->format('Hi')) {
                        $this->context->buildViolation('An overlap exists between the previous row "(%previous%)" and the current row "(%current%)."')
                            ->setParameter('%previous%', $previous->getName() . ' ' . $previous->getTimeEnd()->format('H:i'))
                            ->setParameter('%current%', $row->getName() . ' ' . $row->getTimeStart()->format('H:i'))
                            ->setTranslationDomain($constraint->transDomain)
                            ->atPath('timeStart')
                            ->setCode(TimetableColumnPeriod::TIMETABLE_COLUMN_ROW_ERROR)
                            ->addViolation();
                    }
                    if ($row->getTimeStart()->format('Hi') > $previous->getTimeEnd()->format('Hi')) {
                        $this->context->buildViolation('A gap in time exists between the previous row "(%previous%)" and the current row "(%current%)"')
                            ->setParameter('%previous%', $previous->getName() . ' ' . $previous->getTimeEnd()->format('H:i'))
                            ->setParameter('%current%', $row->getName() . ' ' . $row->getTimeStart()->format('H:i'))
                            ->setTranslationDomain($constraint->transDomain)
                            ->atPath('timeStart')
                            ->setCode(TimetableColumnPeriod::TIMETABLE_COLUMN_ROW_ERROR)
                            ->addViolation();
                    }
                }
                if ($previous === $value) {
                    if ($previous->getTimeEnd()->format('Hi') > $row->getTimeStart()->format('Hi')) {
                        $this->context->buildViolation('An overlap exists between this row "(%previous%)" and the next row "(%current%)."')
                            ->setParameter('%previous%', $previous->getName() . ' ' . $previous->getTimeEnd()->format('H:i'))
                            ->setParameter('%current%', $row->getName() . ' ' . $row->getTimeStart()->format('H:i'))
                            ->setTranslationDomain($constraint->transDomain)
                            ->atPath('timeEnd')
                            ->setCode(TimetableColumnPeriod::TIMETABLE_COLUMN_ROW_ERROR)
                            ->addViolation();
                    }
                    if ($row->getTimeStart()->format('Hi') > $previous->getTimeEnd()->format('Hi')) {
                        $this->context->buildViolation('A gap in time exists between this row "(%previous%)" and the next row "(%current%)"')
                            ->setParameter('%previous%', $previous->getName() . ' ' . $previous->getTimeEnd()->format('H:i'))
                            ->setParameter('%current%', $row->getName() . ' ' . $row->getTimeStart()->format('H:i'))
                            ->setTranslationDomain($constraint->transDomain)
                            ->atPath('timeEnd')
                            ->setCode(TimetableColumnPeriod::TIMETABLE_COLUMN_ROW_ERROR)
                            ->addViolation();
                    }

                }
            }
            $previous = $rows->current();
        } while (false !== ($row = $rows->next()));
    }

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * TimetableColumnValidator constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
}
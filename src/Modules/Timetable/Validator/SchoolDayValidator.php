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
 * Date: 7/11/2020
 * Time: 08:24
 */
namespace App\Modules\Timetable\Validator;

use App\Modules\Timetable\Entity\TimetableDate;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SchoolDayValidator
 *
 * 7/11/2020 08:28
 * @package App\Modules\Timetable\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SchoolDayValidator extends ConstraintValidator
{
    /**
     * validate
     *
     * 7/11/2020 08:28
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof DateTimeImmutable) return;

        $count = ProviderFactory::getRepository(TimetableDate::class)->countValidDates($value, $constraint->enforceCurrentYear);

        if ($count === 0) {
            $this->context->buildViolation('"_date_" is not a valid school date.')
                ->setParameter('_date_', $value->format('jS M'))
                ->setTranslationDomain($constraint->transDomain)
                ->setCode(SchoolDay::VALID_SCHOOL_DAY_ERROR)
                ->addViolation();
        }
    }
}
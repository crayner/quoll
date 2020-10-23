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
 * Date: 23/10/2020
 * Time: 11:12
 */
namespace App\Modules\Attendance\Validator;

use App\Modules\System\Manager\SettingFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ReasonValidator
 *
 * 23/10/2020 11:14
 * @package App\Modules\Attendance\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ReasonValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) return null;

        $reasons = SettingFactory::getSettingManager()->get('Attendance', 'attendanceReasons');

        if (!in_array($value, $reasons)) {
            $this->context->buildViolation('The reason is not valid. Valid reasons are: "{reasons}"')
                ->setCode(Reason::INVALID_REASON_ERROR)
                ->setParameter('{reasons}', implode('","', $reasons))
                ->atPath('reason')
                ->setTranslationDomain('Attendance')
                ->addViolation();
        }
    }
}
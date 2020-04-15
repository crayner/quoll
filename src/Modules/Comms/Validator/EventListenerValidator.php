<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 28/03/2020
 * Time: 12:35
 */

namespace App\Modules\Comms\Validator;

use App\Modules\Comms\Entity\NotificationListener;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EventListenerValidator
 * @package App\Modules\Comms\validator
 */
class EventListenerValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof NotificationListener)
            return ;

        if ($value->getScopeType() === 'All' || $value->getScopeType() === '' || $value->getScopeType() === null) {
            $value->setScopeID(null);
            return;
        }

        if (null === $value->getScopeID() || '' === $value->getScopeID())
        {
            $this->context->buildViolation('This value cannot be blank when the Scope is set to {name}')
                ->atPath('scopeID')
                ->setTranslationDomain('SystemAdmin')
                ->setParameter('{name}', TranslationsHelper::tranlate($value->getScopeType(), [], 'messages'))
                ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'Student') {
            $person = ProviderFactory::getRepository(Person::class)->find($value->getScopeID());
            if (null === $person || !$person->isStudent())
                $this->context->buildViolation('The value is not a valid student.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('SystemAdmin')
                    ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'Staff') {
            $person = ProviderFactory::getRepository(Person::class)->find($value->getScopeID());
            if (null === $person || !$person->isStaff())
                $this->context->buildViolation('The value is not a valid staff member.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('SystemAdmin')
                    ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'Year Group') {
            $yg = ProviderFactory::getRepository(YearGroup::class)->find($value->getScopeID());
            if (!$yg instanceof YearGroup)
                $this->context->buildViolation('The value is not a valid Year Group.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('SystemAdmin')
                    ->addViolation();
            return;
        }
    }

}
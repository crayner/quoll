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
 * Date: 28/03/2020
 * Time: 12:35
 */
namespace App\Modules\Comms\Validator;

use App\Modules\Comms\Entity\NotificationListener;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
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
     * 24/06/2020 15:36
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof NotificationListener)
            return ;

        if ($value->getScopeType() === 'All' || $value->getScopeType() === '' || $value->getScopeType() === null) {
            $value->setScopeIdentifier('');
        }

        if ($value->getScopeType() === '' || $value->getScopeType() === null) {
            return;
        }

        if ($value->getPerson() === null) {
            $this->context->buildViolation($constraint->personMissingMessage)
                ->atPath('person')
                ->setCode(EventListener::PERSON_MISSING_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return;

        }

        if ($value->getScopeType() === null) {
            $this->context->buildViolation($constraint->scopeTypeMissingMessage)
                ->atPath('scopeType')
                ->setCode(EventListener::SCOPE_TYPE_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return;
        }

        // Check unique
        if (!ProviderFactory::getRepository(NotificationListener::class)->isUnique($value)) {
            $this->context->buildViolation($constraint->uniqueNotificationListenerMessage)
                ->atPath('person')
                ->setCode(EventListener::UNIQUE_NOTIFICATION_LISTENER_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'All') {
            return;
        }

        if (null === $value->getScopeIdentifier() || '' === $value->getScopeIdentifier())
        {
            $this->context->buildViolation($constraint->scopeIdentifierMessage)
                ->atPath('scopeIdentifier')
                ->setCode(EventListener::SCOPE_IDENTIFIER_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->setParameter('{name}', TranslationHelper::translate($value->getScopeType(), [], $constraint->transDomain))
                ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'student') {
            $person = ProviderFactory::getRepository(Person::class)->find($value->getScopeIdentifier());
            if (null === $person || !$person->isStudent())
                $this->context->buildViolation($constraint->studentMessage)
                    ->atPath('scopeIdentifier')
                    ->setCode(EventListener::STUDENT_ERROR)
                    ->setTranslationDomain($constraint->transDomain)
                    ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'staff') {
            $person = ProviderFactory::getRepository(Person::class)->find($value->getScopeIdentifier());
            if (null === $person || !$person->isStaff())
                $this->context->buildViolation($constraint->staffMessage)
                    ->atPath('scopeIdentifier')
                    ->setCode(EventListener::STAFF_ERROR)
                    ->setTranslationDomain($constraint->transDomain)
                    ->addViolation();
            return;
        }

        if ($value->getScopeType() === 'year_group') {
            $yg = ProviderFactory::getRepository(YearGroup::class)->find($value->getScopeIdentifier());
            if (!$yg instanceof YearGroup)
                $this->context->buildViolation($constraint->yearGroupMessage)
                    ->atPath('scopeIdentifier')
                    ->setCode(EventListener::YEAR_GROUP_ERROR)
                    ->setTranslationDomain($constraint->transDomain)
                    ->addViolation();
            return;
        }
    }

}
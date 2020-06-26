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
 * Time: 12:34
 */
namespace App\Modules\Comms\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class EventListener
 * @package App\Modules\Comms\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotation
 */
class EventListener extends Constraint
{
    const SCOPE_IDENTIFIER_ERROR = '50804b71-212d-4efc-a0e9-42019b372758';
    const SCOPE_TYPE_ERROR = '6f4f877d-d786-429d-bf6a-a929b7375455';
    const UNIQUE_NOTIFICATION_LISTENER_ERROR = '18f09078-7b33-4e75-9456-fb5c08cc9b51';
    const PERSON_MISSING_ERROR = '9c3bfa3c-d4e9-4e18-8552-c83c68e25de4';
    const STUDENT_ERROR = '76f6cc07-4dfd-463c-837c-f58fd3cc1449';
    const STAFF_ERROR = 'd0b0f412-eac9-43a3-9bf8-ca766efd686b';
    const YEAR_GROUP_ERROR = '015226b5-55c8-4969-915f-d6b210183f62';

    protected static $errorNames = [
        self::SCOPE_IDENTIFIER_ERROR => 'SCOPE_IDENTIFIER_ERROR',
        self::UNIQUE_NOTIFICATION_LISTENER_ERROR => 'UNIQUE_NOTIFICATION_LISTENER_ERROR',
        self::STUDENT_ERROR => 'STUDENT_ERROR',
        self::STAFF_ERROR => 'STAFF_ERROR',
        self::YEAR_GROUP_ERROR => 'YEAR_GROUP_ERROR',
        self::PERSON_MISSING_ERROR => 'PERSON_MISSING_ERROR',
        self::SCOPE_TYPE_ERROR => 'SCOPE_TYPE_ERROR',
    ];

    public $scopeIdentifierMessage = 'This scope choice cannot be blank when the Scope is set to {name}';
    public $uniqueNotificationListenerMessage = 'The listener is NOT unique.';
    public $staffMessage = 'The scope choice is not a valid staff member.';
    public $studentMessage = 'The scope choice is not a valid student.';
    public $yearGroupMessage = 'The scope choice is not a valid Year Group.';
    public $personMissingMessage = 'The person must not be blank.';
    public $scopeTypeMissingMessage = 'The scope must not be blank.';


    public $transDomain = 'System';

    /**
     * getTargets
     * @return array|string
     * 23/06/2020 16:02
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
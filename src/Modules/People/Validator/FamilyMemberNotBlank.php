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
 * Date: 11/07/2020
 * Time: 12:55
 */
namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class FamilyMemberNotBlank
 * @package App\Modules\People\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 * @Annotations()
 */
class FamilyMemberNotBlank extends Constraint
{
    const PARENT_AND_STUDENT_ERROR = '0d5d5ab4-922c-4b66-bbc2-d0ca06bbaf4c';

    protected static $errorNames = [
        self::PARENT_AND_STUDENT_ERROR => 'PARENT_AND_STUDENT_ERROR',
    ];

    public $message = ['student' => 'A student must be set.', 'careGiver' => 'A care giver must be set.'];

    public $transDomain = 'People';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
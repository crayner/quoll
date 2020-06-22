<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 * 
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/12/2019
 * Time: 16:28
 */

namespace App\Modules\Students\Util;

use App\Modules\People\Entity\Person;
use App\Modules\People\Util\UserHelper;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;

/**
 * Class StudentHelper
 * @package App\Modules\Students\Util
 */
class StudentHelper
{
    /**
     * @var array
     */
    private static $noteNotificationList = [
        'Tutors',
        'Tutors and Teachers',
    ];

    /**
     * @return array
     */
    public static function getNoteNotificationList(): array
    {
        return self::$noteNotificationList;
    }

    /**
     * getCurrentRollGroup
     * @param Person|int $person
     */
    public static function getCurrentRollGroup($person): string
    {
        if (is_int($person))
            $person = ProviderFactory::getRepository(Person::class)->find($person);
        if (!$person instanceof Person)
            return '';

        if (!UserHelper::isStudent($person))
            return '';

        $se = null;
        foreach($person->getStudentEnrolments() as $enrolment)
        {
            if ($enrolment->getAcademicYear()->getId() === AcademicYearHelper::getCurrentAcademicYear()->getId()) {
                $se = $enrolment;
                break;
            }
        }
        if ($se === null)
            return '';

        return $se->getRollGroup()->getName();
    }
}
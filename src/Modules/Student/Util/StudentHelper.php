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

namespace App\Modules\Student\Util;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;

/**
 * Class StudentHelper
 * @package App\Modules\Student\Util
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
    public static function getCurrentRollGroup($student): string
    {
        if (is_int($student)) $student = ProviderFactory::getRepository(Student::class)->find($student);
        if ($student instanceof Person && $student->isStudent()) $student = $student->getPerson();
        if (!$student instanceof Student)
            return '';


        $se = null;
        foreach($student->getStudentEnrolments() as $enrolment)
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
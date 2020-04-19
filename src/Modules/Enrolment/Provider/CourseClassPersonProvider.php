<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 10/08/2019
 * Time: 14:58
 */

namespace App\Modules\Enrolment\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\School\Util\AcademicYearHelper;

class CourseClassPersonProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = CourseClassPerson::class;

    /**
     * getClassPeopleList
     * @return array
     */
    public function getClassStudentList(CourseClass $class): array
    {
        $schoolYear = AcademicYearHelper::getCurrentAcademicYear();
        $date = new \DateTime(date('Y-m-d'));

        $list = $this->getRepository()->findStudentsInClass($class, $schoolYear, $date);

        return $list;
    }
}
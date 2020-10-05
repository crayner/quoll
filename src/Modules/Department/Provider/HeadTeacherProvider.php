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
 * Date: 3/10/2020
 * Time: 09:23
 */
namespace App\Modules\Department\Provider;

use App\Modules\Department\Entity\Department;
use App\Modules\Department\Entity\HeadTeacher;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\YearGroup;
use App\Provider\AbstractProvider;

/**
 * Class HeadTeacherProvider
 * @package App\Modules\Department\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HeadTeacherProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = HeadTeacher::class;

    /**
     * addDepartmentClasses
     *
     * 4/10/2020 17:20
     * @param Department $department
     * @param HeadTeacher $headTeacher
     */
    public function addDepartmentClasses(Department $department, HeadTeacher $headTeacher)
    {
        foreach ($this->getRepository(CourseClass::class)->findByDepartment($department) as $class) {
            $headTeacher->addClass($class);
        }
        $this->persistFlush($headTeacher);
    }

    /**
     * addYearGroupClasses
     *
     * 4/10/2020 17:36
     * @param YearGroup $yearGroup
     * @param HeadTeacher $headTeacher
     */
    public function addYearGroupClasses(YearGroup $yearGroup, HeadTeacher $headTeacher)
    {
        foreach ($this->getRepository(CourseClass::class)->findByYearGroup($yearGroup) as $class) {
            $headTeacher->addClass($class);
        }
        foreach ($this->getRepository(RollGroup::class)->findByYearGroup($yearGroup))
        $this->persistFlush($headTeacher);
    }
}

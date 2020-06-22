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
 * Date: 12/08/2019
 * Time: 14:56
 */
namespace App\Modules\Enrolment\Provider;

use App\Modules\Department\Twig\MyClasses;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Provider\AbstractProvider;
use App\Twig\SidebarContent;

/**
 * Class CourseClassProvider
 * @package App\Modules\Enrolment\Provider
 */
class CourseClassProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = CourseClass::class;

    /**
     * getMyClasses
     * @param Person|SecurityUser|string|null $person
     * @param SidebarContent|null $sidebar
     * @return array
     */
    public function getMyClasses($person, ?SidebarContent $sidebar = null): array
    {
        $result = [];
        if ($person instanceof SecurityUser)
            $result = $this->getRepository()->findByAcademicYearPerson(AcademicYearHelper::getCurrentAcademicYear(), $person->getPerson());
        elseif ($person instanceof Person)
            $result = $this->getRepository()->findByAcademicYearPerson(AcademicYearHelper::getCurrentAcademicYear(), $person);

        if (count($result) > 0 && null !== $sidebar) {
            $myClasses = new MyClasses();
            $sidebar->addContent($myClasses->setClasses($result));
        }

        return $result ?: [];
    }
}
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
use App\Modules\Enrolment\Entity\CourseClassPerson;
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

    /**
     * getCourseClassEnrolmentPaginationContent
     *
     * 10/09/2020 13:00
     * @return array
     */
    public function getCourseClassEnrolmentPaginationContent(): array
    {
        $result = $this->getRepository()->findCourseClassEnrolmentPagination();
        $active = $this->getRepository(CourseClass::class)->countParticipants('Full');
        $expected = $this->getRepository(CourseClass::class)->countParticipants('Expected');
        $total = $this->getRepository(CourseClass::class)->countParticipants();
        foreach ($active as $id=>$value) {
            if (key_exists($id, $result)) {
                $result[$id]['activeParticipants'] = $value['participants'];
            }
        }
        foreach ($expected as $id=>$value) {
            if (key_exists($id, $result)) {
                $result[$id]['expectedParticipants'] = $value['participants'];
            }
        }
        foreach ($total as $id=>$value) {
            if (key_exists($id, $result)) {
                $result[$id]['totalParticipants'] = $value['participants'];
            }
        }

        return array_values($result);
    }

    /**
     * getIndividualClassChoices
     *
     * 11/09/2020 09:05
     * @param Person $person
     * @return array
     */
    public function getIndividualClassChoices(Person $person): array
    {
        if ($person->isStudent()) {
            $result['-- --Enrolable Classes-- --'] = $this->getRepository()->findEnrolableClasses($person);
            $c = $this->getRepository()->findClassesByCurrentAcademicYear();
            foreach ($result['-- --Enrolable Classes-- --'] as $q=>$w) {
                if (key_exists($q, $c)) unset($c[$q]);
            }
            $result['-- --Enrolable Classes-- --'] = array_values($result['-- --Enrolable Classes-- --']);
            $result['-- --All Classes-- --'] = array_values($c);
        } else {
            $result = array_values($this->getRepository()->findClassesByCurrentAcademicYear());
        }
        return $result;
    }
}

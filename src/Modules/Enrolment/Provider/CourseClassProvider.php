<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use App\Modules\Enrolment\Manager\Traits\EntityTrait;
use App\Modules\Departments\Twig\MyClasses;
use App\Modules\People\Manager\SecurityUser;
use App\Modules\Enrolment\Twig\SidebarContent;

/**
 * Class CourseClassProvider
 * @package App\Modules\Enrolment\Provider
 */
class CourseClassProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = CourseClass::class;

    /**
     * getMyClasses
     * @param Person|SecurityUser|string|null $person
     * @param SidebarContent|null $sidebar
     * @return array
     * @throws \Exception
     */
    public function getMyClasses($person, ?SidebarContent $sidebar = null)
    {
        $result = [];
        if ($person instanceof SecurityUser)
            $result = $this->getRepository()->findByPersonSchoolYear($this->getSession()->get('academicYear'), $person->getPerson());
        elseif ($person instanceof Person)
            $result = $this->getRepository()->findByPersonSchoolYear($this->getSession()->get('academicYear'), $person);

        if (count($result) > 0 && null !== $sidebar) {
            $myClasses = new MyClasses();
            $sidebar->addContent($myClasses->setClasses($result));
        }

        return $result ?: [];
    }
}
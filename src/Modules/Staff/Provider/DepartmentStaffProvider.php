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
 * Time: 20:54
 */

namespace App\Modules\Staff\Provider;

use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\Department;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Staff\Entity\DepartmentStaff;
use App\Provider\AbstractProvider;
use App\Util\ErrorMessageHelper;

/**
 * Class DepartmentStaffProvider
 * @package App\Provider
 */
class DepartmentStaffProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = DepartmentStaff::class;

    /**
     * getRole
     * @param Department $department
     * @param Person|SecurityUser|null $person
     * @return bool
     * @throws \Exception
     */
    public function getRole(Department $department, $person = null): bool
    {
        if($person instanceof SecurityUser)
            $person = $person->getPerson();

        $result = $this->getRepository()->findOneBy(['department' => $department, 'person' => $person]);

        return $result ? $result->getRole() : false;
    }

    /**
     * writeDepartmentStaff
     * @param Department $department
     * @param array $personList
     * @param string $role
     * @param array $data
     * @return array
     */
    public function writeDepartmentStaff(Department $department, array $personList, string $role, array $status): array
    {
        if (empty($personList) || empty($role))
        {
           return ErrorMessageHelper::getInvalidInputsMessage($status);
        }

        foreach($personList as $personId)
        {
            $person = $this->getRepository(Person::class)->find($personId);
            if ($person instanceof Person)
            {
                $ds = $this->getRepository()->findOneBy(['department' => $department, 'person' => $person]) ?: new DepartmentStaff();
                $ds->setPerson($person)->setDepartment($department)->setRole($role);
                $status = $this->persistFlush($ds, $status, false);
            }
        }
        $this->flush();
        return $status;
    }
}
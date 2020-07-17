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
 * Date: 10/08/2019
 * Time: 20:54
 */
namespace App\Modules\Department\Provider;

use App\Modules\People\Entity\Person;
use App\Modules\Department\Entity\Department;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Provider\AbstractProvider;

/**
 * Class DepartmentStaffProvider
 * @package App\Modules\Department\Provider
 * @author Craig Rayner <craig@craigrayner.com>
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
     * isHeadTeacherOf
     * @param Person $person
     * @param array $staffIDList
     * @param bool $includeAssistant
     * @return bool
     * 19/06/2020 14:09
     */
    public function isHeadTeacherOf(Person $person, array $staffIDList, bool $includeAssistant = true): bool
    {
        return $this->getRepository()->countWhenPersonIsHeadOf($person,$staffIDList,$includeAssistant) > 0;
    }

}
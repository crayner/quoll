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
namespace App\Modules\Department\Provider;

use App\Modules\People\Entity\Person;
use App\Modules\Department\Entity\Department;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Provider\AbstractProvider;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

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
     * writeDepartmentStaff
     * @param Department $department
     * @param string $personId
     * @param string $role
     * @param array $status
     * @param FormInterface $form
     * @return array
     */
    public function writeDepartmentStaff(Department $department, string $personId, string $role, array $status, FormInterface $form): array
    {
        if (empty($personId) || empty($role))
        {
            if (empty($personId)) {
                $form->get('newStaff')->addError(new FormError(TranslationHelper::translate('This value should not be blank.', [], 'validators')));
            }
            if (empty($role)) {
                $form->get('role')->addError(new FormError(TranslationHelper::translate('This value should not be blank.', [], 'validators')));
            }
            return ErrorMessageHelper::getInvalidInputsMessage($status, true);
        }

        $person = $this->getRepository(Person::class)->find($personId);
        if ($person instanceof Person)
        {
            $ds = $this->getRepository()->findOneBy(['department' => $department, 'person' => $person]) ?? new DepartmentStaff();
            $ds->setPerson($person)->setDepartment($department)->setRole($role);
            $form->get('person')->setData($person);
            $form->get('role')->setData($role);
            $status = $this->persistFlush($ds, $status);
        }

        return $status ?? [];
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
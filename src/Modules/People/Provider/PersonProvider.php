<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/07/2019
 * Time: 09:47
 */

namespace App\Modules\People\Provider;

use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\IndividualNeed\Entity\INPersonDescriptor;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Phone;
use App\Modules\School\Entity\House;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PersonProvider
 * @package App\Modules\people\Provider
 */
class PersonProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Person::class;

    /**
     * handleRegistration
     * @param FormInterface $form
     */
    public function handleRegistration(FormInterface $form)
    {
        $person = $form->getData();
        $this->setEntity($person);

        $person->setOfficialName($person->getFirstName() . ' ' . $person->getSurname());

        $raw = $form->get('passwordNew')->getData();
        $user = new SecurityUser($person);
        SecurityHelper::encodeAndSetPassword($user, $raw);
        $person->setStatus(SettingFactory::getSettingManager()->get('People', 'publicRegistrationDefaultStatus'));
        $role = SettingFactory::getSettingManager()->get('People', 'publicRegistrationDefaultRole');
        $person->setSecurityRoles([$role]);

        foreach($form->get('fields')->getData() as $key=>$value)
        {
            $value = $form->get('fields')->get($key)->get('value')->getData();
            $person->addField($key,$value);
        }

        $this->saveEntity();
        if ($person->getStatus() === 'Pending Approval') {
            // Raise a new notification event
            $event = ProviderFactory::create(NotificationEvent::class)->createEvent('User Admin', 'New Public Registration');

            $event->addRecipient($this->getSession()->get('organisationAdmissions'));
            $event->setNotificationText('An new public registration, for {oneString}, is pending approval.')->setNotificationTextOptions(['{oneString}' => $person->formatName()]);
            $event->setActionLink("/?q=/modules/User Admin/user_manage_edit.php&gibbonPersonID=". $person->getId()."&search=");

            $event->sendNotifications();

            $data['errors'][] = ['class' => 'success', 'message' => ['Your registration was successfully submitted and is now pending approval. Our team will review your registration and be in touch in due course.', [], 'People']];
        } else {
            $data['errors'][] = ['class' => 'success', 'messages' => ['Your registration was successfully submitted, and you may now log into the system using your new username and password.', [], 'People']];
        }
        return $data;
    }

    /**
     * getCurrentStudentChoiceList
     * @param bool $useEntity
     * @return array
     */
    public function getCurrentStudentChoiceList(bool $useEntity = false): array {
        $result = [];
        foreach($this->getRepository()->findCurrentStudents() as $q=>$w){
            if ($useEntity)
                $result[$w->getId()] = $w;
            else
                $result[] = new ChoiceView($w->toArray('short'), $w->getId(), $w->getFullNameReversed(), []);
        }
        return $result;
    }

    /**
     * @var array|null
     */
    private $staffChoiceList;

    /**
     * getCurrentStaffChoiceList
     * @param bool $useEntity
     * @return array
     */
    public function getCurrentStaffChoiceList(bool $useEntity = false): array
    {
        if (null === $this->staffChoiceList) {
            $result = [];
            foreach ($this->getRepository()->findCurrentStaff() as $q => $w) {
                if ($useEntity)
                    $result[$w->getId()] = $w;
                else
                    $result[] = new ChoiceView($w->toArray('short'), $w->getId(), $w->getFullNameReversed(), []);
            }
            $this->staffChoiceList = $result;
        }
        return $this->staffChoiceList;
    }

    /**
     * isStudent
     * @param Person $person
     * @return bool
     * 26/06/2020 10:54
     * @deprecated Use Person::isStudent() directly
     */
    public function isStudent(Person $person): bool
    {
        trigger_error(sprintf('%s id deprecated. Please use %s::isStudent() directly.', __METHOD__, Person::class), E_USER_DEPRECATED);
        return $person->isStudent();
    }

    /**
     * findAllFullList
     * @throws \Exception
     */
    public function findAllFullList()
    {
        $result = $this->getRepository()->findAllFullList();
        $w = [];
        foreach($result as $x) {
            while (isset($w[$x['fullName']]))
                $x['fullName'] .= '.';
            $w[$x['fullName']] = $x['id'];
        }
        return $w;
    }

    /**
     * isCareGiver
     * @param Person|null $person
     * @return bool
     */
    public function isCareGiver(?Person $person = null): bool
    {
        if (null === $person)
            $person = $this->getEntity();

        return $person->isCareGiver();
    }

    /**
     * groupedChoiceList
     */
    public function groupedChoiceList(): array
    {
        $people = $this->getRepository(Student::class)->findCurrentStudentsAsArray();
        $people = array_merge($this->getRepository(Staff::class)->findCurrentStaffAsArray(),$people);
        $people = array_merge($this->getRepository(CareGiver::class)->findCurrentCareGiversAsArray(),$people);


        dump($people[100],$people[700],$people[1000]);

        uasort($people, function($a, $b) {
            return $a['data'] < $b['data'] ? -1 : 1;
        });

        return array_values($people);
    }

    /**
     * findByRoles
     * @param array $roles
     * @return array
     * 23/06/2020 11:49
     */
    public function findByRoles(array $roles): array
    {
        return $this->getRepository()->findByRoles(SecurityHelper::getHierarchy()->getRoleNamesThatReach($roles));
    }

    /**
     * getPaginationContent
     * @return array
     */
    public function getPaginationContent(): array
    {
        return $this->getRepository()->getPaginationContent();
    }
}
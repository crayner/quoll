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
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\FamilyMemberChild;
use App\Modules\People\Entity\Phone;
use App\Modules\School\Entity\House;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\System\Entity\Setting;
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
class PersonProvider extends AbstractProvider implements UserLoaderInterface
{
    /**
     * @var string
     */
    protected $entityName = Person::class;

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username): ?UserInterface
    {
        $person = $this->getRepository()->loadUserByUsernameOrEmail($username);

        return $person ? new SecurityUser($person) : null;
    }

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
        $person->setStatus(ProviderFactory::create(Setting::class)->getSettingByScope('People', 'publicRegistrationDefaultStatus'));
        $role = ProviderFactory::create(Setting::class)->getSettingByScope('People', 'publicRegistrationDefaultRole');
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
                $result[] = new ChoiceView([], $w->getId(), $w->formatName(['style' => 'long', 'reverse' => true]), []);
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
                    $result[] = new ChoiceView($w, $w->getId(), $w->formatName(['style' => 'long', 'reverse' => true]), []);
            }
            $this->staffChoiceList = $result;
        }
        return $this->staffChoiceList;
    }

    /**
     * isStudent
     * @param Person $person
     * @return bool
     * @throws \Exception
     */
    public function isStudent(Person $person): bool
    {
        $result = $this->getRepository(StudentEnrolment::class)->findOneBy(['person' => $person, 'academicYear' => AcademicYearHelper::getCurrentAcademicYear()]);
        return $result instanceof StudentEnrolment;
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
     * isParent
     * @param Person|null $person
     * @return bool
     */
    public function isParent(?Person $person = null): bool
    {
        if (null === $person)
            $person = $this->getEntity();

        return $person->isParent();
    }

    /**
     * groupedChoiceList
     */
    public function groupedChoiceList(): array
    {
        $people = $this->getRepository(RollGroup::class)->findCurrentStudentsAsArray();
        $people = array_merge($this->getRepository()->findCurrentStaffAsArray(),$people);
        $people = array_merge($this->getRepository(FamilyMemberAdult::class)->findCurrentParentsAsArray(),$people);

        uasort($people, function($a, $b) {
            return $a['data'] < $b['data'] ? -1 : 1;
        });

        return array_values($people);
    }

    /**
     * isHouseInUse
     * @param House $house
     * @return bool
     */
    public function isHouseInUse(House $house): bool
    {
        return $this->getRepository()->countPeopleInHouse($house) > 0;
    }

    /**
     * findByRole
     * @param string $role
     * @param RoleHierarchyInterface $hierarchy
     * @return array
     */
    public function findByRole(string $role, RoleHierarchyInterface $hierarchy): array
    {
        $people = $this->getRepository()->findBy([],['surname' => 'ASC', 'firstName' => 'ASC']);
        $found = [];
        $reachable = $hierarchy->getReachableRoleNames([$role]);

        foreach($people as $person) {
            foreach($person->getSecurityRoles() as $role) {
                if (in_array($role, $reachable)) {
                    $found[] = $person;
                    continue;
                }
            }
        }
        return $found;
    }

    /**
     * getPaginationContent
     * @return array
     */
    public function getPaginationContent(): array
    {
        return $this->getRepository()->getPaginationContent();
    }

    /**
     * @var array
     */
    private $phoneList;

    /**
     * isPhoneInPeople
     * @param Phone $phone
     * @return bool
     */
    public function isPhoneInPeople(Phone $phone): bool
    {
        if (is_null($this->phoneList)) {
            $this->phoneList = [];
            foreach($this->getRepository()->findPhoneList() as $item) {
                $this->phoneList[$item['id']] = $item['id'];
            }
        }
        if (key_exists($phone->getId(), $this->phoneList)) {
            return true;
        }
        return false;
    }
}
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

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\School\Entity\House;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function getPeoplePaginationContent(): array
    {
        return $this->getRepository()->getPeoplePaginationContent();
    }

    /**
     * getEnrolmentListByClass
     *
     * 4/09/2020 12:01
     * @param CourseClass $class
     * @return array
     */
    public function getEnrolmentListByClass(CourseClass $class): array
    {
        $result = $this->getRepository()->getStudentsByYearGroupQuery($class->getCourse()->getYearGroups())
            ->select(['p','s','pd','c','se','rg','yg'])
            ->orderBy('rg.name', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
        return array_merge($result, $this->getRepository()->getStaffQuery()
            ->getQuery()
            ->getResult());
    }

    /**
     * loadDemonstrationData
     *
     * 9/09/2020 08:09
     * @param array $content
     * @param LoggerInterface $logger
     */
    public function loadDemonstrationData(array $content, LoggerInterface $logger, ValidatorInterface $validator)
    {
        $entities = new ArrayCollection();
        $valid = 0;
        $invalid = 0;
        foreach ($content as $item) {
            $person = new Person();
            $person->setContact(new Contact($person))
                ->setPersonalDocumentation(new PersonalDocumentation($person))
                ->setSecurityUser(new SecurityUser($person))
            ;
            if (key_exists('staff', $item)) $person->setStaff(new Staff($person));
            if (key_exists('student', $item)) $person->setStudent(new Student($person));
            if (key_exists('careGiver', $item)) $person->setCareGiver(new CareGiver($person));

            foreach ($item as $name => $value) {
                $method = 'set' . ucfirst($name);
                switch ($name) {
                    case 'staff':
                        foreach ($value as $q=>$w) {
                            $method = 'set' . ucfirst($q);
                            if ($q === 'house') {
                                if (!$entities->containsKey($w['entityName'])) $entities->set($w['entityName'], new ArrayCollection());
                                if (!$entities->get($w['entityName'])->containsKey($w['value'])) {
                                    $entities->get($w['entityName'])->set($w['value'], ProviderFactory::getRepository(House::class)
                                        ->findOneBy(['abbreviation' => $w['value']]));
                                }
                                $person->getStaff()->setHouse($entities->get($w['entityName'])->get($w['value']));
                            } else {
                                $person->getStaff()->$method($w);
                            }
                        }
                        break;
                    case 'student':
                        foreach ($value as $q=>$w) {
                            $method = 'set' . ucfirst($q);
                            if ($q === 'house') {
                                if (!$entities->containsKey($w['entityName'])) $entities->set($w['entityName'], new ArrayCollection());
                                if (!$entities->get($w['entityName'])->containsKey($w['value'])) {
                                    $entities->get($w['entityName'])->set($w['value'], ProviderFactory::getRepository(House::class)
                                        ->findOneBy(['abbreviation' => $w['value']]));
                                }
                                $person->getStudent()->setHouse($entities->get($w['entityName'])->get($w['value']));
                            } else {
                                $person->getStudent()->$method($w);
                            }
                        }
                        break;
                    case 'contact':
                        foreach ($value as $q=>$w) {
                            $method = 'set' . ucfirst($q);
                            $person->getContact()->$method($w);
                        }
                        break;
                    case 'careGiver':
                        foreach ($value as $q=>$w) {
                            $method = 'set' . ucfirst($q);
                            $person->getCareGiver()->$method($w);
                        }
                        break;
                    case 'securityUser':
                        foreach ($value as $q=>$w) {
                            $method = 'set' . ucfirst($q);
                            $person->getSecurityUser()->$method($w)
                                ->setPassword('$argon2id$v=19$m=16,t=2,p=1$ZU9sWExQTGF1ZkpBOVpNZw$zqdlFyTbVW2E4InbOfvyOg');
                        }
                        break;
                    case 'personalDocumentation':
                        foreach ($value as $q=>$w) {
                            if ($q === 'dob') {
                                try {
                                    $w = new \DateTimeImmutable($w['datetimeimmutable']);
                                } catch (\Exception $e) {
                                    $w = null;
                                }
                            }
                            $method = 'set' . ucfirst($q);
                            $person->getPersonalDocumentation()->$method($w);
                        }
                        break;
                    default:
                        $person->$method($value);
                }
            }
            $validatorList = $validator->validate($person);
            if (count($validatorList) === 0) {
                ProviderFactory::create(Person::class)->persistFlush($person, false);
                if (!$this->getMessageManager()->isStatusSuccess()) {
                    $this->getLogger()->error('Something when wrong with persist:' . $this->getMessageManager()->getLastMessageTranslated());
                    $invalid++;
                } else {
                    $valid++;
                }
            } else {
                $this->getLogger()->warning(sprintf('An entity failed validation for %s', Person::class), [$item, $person, $validatorList->__toString()]);
                $invalid++;
            }

            if (($valid + $invalid) % 50 === 0) {
                $this->flush();
                $logger->notice(sprintf('50 (to %s) records pushed to the database for %s from %s', strval($valid), Person::class, strval(count($content))));
                ini_set('max_execution_time', 60);
            }
        }
        $this->flush();
        return $valid;
    }

    /**
     * getIndividualEnrolmentPaginationContent
     *
     * 10/09/2020 14:07
     * @return array
     */
    public function getIndividualEnrolmentPaginationContent(): array
    {
        $result = $this->getRepository()->findStaffAndStudents(['Full','Expected','Left']);
        foreach ($this->getRepository(Staff::class)->mergeStaffIndividualEnrolmentPagination() as $id=>$item) {
            if (key_exists($id, $result)) {
                $result[$id] = array_merge($item,$result[$id]);
                if ($item['status'] === 'Left' || ($item['dateEnd'] !== null && $item['dateEnd']->format('Y-m-d') < date('Y-m-d'))) $result[$id]['role'] .= ' - Left';
            }
        }
        foreach ($this->getRepository(Student::class)->mergeStudentIndividualEnrolmentPagination() as $id=>$item) {
            if (key_exists($id, $result)) {
                $result[$id] = array_merge($item,$result[$id]);
                if ($item['status'] === 'Left' || ($item['dateEnd'] !== null && $item['dateEnd']->format('Y-m-d') < date('Y-m-d'))) $result[$id]['role'] .= ' - Left';
            }
        }

        uasort($result, function($a,$b) {
            return $a['name'] > $b['name'] ? 1 : -1;
        });

        return array_values($result);
    }
}

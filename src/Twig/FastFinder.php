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
 * Date: 29/07/2019
 * Time: 15:36
 */
namespace App\Twig;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\Enrolment\Entity\StudentRollGroup;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Util\ActionVoterSubject;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use App\Util\CacheHelper;
use App\Util\TranslationHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class FastFinder
 * @package App\Twig
 */
class FastFinder implements ContentInterface
{
    use ContentTrait;

    /**
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * execute
     * @throws \Exception
     */
    public function execute(): void
    {
        if (!$this->hasSession())
            return;

        if (!SecurityHelper::isGranted('IS_AUTHENTICATED_FULLY'))
            return;

        $plannerView = new ActionVoterSubject('planner_view');
        $staffView = new ActionVoterSubject('staff_view');
        $studentView = new ActionVoterSubject('student_view');
        SecurityHelper::isGranted('ROLE_ACTION', $plannerView);
        SecurityHelper::isGranted('ROLE_ACTION', $studentView);
        SecurityHelper::isGranted('ROLE_ACTION', $staffView);
        $person = SecurityHelper::getCurrentUser()->getPerson();;
        $this->addAttribute('roleCategory', $person->getHumanisedRole());

        $this->addAttribute('trans_fastFind', $this->translate('Fast Finder', [], 'messages'));
        $this->addAttribute('trans_fastFindActions', $this->translate('Actions', [], 'messages')
            .($plannerView->isActionAccessible() && $plannerView->getHighestGroupedAction() !== 'viewMyChildrenClasses' ? ', ' . $this->translate('Classes', [], 'messages') : '')
            .($studentView->isActionAccessible() ? ', '.$this->translate('Students', [], 'messages') : '')
            .($staffView->isActionAccessible() ? ', '.$this->translate('Staff', [], 'messages') : ''));
        $this->addAttribute('trans_enrolmentCount', $this->getAttribute('roleCategory') === 'Staff' ? $this->translate('Total Student Enrolment:', [], 'messages') . ' ' .ProviderFactory::getRepository(StudentRollGroup::class)->getStudentEnrolmentCount($this->getSession()->get('AcademicYearID')) : '');
        $this->addAttribute('themeName', $this->getSession()->get('theme'));
        $this->addAttribute('trans_placeholder', $this->translate('Start typing a name...', [], 'messages'));
        $this->addAttribute('trans_close', $this->translate('Close', [], 'messages'));

        $actions = $this->getFastFinderActions();

        $classes = $this->accessibleClasses($plannerView);

        $staff = $this->accessibleStaff($staffView);
        $students = $this->accessibleStudents($studentView);
        $fastFindChoices = [];
        $fastFindChoices[] = ['title' => $this->translate('Actions'), 'suggestions' => $actions, 'prefix' => $this->translate('Action')];
        $fastFindChoices[] = ['title' => $this->translate('Classes'), 'suggestions' => $classes, 'prefix' => $this->translate('Class')];
        $fastFindChoices[] = ['title' => $this->translate('Staff'), 'suggestions' => $staff, 'prefix' => $this->translate('Staff')];
        $fastFindChoices[] = ['title' => $this->translate('Students'), 'suggestions' => $students, 'prefix' => $this->translate('Student')];
        $this->addAttribute('fastFindChoices', $fastFindChoices);
    }

    /**
     * getFastFinderActions
     *
     * @param int $roleID
     * @return mixed
     * @throws \Exception
     */
    public function getFastFinderActions(): array
    {
        CacheHelper::setSession($this->getSession());
        if (CacheHelper::isStale('fastFinderActions'))
        {
            // Get the accessible actions for the current user
            $actions = ProviderFactory::create(Action::class)->findFastFinderActions();
            CacheHelper::setCacheValue('fastFinderActions', $actions, 10);
        } else {
            $actions = CacheHelper::getCacheValue('fastFinderActions') ?: [];
        }
        return $actions;
    }

    /**
     * accessibleClasses
     * @throws \Exception
     */
    public function accessibleClasses(ActionVoterSubject $plannerView)
    {
        $classes = [];
        if (CacheHelper::isStale('fastFinderClasses')) {
            $classIsAccessible = false;
            if ($plannerView->isActionAccessible() && $plannerView->getHighestGroupedAction() !== 'viewMyChildrenClasses') {
                $classIsAccessible = true;
            }
            // CLASSES
            if ($classIsAccessible) {
                if ($plannerView->getHighestGroupedAction() === 'viewEditAllClasses' || $plannerView->getHighestGroupedAction() === 'viewAllEditMyClasses') {
                    $classes = ProviderFactory::getRepository(CourseClass::class)->findAccessibleClasses($this->getSession()->get('academicYear'), '');
                } else {
                    $classes = ProviderFactory::getRepository(CourseClassStudent::class)->findAccessibleClasses($this->getSession()->get('academicYear'), $this->getToken()->getToken()->getUser()->getPerson(), '');
                }
            }
            CacheHelper::setCacheValue('fastFinderClasses', $classes ?: []);
        } else {
            $classes = CacheHelper::getCacheValue('fastFinderClasses');
        }
        return $classes;
    }

    /**
     * accessibleStaff
     * @return mixed
     * @throws \Exception
     */
    public function accessibleStaff(ActionVoterSubject $staffView)
    {
        $staff = [];
        if (CacheHelper::isStale('fastFinderStaff'))
        {
            // STAFF
            if ($staffView->isActionAccessible()) {
                $staff = ProviderFactory::getRepository(Person::class)->findStaffForFastFinder('');
                CacheHelper::setCacheValue('fastFinderStaff', $staff);
            }
        } else {
            $staff = CacheHelper::getCacheValue('fastFinderStaff') ?: [];
        }
        return $staff;
    }

    /**
     * accessibleStudents
     * @return mixed
     * @throws \Exception
     */
    public function accessibleStudents(ActionVoterSubject $studentView)
    {
        // STUDENTS
        $students = [];
        if (CacheHelper::isStale('fastFinderStudents')) {
            if ($studentView->isActionAccessible()) {
                if ($studentView->getHighestGroupedAction() === 'myChildren') {
                    $students = ProviderFactory::getRepository(FamilyMemberCareGiver::class)->findStudentsOfParentFastFinder($this->getToken()->getToken()->getUser()->getPerson(), '', $this->getSession()->get('academicYear'));
                } elseif ($studentView->getHighestGroupedAction() == 'View Student Profile_my') {
                    $person = SecurityHelper::getCurrentUser()->getPerson();
                    $students = [];
                    $student = [];
                    $student['id'] = 'Stu-' . $person->getId();
                    $student['text'] = ' - ' . $person->getSurname() . ', ' . $person->getPreferredName();
                    foreach($person->getStudentEnrolments() AS $se) {
                        if ($se->getSchoolYear()->getId() === $this->getSession()->get('academicYear')->getId()) {
                            $rollGroup = $se->getRollGroup();
                            break;
                        }
                    }
                    $student['text'] .= ' (' . ($rollGroup ? $rollGroup->getName() : '') . ', ' . $person->getStudentID() . ')';
                    $student['search'] = $person->getUsername() . ' ' . $person->getFirstName() . ' ' . $person->getEmail();
                    $students[] = $student;
                } else {
                    $students = ProviderFactory::getRepository(Person::class)->findStudentsForFastFinder(AcademicYearHelper::getCurrentAcademicYear(), '');
                }
            }
            CacheHelper::setCacheValue('fastFinderStudents', $students);
        } else {
            $students = CacheHelper::getCacheValue('fastFinderStudents') ?: [];
        }
        return $students;
    }

    /**
     * translate
     * @param string $key
     * @param array|null $params
     * @param string|null $domain
     * @return string
     */
    private function translate(string $key, ?array $params = [], ?string $domain = 'messages'): string
    {
        return TranslationHelper::translate($key, $params, $domain);
    }

    /**
     * Cache translated FastFinder actions to allow searching actions with the current locale
     * @throws \Exception
     */
    public static function cacheFastFinderActions()
    {
        if (CacheHelper::isStale('fastFinderActions')) {
            // Get the accessible actions for the current user
            $result = ProviderFactory::create(Module::class)->buildFastFinder(false);
            $actions = [];
            if (count($result) > 0) {
                // Translate the action names
                foreach ($result as $row) {
                    $row['name'] = TranslationHelper::translate($row['name'], [], $row['name']);
                    $actions[] = $row;
                }
            }
            // Cache the resulting set of translated actions
            CacheHelper::setCacheValue('fastFinderActions', $actions);
        } else
            $actions = CacheHelper::getCacheValue('fastFinderActions');
        return $actions;
    }
}
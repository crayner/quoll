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

use App\Modules\Curriculum\Entity\Course;
use App\Modules\Department\Twig\MyClasses;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Staff\Entity\Staff;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Twig\SidebarContent;
use App\Util\CacheHelper;
use App\Util\TranslationHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CourseClassProvider
 * @package App\Modules\Enrolment\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = CourseClass::class;

    /**
     * getMyClasses
     * @param Person|SecurityUser|string|null $person
     * @param SidebarContent|null $sidebar
     * @return array
     */
    public function getMyClasses($person, ?SidebarContent $sidebar = null): array
    {
        $result = null;
        if ($person instanceof SecurityUser)
            $result = $this->getRepository()->findByAcademicYearPerson($person->getPerson());
        elseif ($person instanceof Person)
            $result = $this->getRepository()->findByAcademicYearPerson($person);

        if (count($result) > 0 && null !== $sidebar) {
            $myClasses = new MyClasses($result);
            $sidebar->addContent($myClasses);
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
        $active = $this->getRepository(CourseClass::class)->countStudentParticipants(['Full']);
        $expected = $this->getRepository(CourseClass::class)->countStudentParticipants(['Expected']);
        $total = $this->getRepository(CourseClass::class)->countStudentParticipants([]);
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
     * 15/09/2020 15:12
     * @param Person $person
     * @return array
     */
    public function getPreferredIndividualClassChoices(Person $person): array
    {
        if ($person->isStudent()) {
            $x = [];
            foreach ($this->getRepository()->findEnrolableClasses($person) as $class) {
                $x[$class->getId()] = TranslationHelper::translate('enrolable_classes',
                    [
                        'course' => $class->getCourse()->getAbbreviation(),
                        'class' => $class->getAbbreviation(),
                        'tutor' => $class->getTutors()->first() ? $class->getTutors()->first()->getStaff()->getFullName('Initial') : TranslationHelper::translate('No Teacher Assigned', [], 'Enrolment'),
                        'count' => $class->getStudents()->count(),
                    ], 'Enrolment');
            }
            $result = array_flip($x);
        } else {
            $result = [];
        }
        return $result;
    }

    /**
     * getIndividualClassChoices
     *
     * 23/09/2020 09:20
     * @return array
     */
    public function getIndividualClassChoices(): array
    {
        if (($result = CacheHelper::getCacheValue('course_class_choices', 30)) !== null) return $result;
        $result = [];
        foreach ($this->getRepository()->findClassesByCurrentAcademicYear() as $class) {
            $result[$class->getId()] = TranslationHelper::translate(
                'enrolable_classes',
                [
                    'course' => $class->getCourse()->getAbbreviation(),
                    'class' => $class->getAbbreviation(),
                    'tutor' => $class->getTutors()->first() ? $class->getTutors()->first()->getStaff()->getFullName('Initial') : TranslationHelper::translate('No Teacher Assigned', [], 'Enrolment'),
                    'count' => $class->getStudents()->count(),
                ],
                'Enrolment');
        }
        $result = array_flip($result);

        CacheHelper::setCacheValue('course_class_choices', $result);
        return $result;
    }

    /**
     * loader
     *
     * 16/09/2020 13:58
     * @param array $content
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     * @return int
     * @throws \Exception
     */
    public function loader(array $content, LoggerInterface $logger, ValidatorInterface $validator): int
    {
        $courses = [];
        $tutors = [];
        $valid = 0;
        $classes = [];
        foreach ($content as $w) {
            $courses[$w['course']] = key_exists($w['course'], $courses) ? $courses[$w['course']] : ProviderFactory::getRepository(Course::class)->findOneBy(['abbreviation' => $w['course']]);
            if ($courses[$w['course']] === null) {
                $logger->error(sprintf('A course was not found for course abbreviation of "%s"', $w['course']));
                continue;
            }
            $class = new CourseClass($courses[$w['course']]);
            $class->setName($w['name'])
                ->setAbbreviation($w['abbreviation'])
                ->setCourse($courses[$w['course']])
                ->setReportable($w['reportable'])
                ->setAttendance($w['attendance']);
            if (key_exists('tutors', $w)) {
                foreach ($w['tutors'] as $username) {
                    $tutors[$username] = key_exists($username, $tutors) ? $tutors[$username] : ProviderFactory::getRepository(Staff::class)->findOneByUsername($username);
                    if ($tutors[$username] === null) {
                        $logger->error(sprintf('A person was not found for username "%s"', $username));
                    } else {
                        $tutor = new CourseClassTutor($class);
                        if (key_exists($class->getFullName(), $classes)) {
                            $tutor->setSortOrder(++$classes[$class->getFullName()]);
                        } else {
                            $classes[$class->getFullName()] = 1;
                            $tutor->setSortOrder(1);
                        }
                        $tutor->setStaff($tutors[$username]);
                        $class->addTutor($tutor);
                    }
                }
            }
            ProviderFactory::create(CourseClass::class)->persistFlush($class, false);
            if (++$valid % 50 === 0) {
                $this->getMessageManager()->resetStatus();
                ProviderFactory::create(CourseClass::class)->flush();
                if (!$this->getMessageManager()->isStatusSuccess()) {
                    foreach ($this->getMessageManager()->getMessageArray() as $message) {
                        $logger->error($message['message']);
                    }
                    return $valid;
                }
                $logger->notice(sprintf('50 (to %s) records pushed to the database for %s from %s', $valid, $this->getEntityName(), strval(count($content))));
                ini_set('max_execution_time', 10);
            }
        }
        $this->getMessageManager()->resetStatus();
        ProviderFactory::create(CourseClass::class)->flush();
        if (!$this->getMessageManager()->isStatusSuccess()) {
            foreach ($this->getMessageManager()->getMessageArray() as $message) {
                $logger->error($message['message']);
            }
            return $valid;
        }
        return $valid;
    }

    /**
     * findCourseClassParticipationPagination
     *
     * 20/09/2020 09:32
     * @param CourseClass $class
     * @return array
     */
    public function findCourseClassParticipationPagination(CourseClass $class): array
    {
        $result = [];
        foreach ($class->getTutors() as $tutor) {
            $result[] = [
                'role' => $tutor->getStaff()->getType(),
                'name' => $tutor->getStaff()->getFullNameReversed(),
                'email' => $tutor->getStaff()->getPerson()->getContact()->getEmail(),
                'reportable' => '-',
                'id' => $tutor->getId(),
                'course_class_id' => $class->getId(),
                'course_id' => $class->getCourse()->getId(),
                'person_id' => $tutor->getStaff()->getPerson()->getId(),
                'status' => $tutor->getStaff()->getPerson()->getStatus(),
            ];
        }
        return array_merge($result, $this->getRepository(CourseClassStudent::class)->findCourseClassParticipationStudent($class));
    }
}

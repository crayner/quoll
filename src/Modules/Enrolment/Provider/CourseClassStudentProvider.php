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
 * Time: 14:58
 */
namespace App\Modules\Enrolment\Provider;

use App\Modules\Curriculum\Entity\Course;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Student\Entity\Student;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

/**
 * Class CourseClassPersonProvider
 * @package App\Modules\Enrolment\Provider
 */
class CourseClassStudentProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected string $entityName = CourseClassStudent::class;

    /**
     * loader
     *
     * 3/09/2020 13:40
     * @param array $data
     * @param LoggerInterface $logger
     * @return int
     */
    public function loader(array $data, LoggerInterface $logger): int
    {
        $count = 0;
        $students = [];
        $classes = [];
        $courses[] = [];
        $validator = Validation::createValidator();
        $flushCount = 0;
        foreach ($data as $q=>$item) {
            $ccp = new CourseClassStudent();
            if (!key_exists('username', $item)) continue;
            if (!key_exists('class', $item)) continue;

            if (!key_exists($item['username'], $students)) {
                $students[$item['username']] = ProviderFactory::getRepository(Student::class)->findOneByUsername($item['username']);
            }
            $student = key_exists($item['username'], $students) && $students[$item['username']] ? $students[$item['username']] : null;

            if (!key_exists($item['class']['course'], $courses))
                $courses[$item['class']['course']] = ProviderFactory::getRepository(Course::class)->findOneBy(['abbreviation' => $item['class']['course']]);

            $course = $courses[$item['class']['course']];
            $class = null;
            $key = $item['class']['course'] . $item['class']['name'];
            if ($courses[$item['class']['course']]) {
                if (!key_exists($key, $classes)) {
                    $classes[$key] = ProviderFactory::getRepository(CourseClass::class)->findOneBy(['course' => $course, 'name' => $item['class']['name']]);
                }
            } else {
                $classes[$key] = null;
            }
            $class = $classes[$key];

            $ccp->setReportable($item['reportable'])
                ->setStudent($student)
                ->setCourseClass($class)
            ;

            $errors = $validator->validate($ccp);
            if ($student === null) {
                $logger->error(sprintf('A student was not found for username "%s"',$item['username']));
            } else if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $logger->error($error->getMessage());
                }
            } else {
                $this->getMessageManager()->resetStatus();
                ProviderFactory::create(CourseClassStudent::class)->persistFlush($ccp, false);
                if (!$this->getMessageManager()->isStatusSuccess()) {
                    foreach ($this->getMessageManager()->getMessageArray() as $message) {
                        $logger->error($message['message']);
                    }
                    return $flushCount;
                }
                if (++$count % 50 === 0) {
                    $this->getMessageManager()->resetStatus();
                    ProviderFactory::create(CourseClassStudent::class)->flush();
                    if (!$this->getMessageManager()->isStatusSuccess()) {
                        foreach ($this->getMessageManager()->getMessageArray() as $message) {
                            $logger->error($message['message']);
                        }
                        return $flushCount;
                    }
                    $flushCount = $count;
                    $logger->notice(sprintf('50 (to %s) records pushed to the database for %s from %s', $flushCount, $this->getEntityName(), strval(count($data))));
                    ini_set('max_execution_time', 10);
                }
            }
        }
        $this->getMessageManager()->resetStatus();
        ProviderFactory::create(CourseClassStudent::class)->flush();
        if (!$this->getMessageManager()->isStatusSuccess()) {
            foreach ($this->getMessageManager()->getMessageArray() as $message) {
                $logger->error($message['message']);
            }
            return $flushCount;
        }
        return $count;
    }

    /**
     * canDelete
     *
     * 4/09/2020 09:33
     * @return bool
     * @todo Build reasons to not remove enrolment
     */
    public function canDelete(): bool
    {
        return true;
    }

    /**
     * getIndividualClassEnrolmentContent
     *
     * 18/09/2020 12:26
     * @param Person $person
     * @return array
     */
    public function getIndividualClassEnrolmentContent(Person $person): array
    {
        if ($person->isStudent())
            return $this->getRepository()->findIndividualClassEnrolmentContent($person->getStudent());
        return $this->getRepository(CourseClassTutor::class)->findIndividualClassEnrolmentContent($person->getStaff());
    }
}

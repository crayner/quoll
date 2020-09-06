<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 4/09/2020
 * Time: 13:47
 */
namespace App\Modules\Enrolment\Manager;

use App\Manager\StatusManager;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CourseClassPersonManager
 * @package App\Modules\Enrolment\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassPersonManager
{
    /**
     * @var StatusManager
     */
    private StatusManager $statusManager;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * CourseClassPersonManager constructor.
     * @param StatusManager $statusManager
     */
    public function __construct(StatusManager $statusManager, ValidatorInterface $validator)
    {
        $this->statusManager = $statusManager;
        $this->validator = $validator;
    }

    /**
     * handleRequest
     *
     * 4/09/2020 13:48
     * @param array $content
     * @param CourseClass $class
     */
    public function handleRequest(array $content, CourseClass $class)
    {
        if (empty($content['role'])) {
            $this->getStatusManager()->error('The role must not be blank.',[],'Enrolment');
            return;
        }
        if (empty($content['people'])) {
            $this->getStatusManager()->error('No participants were selected.',[],'Enrolment');
            return;
        }

        $valid = 0;
        foreach ($content['people'] as $id) {
            $person = ProviderFactory::getRepository(Person::class)->find($id);
            $ccp = ProviderFactory::getRepository(CourseClassPerson::class)->findOneBy(['courseClass' => $class, 'person' => $person]) ?: new CourseClassPerson($class);
            $ccp->setPerson($person)
                ->setCourseClass($class)
                ->setReportable($class->isReportable())
                ->setRole($content['role']);
            $errors = $this->validator->validate($ccp);
            if (count($errors) === 0) {
                ProviderFactory::create(CourseClassPerson::class)->persistFlush($ccp, false);
                dump($ccp, $this->getStatusManager());
                $valid++;
            } else {
                foreach($errors as $error) $this->getStatusManager()->error($error->getMessage());
            }
        }
        if ($valid > 0) ProviderFactory::create(CourseClassPerson::class)->flush();
        dump($this->getStatusManager());
    }

    /**
     * @return StatusManager
     */
    public function getStatusManager(): StatusManager
    {
        return $this->statusManager;
    }
}
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
 * Date: 12/05/2020
 * Time: 08:55
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Person;
use App\Modules\Student\Entity\Student;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class FamilyMemberStudentProvider
 * @package App\Modules\People\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FamilyMemberStudentProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = FamilyMemberStudent::class;

    /**
     * getStudentsOfParent
     * @param Person $parent
     * @return array
     * 19/06/2020 09:33
     */
    public function getStudentsOfParent(Person $parent): array
    {
        $result = ProviderFactory::getRepository(FamilyMemberCareGiver::class)->findFamiliesOfParent($parent, true);
        $families = [];
        foreach($result as $q=>$w) {
            if ($w->isChildDataAccess()) {
                $families[] = $w->getFamily();
            }
        }
        $students = [];
        foreach($families as $family) {
            $students = array_merge($students, $family->getStudents()->toArray());
        }

        return $students;
    }

    /**
     * loadDemonstrationData
     *
     * 9/09/2020 10:13
     * @param array $content
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     * @return int
     */
    public function loadDemonstrationData(array $content, LoggerInterface $logger, ValidatorInterface $validator): int
    {
        $valid = 0;
        $invalid = 0;
        $entities = new ArrayCollection();

        foreach ($content as $item) {
            $family = $entities->containsKey($item['family']) ? $entities->get($item['family']) : $this->getRepository(Family::class)->findOneBy(['familySync' => $item['family']]);
            $student = $this->getRepository(Student::class)->findOneByUsername($item['student']);
            $fm = new FamilyMemberStudent($family);
            $fm->setStudent($student);

            $validatorList = $validator->validate($fm);
            if (count($validatorList) === 0) {
                ProviderFactory::create(Person::class)->persistFlush($fm, false);
                if (!$this->getMessageManager()->isStatusSuccess()) {
                    $this->getLogger()->error('Something when wrong with persist:' . $this->getMessageManager()->getLastMessageTranslated());
                    $invalid++;
                } else {
                    $valid++;
                }
            } else {
                $this->getLogger()->warning(sprintf('An entity failed validation for %s', FamilyMemberStudent::class), [$item, $fm, $validatorList->__toString()]);
                $invalid++;
            }

            if (($valid + $invalid) % 50 === 0) {
                $this->flush();
                $logger->notice(sprintf('50 (to %s) records pushed to the database for %s from %s', strval($valid), FamilyMemberStudent::class, strval(count($content))));
                ini_set('max_execution_time', 60);
            }
        }
        $this->flush();
        return $valid;
    }
}
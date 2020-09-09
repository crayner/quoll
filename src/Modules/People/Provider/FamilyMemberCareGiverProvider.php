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

use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Person;
use App\Modules\Student\Entity\Student;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class FamilyMemberAdultProvider
 * @package App\Modules\People\Provider
 */
class FamilyMemberCareGiverProvider extends AbstractProvider
{

    protected $entityName = FamilyMemberCareGiver::class;

    /**
     * saveAdults
     * @param array $adults
     * @param array $data
     * @return array
     */
    public function saveAdults(array $adults, array $data): array
    {
        $sm = $this->getEntityManager()->getConnection()->getSchemaManager();
        $prefix = $this->getEntityManager()->getConnection()->getParams()['driverOptions']['prefix'];

        try {
            $table = $sm->listTableDetails($prefix. 'FamilyMember');
            $indexes = $sm->listTableIndexes($prefix. 'FamilyMember');
            if (key_exists('family_contact', $indexes) || key_exists('family_contact', $indexes)) {
                $index = $table->getIndex('family_contact');
                $sm->dropIndex($index, $table);
            } else {
                $index = new Index('family_contact', ['family','contactPriority'], true);
            }

            foreach ($adults as $adult)
                $this->getEntityManager()->persist($adult);
            $this->getEntityManager()->flush();

            $sm->createIndex($index, $table);
            $data = ErrorMessageHelper::getSuccessMessage($data, true);
        } catch (SchemaException | \Exception $e) {
            $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            $data['errors'][] = ['class' => 'error', 'message' => $e->getMessage()];
        }

        return $data;
    }

    /**
     * getStudentsOfParent
     * @param Person|null $parent
     * @return array
     * 19/06/2020 08:49
     */
    public function getStudentsOfParent(Person $parent = null): array
    {
        return $parent ? $this->getRepository()->findStudentsOfParent($parent) : [];
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
            $careGiver = $this->getRepository(CareGiver::class)->findOneByUsername($item['careGiver']);
            $fm = new FamilyMemberCareGiver($family);
            $fm->setCareGiver($careGiver);
            foreach ($item as $name=>$value) {
                if (in_array($name, ['careGiver','family'])) continue;
                $method = 'set' . ucfirst($name);
                $fm->$method($value);
            }

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
                $this->getLogger()->warning(sprintf('An entity failed validation for %s', FamilyMemberCareGiver::class), [$item, $fm, $validatorList->__toString()]);
                $invalid++;
            }

            if (($valid + $invalid) % 50 === 0) {
                $this->flush();
                $logger->notice(sprintf('50 (to %s) records pushed to the database for %s from %s', strval($valid), FamilyMemberCareGiver::class, strval(count($content))));
                ini_set('max_execution_time', 60);
            }
        }
        $this->flush();
        return $valid;
    }
}

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

use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\Person;
use App\Provider\AbstractProvider;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;

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

}
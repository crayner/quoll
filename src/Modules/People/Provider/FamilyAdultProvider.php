<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 6/12/2019
 * Time: 14:52
 */

namespace App\Modules\People\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;
use App\Modules\People\Entity\FamilyAdult;

/**
 * Class FamilyAdultProvider
 * @package App\Modules\People\Provider
 */
class FamilyAdultProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = FamilyAdult::class;

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
            $table = $sm->listTableDetails($prefix. 'FamilyAdult');
            $indexes = $sm->listTableIndexes($prefix. 'FamilyAdult');
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
 //           $data['errors'][] = ['class' => 'error', 'message' => $e->getMessage()];
        }

        return $data;
    }
}
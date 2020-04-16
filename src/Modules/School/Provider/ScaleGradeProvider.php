<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 10/01/2020
 * Time: 07:59
 */

namespace App\Modules\School\Provider;

use App\Entity\MarkbookTarget;
use App\Entity\RubricColumn;
use App\Manager\Traits\EntityTrait;
use App\Modules\School\Entity\ScaleGrade;
use App\Provider\EntityProviderInterface;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * Class ScaleGradeProvider
 * @package App\Modules\School\Provider
 */
class ScaleGradeProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = ScaleGrade::class;

    /**
     * saveGrades
     * @param array $grades
     * @param array $data
     * @return array
     */
    public function saveGrades(array $grades, array $data): array
    {
        $sm = $this->getEntityManager()->getConnection()->getSchemaManager();
        $prefix = $this->getEntityManager()->getConnection()->getParams()['driverOptions']['prefix'];

        try {
            $table = $sm->listTableDetails($prefix. 'ScaleGrade');
            $indexes = $sm->listTableIndexes($prefix. 'ScaleGrade');

            if (isset($indexes['scalesequence'])) {
                $index = $table->getIndex('scalesequence');
                $sm->dropIndex($index, $table);
            } else {
                $index = new Index('scaleSequence', ['sequenceNumber','scale'], true);
            }

            foreach ($grades as $grade)
                $this->getEntityManager()->persist($grade);
            $this->getEntityManager()->flush();

            $sm->createIndex($index, $table);
        } catch (SchemaException | \Exception $e) {
            $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            $data['errors'][] = ['class' => 'error', 'message' => $e->getMessage()];
        }

        return $data;
    }

    /**
     * canDelete
     * @param ScaleGrade $grade
     * @return bool
     */
    public function canDelete(ScaleGrade $grade): bool
    {
        if ($this->getRepository(MarkbookTarget::class)->countGradeUse($grade) > 0)
            return false;
        if ($this->getRepository(RubricColumn::class)->countGradeUse($grade) > 0)
            return false;

        return true;
    }
}
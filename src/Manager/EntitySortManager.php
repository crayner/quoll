<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 29/05/2020
 * Time: 09:26
 */
namespace App\Manager;

use App\Provider\AbstractProvider;
use App\Provider\EntityProviderInterface;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * Class EntitySortManager
 * @package App\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class EntitySortManager
{
    /**
     * @var AbstractEntity
     */
    private $source;

    /**
     * @var AbstractEntity
     */
    private $target;

    /**
     * @var AbstractPaginationManager
     */
    private $pagination;

    /**
     * @var array
     */
    private $details = [];

    /**
     * @var array
     */
    private $content = [];

    /**
     * ScaleGradeSort constructor.
     *
     * @param AbstractEntity $source
     * @param AbstractEntity $target
     * @param AbstractPaginationManager $pagination
     * @param string $sortField
     * @param string $indexName
     */
    public function __construct(AbstractEntity $source, AbstractEntity $target, AbstractPaginationManager $pagination, string $sortField, string $indexName, array $indexColumns = [])
    {
        $this->source = $source;
        $this->target = $target;
        $this->pagination = $pagination;
        $this->details = [];

        if ($indexColumns === []) {
            $indexColumns = [$indexName];
        }

        $provider = ProviderFactory::create(get_class($source));

        $content = $provider->getRepository()->findBy([],[$sortField => 'ASC']);

        $key = 1;
        $result = [];
        $method = 'set' . ucfirst($sortField);
        foreach($content as $q => $item)
        {
            if ($item === $source) {
                continue;
            }
            if ($item === $target) {
                $source->$method($key++);
                $result[] = $source;
            }
            $item->$method($key++);
            $result[] = $item;
        }

        $this->details = $this->saveSort($provider, $result, $this->details, basename(get_class($source)), $indexName, $indexColumns);
        $this->content = $result;

    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        if ($this->details['status'] === 'success') {
            $this->details['content'] = $this->pagination->setContent($this->content)->toArray()['content'];
        }
        return $this->details;
    }

    /**
     * Details.
     *
     * @param array $details
     * @return $this
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * setContent
     * @param array $content
     * @return $this
     */
    public function setContent(array $content): EntitySortManager
    {
        $this->content = $content;
        return $this;
    }

    /**
     * saveAdults
     * @param EntityProviderInterface $provider
     * @param array $result
     * @param array $data
     * @param string $tableName
     * @param string $indexName
     * @param array $indexColumns
     * @return array
     */
    public function saveSort(EntityProviderInterface $provider, array $result, array $data, string $tableName, string $indexName, array $indexColumns): array
    {
        $sm = $provider->getEntityManager()->getConnection()->getSchemaManager();
        $prefix = $provider->getEntityManager()->getConnection()->getParams()['driverOptions']['prefix'];

        try {
            $table = $sm->listTableDetails($prefix . $tableName);
            $indexes = $sm->listTableIndexes($prefix . $tableName);
            if (key_exists($indexName, $indexes) || key_exists($indexName, $indexes)) {
                $index = $table->getIndex($indexName);
                $sm->dropIndex($index, $table);
            } else {
                $index = new Index($indexName, $indexColumns, true);
            }

            foreach ($result as $adult)
                $provider->getEntityManager()->persist($adult);
            $provider->getEntityManager()->flush();

            $sm->createIndex($index, $table);
            $data = ErrorMessageHelper::getSuccessMessage($data, true);
        } catch (SchemaException | \Exception $e) {
            $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            $data['errors'][] = ['class' => 'error', 'message' => $e->getMessage()];
        }

        return $data;
    }

}

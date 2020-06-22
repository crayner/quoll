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
 * Date: 29/05/2020
 * Time: 09:26
 */
namespace App\Manager;

use App\Provider\EntityProviderInterface;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;
use Symfony\Component\Intl\Exception\MissingResourceException;

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
     * @var array
     */
    private $findBy = [];

    /**
     * @var string
     */
    private $sortField;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var array
     */
    private $indexColumns;

    /**
     * ScaleGradeSort constructor.
     *
     * @param AbstractEntity $source
     * @param AbstractEntity $target
     * @param AbstractPaginationManager $pagination
     */
    public function execute(
        AbstractEntity $source,
        AbstractEntity $target,
        AbstractPaginationManager $pagination
    ) {
        $this->setSource($source);
        $this->setTarget($target);
        $this->setPagination($pagination);
        $this->details = [];

        $provider = ProviderFactory::create(get_class($this->getSource()));

        $content = $provider->getRepository()->findBy($this->getFindBy(),[$this->getSortField() => 'ASC']);

        if ($source !== $target) {
            $key = 1;
            $result = [];
            $method = 'set' . ucfirst($this->getSortField());
            foreach ($content as $q => $item) {
                if ($item === $this->getSource()) {
                    continue;
                }
                if ($item === $this->getTarget()) {
                    $this->getSource()->$method($key++);
                    $result[] = $this->getSource();
                }
                $item->$method($key++);
                $result[] = $item;
            }
            $this->setDetails($this->saveSort($provider, $result, $this->details, basename(get_class($this->getSource()))));
        } else {
            $this->setDetails(['status' => 'success', 'errors' => ['class' => 'info', 'messages' => TranslationHelper::translate('No change is required.', [], 'messages')]]);
            $result = $content;
        }
        $this->setContent($result);
    }

    /**
     * getDetails
     * @return array
     * 2/06/2020 10:47
     */
    public function getDetails(): array
    {
        if ($this->details['status'] === 'success') {
            $this->details['content'] = $this->getPagination()->setContent($this->content)->toArray()['content'];
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
     * @return array
     */
    public function saveSort(EntityProviderInterface $provider, array $result, array $data, string $tableName): array
    {
        $sm = $provider->getEntityManager()->getConnection()->getSchemaManager();
        $prefix = $provider->getEntityManager()->getConnection()->getParams()['driverOptions']['prefix'];

        try {
            $table = $sm->listTableDetails($prefix . $tableName);
            $indexes = $sm->listTableIndexes($prefix . $tableName);
            if (key_exists($this->getIndexName(), $indexes) || key_exists($this->getIndexName(), $indexes)) {
                $index = $table->getIndex($this->getIndexName());
                $sm->dropIndex($index, $table);
            } else {
                $index = new Index($this->getIndexName(), $this->getIndexColumns(), true);
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

    /**
     * @return array
     */
    public function getFindBy(): array
    {
        return $this->findBy ?: [];
    }

    /**
     * @param array $findBy
     * @return EntitySortManager
     */
    public function setFindBy(array $findBy): EntitySortManager
    {
        $this->findBy = $findBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortField(): string
    {
        if (empty($this->sortField)) {
            throw new MissingResourceException('The sort field is not set');
        }
        return $this->sortField;
    }

    /**
     * @param string $sortField
     * @return EntitySortManager
     */
    public function setSortField(string $sortField): EntitySortManager
    {
        $this->sortField = $sortField;
        return $this;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        if (empty($this->indexName)) {
            throw new MissingResourceException('The index name is not set');
        }
        return $this->indexName;
    }

    /**
     * @param string $indexName
     * @return EntitySortManager
     */
    public function setIndexName(string $indexName): EntitySortManager
    {
        $this->indexName = $indexName;
        return $this;
    }

    /**
     * a simple string[] array
     * @return array|string[]
     */
    public function getIndexColumns(): array
    {
        if (empty($this->indexColumns)) {
            $this->setIndexColumns([$this->getIndexName()]);
        }

        return $this->indexColumns;
    }

    /**
     * @param array|string[] $indexColumns
     * @return EntitySortManager
     */
    public function setIndexColumns(array $indexColumns): EntitySortManager
    {
        $this->indexColumns = $indexColumns;
        
        return $this;
    }

    /**
     * @return AbstractEntity
     */
    public function getSource(): AbstractEntity
    {
        return $this->source;
    }

    /**
     * @param AbstractEntity $source
     * @return EntitySortManager
     */
    public function setSource(AbstractEntity $source): EntitySortManager
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return AbstractEntity
     */
    public function getTarget(): AbstractEntity
    {
        return $this->target;
    }

    /**
     * @param AbstractEntity $target
     * @return EntitySortManager
     */
    public function setTarget(AbstractEntity $target): EntitySortManager
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return AbstractPaginationManager
     */
    public function getPagination(): AbstractPaginationManager
    {
        return $this->pagination;
    }

    /**
     * @param AbstractPaginationManager $pagination
     * @return EntitySortManager
     */
    public function setPagination(AbstractPaginationManager $pagination): EntitySortManager
    {
        $this->pagination = $pagination;
        return $this;
    }
}

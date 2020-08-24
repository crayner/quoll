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
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;
use Exception;
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
    private AbstractEntity $source;

    /**
     * @var AbstractEntity
     */
    private AbstractEntity $target;

    /**
     * @var ?string
     */
    private ?string $tableName = null;

    /**
     * @var ?string
     */
    private ?string $entityName = null;

    /**
     * @var AbstractPaginationManager
     */
    private AbstractPaginationManager $pagination;

    /**
     * @var array
     */
    private array $content = [];

    /**
     * @var array
     */
    private array $findBy = [];

    /**
     * @var string|null
     */
    private ?string $sortField;

    /**
     * @var string|null
     */
    private ?string $indexName;

    /**
     * @var array|null
     */
    private ?array $indexColumns;

    /**
     * @var StatusManager
     */
    private StatusManager $messages;

    /**
     * EntitySortManager constructor.
     * @param StatusManager $messages
     */
    public function __construct(StatusManager $messages)
    {
        $this->messages = $messages;
    }

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
        $this->setSource($source)
            ->setTarget($target)
            ->setPagination($pagination);

        $provider = ProviderFactory::create($this->getEntityName());

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
            $this->saveSort($provider, $result);
        } else {
            $this->getMessages()->info(StatusManager::NOTHING_TO_DO);
            $result = $content;
        }
        $this->setContent($result);
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
     * saveSort
     *
     * 16/08/2020 16:42
     * @param EntityProviderInterface $provider
     * @param array $result
     */
    public function saveSort(EntityProviderInterface $provider, array $result)
    {
        $sm = $provider->getEntityManager()->getConnection()->getSchemaManager();
        $prefix = $provider->getEntityManager()->getConnection()->getParams()['driverOptions']['prefix'];

        try {
            $table = $sm->listTableDetails($prefix . $this->getTableName());
            $indexes = $sm->listTableIndexes($prefix . $this->getTableName());
            if (key_exists($this->getIndexName(), $indexes) || key_exists($this->getIndexName(), $indexes)) {
                $index = $table->getIndex($this->getIndexName());
                $sm->dropIndex($index, $table);
            } else {
                $index = new Index($this->getIndexName(), $this->getIndexColumns(), true);
            }

            foreach ($result as $item)
                $provider->persistFlush($item, false);
            $provider->flush();

            $sm->createIndex($index, $table);
            $this->getMessages()->success();
        } catch (SchemaException | Exception $e) {
            if ($_SERVER['APP_ENV'] === 'dev') $this->getMessages()->error($e->getMessage(), [], false);
            $this->getMessages()->info('When using a single table discriminator search, the correct table name must be set manually to ensure the single table is correctly addressed in the save routine.  Use setTableName() using the base name without prefix. The given tableName = ' . $this->getTableName(), [], false);
            $this->getMessages()->error(StatusManager::DATABASE_ERROR);
        }
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

    /**
     * refreshSequences
     * 18/07/2020 09:31
     */
    public function refreshSequences()
    {
        $provider = ProviderFactory::create(get_class($this->getSource()));

        $content = $provider->getRepository()->findBy($this->getFindBy(),[$this->getSortField() => 'ASC']);

        $method = 'set' . ucfirst($this->getSortField());
        $key = 1;
        foreach ($content as $item) {
            $item->$method($key++);
        }

        $this->saveSort($provider, $content);
        $this->setContent($content);
    }

    /**
     * getMessages
     *
     * 16/08/2020 16:38
     * @return StatusManager
     */
    public function getMessages(): StatusManager
    {
        return $this->messages;
    }

    /**
     * setMessages
     *
     * 16/08/2020 16:38
     * @param StatusManager $messages
     * @return $this
     */
    public function setMessages(StatusManager $messages): EntitySortManager
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * getPaginationContent
     *
     * 16/08/2020 17:21
     * @param string|null $serialisationName
     * @return array
     */
    public function getPaginationContent(?string $serialisationName = null): array
    {
        return $this->getPagination()->setContent($this->getContent(), $serialisationName)->getContent();
    }

    /**
     * getTableName
     *
     * 22/08/2020 08:59
     * @return string|null
     */
    public function getTableName(): ?string
    {
        return $this->tableName = $this->tableName ?: basename(get_class($this->getSource()));
    }

    /**
     * setTableName
     *
     * 22/08/2020 08:59
     * @param string $tableName
     * @return $this
     */
    public function setTableName(string $tableName): EntitySortManager
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * getEntityName
     *
     * 22/08/2020 09:13
     * @return string|null
     */
    public function getEntityName(): ?string
    {
        return $this->entityName = $this->entityName ?: get_class($this->getSource());
    }

    /**
     * setEntityName
     *
     * 22/08/2020 09:13
     * @param string|null $entityName
     * @return $this
     */
    public function setEntityName(?string $entityName): EntitySortManager
    {
        $this->entityName = $entityName;
        return $this;
    }
}


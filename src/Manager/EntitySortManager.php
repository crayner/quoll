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
     * @var MessageStatusManager
     */
    private MessageStatusManager $messages;

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
            $this->saveSort($provider, $result, basename(get_class($this->getSource())));
        } else {
            $this->getMessages()->info('No change is required.', [], 'messages');
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
     * @param string $tableName
     */
    public function saveSort(EntityProviderInterface $provider, array $result, string $tableName)
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

            foreach ($result as $item)
                $provider->persistFlush($item, false);
            $provider->flush();

            $sm->createIndex($index, $table);
            $this->getMessages()->success();
        } catch (SchemaException | \Exception $e) {
            $this->getMessages()->error($e->getMessage(), [], false);
            $this->getMessages()->error(MessageStatusManager::DATABASE_ERROR);
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

        $this->saveSort($provider, $content, basename(get_class($this->getSource())));
        $this->setContent($content);
    }

    /**
     * getMessages
     *
     * 16/08/2020 16:38
     * @return MessageStatusManager
     */
    public function getMessages(): MessageStatusManager
    {
        return $this->messages;
    }

    /**
     * setMessages
     *
     * 16/08/2020 16:38
     * @param MessageStatusManager $messages
     * @return $this
     */
    public function setMessages(MessageStatusManager $messages): EntitySortManager
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * getPaginationContent
     *
     * 16/08/2020 17:21
     * @return array
     */
    public function getPaginationContent(): array
    {
        return $this->getPagination()->setContent($this->getContent())->getContent();
    }
}


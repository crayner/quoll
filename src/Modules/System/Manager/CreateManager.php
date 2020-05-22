<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 21/05/2020
 * Time: 16:15
 */
namespace App\Modules\System\Manager;

use App\Util\TranslationHelper;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CreateManager
 * @package App\Modules\System\Manager
 */
class CreateManager
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CreateManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * createTables
     */
    public function createTables()
    {
        $finder = new Finder();
        $bundles = $finder->directories()->in(__DIR__ . '/../../')->depth(0);
        $moduleDir = realpath(__DIR__ . '/../../');
        $count = 0;
        try {
            $this->getEm()->beginTransaction();
            foreach ($bundles as $bundle) {
                $finder = new Finder();
                $tables = $finder->files()->in($bundle->getLinkTarget() . '/Entity')->depth(0);
                foreach ($tables as $table) {
                    $name = str_replace(['.php', $moduleDir], ['', 'App\Modules'], $table->getRealPath());
                    $table = new $name();
                    foreach ($table->create() as $sql) {
                        $sql = str_replace(['__prefix__', ' IF NOT EXISTS'], [$this->getPrefix(), ''], $sql);
                        $this->em->getConnection()->exec($sql);
                        $this->getLogger()->notice(TranslationHelper::translate('The {table} was written to the database.', ['{table}' => $name]));
                        $count++;
                    }
                }
            }
            $this->getEm()->commit();
        } catch (TableExistsException | PDOException | \PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
        }
        $this->getLogger()->notice(TranslationHelper::translate('The creation of tables for the database is completed. {count} tables where added.', ['{count}' => $count]));
        return $count;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Logger.
     *
     * @param LoggerInterface $logger
     * @return CreateManager
     */
    public function setLogger(LoggerInterface $logger): CreateManager
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Prefix.
     *
     * @param string $prefix
     * @return CreateManager
     */
    public function setPrefix(string $prefix): CreateManager
    {
        $this->prefix = $prefix;
        return $this;
    }
}
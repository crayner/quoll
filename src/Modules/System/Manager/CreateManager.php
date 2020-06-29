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
 * Date: 21/05/2020
 * Time: 16:15
 */
namespace App\Modules\System\Manager;

use App\Modules\System\Entity\ModuleUpgrade;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @var InstallationManager
     */
    private $manager;

    /**
     * CreateManager constructor.
     * @param EntityManagerInterface $em
     * @param InstallationManager $manager
     */
    public function __construct(EntityManagerInterface $em, InstallationManager $manager)
    {
        $this->em = $em;
        $this->manager = $manager;
    }

    /**
     * @return InstallationManager
     */
    public function getManager(): InstallationManager
    {
        return $this->manager;
    }

    /**
     * Manager.
     *
     * @param InstallationManager $manager
     * @return CreateManager
     */
    public function setManager(InstallationManager $manager): CreateManager
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * createTables
     */
    public function createTables()
    {
        $finder = new Finder();
        $bundles = $finder->directories()->in(__DIR__ . '/../../')->depth(0);
        $moduleDir = realpath(__DIR__ . '/../../');
        $reportLog = [];
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
                        $report = new ModuleUpgrade();
                        $report->setTableName($name)->setTableVersion($name::getVersion())->setTableSection('Create');
                        $reportLog[] = $report;
                    }

                }
            }
            $this->getEm()->commit();
        } catch (TableExistsException | PDOException | \PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
        }
        $this->getLogger()->notice(TranslationHelper::translate('The creation of tables for the database is completed. {count} tables where added.', ['{count}' => $count]));
        $x = 0;
        foreach($reportLog as $report) {
            $this->getEm()->persist($report);
            if ($x++ % 100 === 0) {
                $this->getEm()->flush();
            }
        }
        $this->getEm()->flush();
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

    public function coreData()
    {
        $finder = new Finder();
        $bundles = $finder->directories()->in(__DIR__ . '/../../')->depth(0);
        $moduleDir = realpath(__DIR__ . '/../../');
        $count = 0;
        try {
            foreach ($bundles as $bundle) {
                $finder = new Finder();
                $tables = $finder->files()->in($bundle->getLinkTarget() . '/Entity')->depth(0);
                foreach ($tables as $table) {
                    $this->getEm()->beginTransaction();
                    $name = str_replace(['.php', $moduleDir], ['', 'App\Modules'], $table->getRealPath());
                    $table = new $name();
                    $itemCount = 0;
                    foreach ($table->coreData() as $data) {
                        $table = new $name();
                        $table->loadData($data);
                        $this->em->persist($table);
                        $itemCount++;
                        if ($itemCount % 100 === 0) {
                            $this->em->flush();
                        }
                    }

                    if ($itemCount > 0) {
                        $report = new ModuleUpgrade();
                        $report->setTableName($name)->setTableVersion($name::getVersion())->setTableSection('Core Data');
                        $this->getEm()->persist($report);
                        $this->em->flush();
                        $this->getLogger()->notice(TranslationHelper::translate('Core data was added to {table}. {count} items were added.', ['{table}' => $name, '{count}' => $itemCount]));
                        $count++;
                    }
                    $this->getEm()->commit();
                }
                foreach ($tables as $table) {
                    $this->getEm()->beginTransaction();
                    $name = str_replace(['.php', $moduleDir], ['', 'App\Modules'], $table->getRealPath());
                    $table = new $name();
                    if (method_exists($table,'coreDataLinks')) {
                        $linkCount = 0;
                        foreach($table->coreDataLinks() as $data) {
                            $this->loadDataLinks($name,$data);
                            $linkCount++;
                            if ($linkCount % 100 === 0) {
                                $this->em->flush();
                            }
                        }
                        $report = new ModuleUpgrade();
                        $report->setTableName($name)->setTableVersion($name::getVersion())->setTableSection('Link Data');
                        $this->getEm()->persist($report);
                        $this->getEm()->flush();
                        $this->getLogger()->notice(TranslationHelper::translate('Link data was added for {table}.  {linkCount} links were added.', ['{table}' => $name, '{linkCount}' => $linkCount]));
                    }

                    $this->getEm()->commit();
                }
            }
        } catch (TableExistsException | PDOException | \PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
        }
        $this->getLogger()->notice(TranslationHelper::translate('Core Data has been added to the database. {count} tables had data added.', ['{count}' => $count]));
        return $count;
    }

    /**
     * loadDataLinks
     * @param string $target
     * @param array $data
     */
    private function loadDataLinks(string $target, array $data)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'findBy',
                'source',
                'target',
            ]
        );
        $resolver->setAllowedTypes('findBy', ['array']);
        $resolver->setAllowedTypes('source', ['array']);
        $resolver->setAllowedTypes('target', ['string']);

        $data = $resolver->resolve($data);

        $targetCriteria = $data['findBy'];
        $sourceCriteria = $data['source']['findBy'];

        $targetEntity = ProviderFactory::getRepository($target)->findOneBy($targetCriteria);
        foreach($sourceCriteria as $name=>$value) {
            if ($value === 'use_target_entity') {
                $sourceCriteria[$name] = $targetEntity;
            }
        }
        $sourceEntity = ProviderFactory::getRepository($data['source']['table'])->findOneBy($sourceCriteria);
        $method = 'set' . ucfirst($data['target']);
        if (!method_exists($targetEntity, $method)) {
            $method = 'add' . ucfirst($data['target']);
        }
        $targetEntity->$method($sourceEntity);
        $this->getEm()->persist($targetEntity);
    }

    /**
     * setInstallationStatus
     * @param string $status
     */
    public function setInstallationStatus(string $status)
    {
        $this->getManager()->setInstallationStatus($status);
    }

    /**
     * foreignConstraints
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function foreignConstraints()
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
                    $sql = $table->foreignConstraints();
                    $sql = str_replace(['__prefix__', ], [$this->getPrefix()], $sql);
                    if ('' !== $sql) {
                        $this->em->getConnection()->exec($sql);
                        $this->getLogger()->notice(TranslationHelper::translate('Foreign Constraints were added to {table}.', ['{table}' => $name]));
                        $count++;
                        $report = new ModuleUpgrade();
                        $report->setTableName($name)->setTableVersion($name::getVersion())->setTableSection('Foreign Constraints');
                        $this->getEm()->persist($report);
                        $this->getEm()->flush();
                    }
                }
            }
            $this->getEm()->commit();
        } catch (TableExistsException | PDOException | \PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
        }
        $this->getLogger()->notice(TranslationHelper::translate('Foreign Constraints added to {count} tables.', ['{count}' => $count]));
        return $count;
    }
}
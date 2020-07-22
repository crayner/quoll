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
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CreateManager
 * @package App\Modules\System\Manager
 * @author Craig Rayner <craig@craigrayner.com>
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
     * @var int
     */
    private $totalItemCount = 0;

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
     * @param EntityManagerInterface $em
     * @return int
     */
    public function createTables(EntityManagerInterface $em)
    {
        ini_set('max_execution_time', 240);
        $schemaTool = new SchemaTool($em);

        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $sql = $schemaTool->getUpdateSchemaSql($metadata);

        $schemaTool->updateSchema($metadata);

        $count = 0;
        foreach($sql as $w) {
            if (str_contains($w, 'CREATE TABLE')) {
                $count++;
            }
        }
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

    /**
     * coreData
     * @return int
     * 9/07/2020 07:49
     */
    public function coreData()
    {
        $finder = new Finder();
        $bundles = $finder->directories()->in(__DIR__ . '/../../')->depth(0);
        $moduleDir = realpath(__DIR__ . '/../../');
        $count = 0;
        $totalItemCount = 0;
        try {
            foreach ($bundles as $bundle) {
                ini_set('max_execution_time', 60);
                $finder = new Finder();
                $tables = $finder->files()->in($bundle->getLinkTarget() . '/Entity')->depth(0)->name('*.php');
                foreach ($tables as $table) {
                    $name = str_replace(['.php', $moduleDir], ['', 'App\Modules'], $table->getRealPath());
                    $table = new $name();
                    $itemCount = 0;
                    if (count($table->coreData()) > 0) {
                        $this->getLogger()->notice(TranslationHelper::translate('Core data started for {table}.', ['{table}' => $name]));
                        foreach ($table->coreData() as $data) {
                            $table = new $name();
                            $table->loadData($data);
                            $this->em->persist($table);
                            $itemCount++;
                            if ($itemCount % 100 === 0) {
                                $this->getEm()->flush();
                            }
                        }

                        if ($itemCount > 0) {
                            $report = new ModuleUpgrade();
                            $report->setTableName($name)->setTableVersion($name::getVersion())->setTableSection('Core Data');
                            $this->getEm()->persist($report);
                            $this->getEm()->flush();
                            $this->getLogger()->notice(TranslationHelper::translate('Core data was added to {table}. {count} items were added.', ['{table}' => $name, '{count}' => $itemCount]));
                            $count++;
                            $totalItemCount += $itemCount + 1;
                        }
                    }
                }
            }
        } catch (TableExistsException | PDOException | \PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
        }
        $this->getLogger()->notice(TranslationHelper::translate('Core Data has been added to the database. {count} tables had data added.', ['{count}' => $count]));
        $this->coreDataLinks();
        $this->totalItemCount = $totalItemCount;
        return $count;
    }

    /**
     * coreDataLinks
     * 4/07/2020 16:31
     */
    public function coreDataLinks()
    {
        $finder = new Finder();
        $bundles = $finder->directories()->in(__DIR__ . '/../../')->depth(0);
        $moduleDir = realpath(__DIR__ . '/../../');
        try {
            foreach ($bundles as $bundle) {
                $finder = new Finder();
                ini_set('max_execution_time', 60);
                $tables = $finder->files()->in($bundle->getLinkTarget() . '/Entity')->depth(0)->name('*.php');
                foreach ($tables as $table) {
                    $name = str_replace(['.php', $moduleDir], ['', 'App\Modules'], $table->getRealPath());
                    $table = new $name();
                    if (method_exists($table,'coreDataLinks')) {
                        $this->getLogger()->notice(TranslationHelper::translate('Link data started for {table}.', ['{table}' => $name]));
                        $linkCount = 0;
                        foreach($table->coreDataLinks() as $data) {
                            if ($this->loadDataLinks($name,$data)) {
                                if (++$linkCount % 100 === 0) {
                                    $this->em->flush();
                                }
                            }
                        }
                        if ($linkCount > 0) {
                            $report = new ModuleUpgrade();
                            $report->setTableName($name)
                                ->setTableVersion($name::getVersion())
                                ->setTableSection('Link Data');
                            $this->getEm()->persist($report);
                            $this->getEm()->flush();
                            $this->getLogger()->notice(TranslationHelper::translate('Link data was added for {table}.  {linkCount} links were added.', ['{table}' => $name, '{linkCount}' => $linkCount]));
                            $this->totalItemCount++;
                        }
                    }
                }
            }
        } catch (TableExistsException | PDOException | \PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
        }
    }

    /**
     * loadDataLinks
     * @param string $target
     * @param array $data
     */
    private function loadDataLinks(string $target, array $data): bool
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

        if ($targetEntity === null) {
            $this->getLogger()->error(TranslationHelper::translate('The link target was not found for "{target}" for value "{value}."', ['{target}' => $target, '{value}' => json_encode($targetCriteria)]));
            return false;
        }
        foreach($sourceCriteria as $name=>$value) {
            if ($value === 'use_target_entity') {
                $sourceCriteria[$name] = $targetEntity;
            }
        }

        $sourceEntity = ProviderFactory::getRepository($data['source']['table'])->findOneBy($sourceCriteria);
        if ($sourceEntity === null) {
            $this->getLogger()->error(TranslationHelper::translate('The link source was not found for "{source}" for value "{value}."', ['{source}' => $data['source']['table'], '{value}' => json_encode($sourceCriteria)]));
            return false;
        }

        $method = 'add' . ucfirst($data['target']);
        if (!method_exists($targetEntity, $method)) $method = 'set' . ucfirst($data['target']);
        $targetEntity->$method($sourceEntity);
        $this->getEm()->persist($targetEntity);
        return true;
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
     * @return int
     */
    public function getTotalItemCount(): int
    {
        return $this->totalItemCount;
    }

    /**
     * @param int $totalItemCount
     * @return CreateManager
     */
    public function setTotalItemCount(int $totalItemCount): CreateManager
    {
        $this->totalItemCount = $totalItemCount;
        return $this;
    }
}

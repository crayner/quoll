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
 * Date: 21/01/2020
 * Time: 07:42
 */
namespace App\Modules\System\Manager;

use App\Modules\System\Entity\Module;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class UpgradeManager
 * @package App\Modules\System\Manager
 */
class UpgradeManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $version;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var ParameterBagInterface
     */
    private $bag;

    /**
     * UpgradeManager constructor.
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface $bag
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $bag, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->connection = $this->em->getConnection();
        $this->bag = $bag;
        $this->logger = $logger;
    }

    /**
     * moduleAtVersion
     * @param Module|null $module
     * @param string|null $version
     * @return bool
     */
    public function hasModuleVersion(?Module $module, ?string $version): bool
    {
        if (in_array($version, [null,'']) || null === $module)
            return false;
        return $this->em->getRepository(ModuleUpgrade::class)->hasModuleVersion($module, strtolower($version));
    }

    /**
     * setModuleVersion
     * @param Module $module
     * @param string $version
     * @return $this
     */
    public function setModuleVersion(Module $module, string $version): self
    {
        if (! $this->hasModuleVersion($module, $version)) {
            $mu = new ModuleUpgrade();
            $mu->setModule($module)->setVersion(strtolower($version));
            $this->em->persist($mu);
            $this->em->flush();
        }
        return $this;
    }

    /**
     * Installation
     * @param KernelInterface $kernel
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function installation(KernelInterface $kernel)
    {
        $finder = new Finder();
        $projectDir = $kernel->getContainer()->getParameter('kernel.project_dir');
        $exitCode = 0;
        $this->getLogger()->notice('Module Installation');
        $version = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/version.yaml'));

        $file = realpath(__DIR__ . '/../Resources/migration/installation.sql');

        $this->getSql($file);

        $file = realpath(__DIR__ . '/../Resources/migration/core.sql');

        $this->getSql($file);

        $this->loadGibbonLegacyTables();

        $this->writeFileSql();

        $this->writeModuleDetails($version['module']);

        $this->setModuleVersion($this->getModule(), 'installation');
        $this->setModuleVersion($this->getModule(), 'core');

        $bundles = $finder->directories()->in($projectDir . '/Gibbon/modules/')->depth(0);
        foreach ($bundles as $bundle) {
            $this->version = null;
        
            $module = $this->em->getRepository(Module::class)->findOneByName(ucfirst($bundle->getBasename()));
            // Do Legacy Module Build
            if (is_file($bundle->getRealPath() . '/legacy.yaml') && !$module instanceof Module && !$this->hasModuleVersion($module, 'Legacy')) {
                $this->getLogger()->notice('Checking legacy bundle ' . $bundle->getBasename());
                $version = Yaml::parse(file_get_contents($bundle->getRealPath() . '/legacy.yaml'));
                $this->version = $version;

                if (isset($version['module'])) {
                    $exitCode += $this->writeModuleDetails($version['module']);
                    if ($exitCode === 0)
                        $this->setModuleVersion($this->getModule(), 'Legacy');
                }
                if (isset($version['events']))
                    $exitCode += $this->writeEventDetails($version['events'],$version['name']);
            }
        }

        $this->getLogger()->notice('Module Check:');
        $finder = new Finder();
        $bundles = $finder->directories()->in($projectDir . '/vendor/kookaburra/')->depth(0);

        foreach ($bundles as $bundle) {
            file_put_contents(__DIR__ . '/../../../../../var/log/'. $bundle->getBasename().'.txt', $bundle->getBasename());
            if ($bundle->getBasename() === 'system-admin')
                continue;
            $this->version = null;
            $this->getLogger()->notice('Build Check for  ' . $bundle->getBasename());
            // do the installation stuff

            // Do Migration stuff
            if (is_file($bundle->getRealPath() . '/src/Resources/config/version.yaml')) {
                $version = Yaml::parse(file_get_contents($bundle->getRealPath() . '/src/Resources/config/version.yaml'));
                $module = $this->em->getRepository(Module::class)->findOneByName($this->version['name']);

                if (isset($version['module']))
                    $exitCode += $this->writeModuleDetails($version['module']);
                if ($exitCode === 0)
                    $this->setModuleVersion($this->getModule(), $version['version']);

                if (!$this->hasModuleVersion($this->getModule(), 'installation')) {
                    if (is_file($bundle->getRealpath() . '/src/Resources/migration/installation.sql')) {
                        $this->getLogger()->notice('Installation for ' . $bundle->getBasename());
                        $this->sqlContent = [];
                        $this->getSql($bundle->getRealpath() . '/src/Resources/migration/installation.sql');
                        if ($this->writeFileSql(   ) > 0)
                            return 1;
                        else
                            $this->setModuleVersion($this->getModule(), 'installation');
                    }
                }

                if (!$this->hasModuleVersion($this->getModule(), 'core')) {
                    if (is_file($bundle->getRealpath() . '/src/Resources/migration/core.sql')) {
                        $this->getLogger()->notice('Core for ' . $bundle->getBasename());
                        $this->sqlContent = [];
                        $this->getSql($bundle->getRealpath() . '/src/Resources/migration/core.sql');
                        if ($this->writeFileSql() > 0)
                            return 1;
                        else
                            $this->setModuleVersion($this->getModule(), 'core');
                    }
                }
            }
            $this->getLogger()->notice('Installation completed and database created for ' . $bundle->getBasename());
        }

        $this->getLogger()->notice('Legacy Table Creation');
        $this->sqlContent = [];
        $this->loadGibbonLegacyTables();
        if ($this->writeFileSql() > 0)
            return 1;

        // Legacy Core
        $this->getLogger()->notice('Legacy Core');
        $this->sqlContent = [];
        $this->getSql(__DIR__ . '/../../../../../src/Migrations/gibbon_base.sql');
        if ($this->writeFileSql() > 0)
            return 1;

        // Add upgrades here ...
        $name = isset($this->version['name']) ? $this->version['name'] : ucfirst($bundle->getBasename());


        // do demo here
        if ($this->installDemo()) {
            foreach ($bundles as $bundle) {
                if (!$this->hasModuleVersion($this->getModule(), 'demo')) {
                    {
                        if (is_file($bundle->getRealpath() . '/src/Resources/migration/demo.sql')) {
                            $this->getLogger()->notice('Demonstration data for ' . $bundle->getBasename());
                            $this->sqlContent = [];
                            $this->getSql($bundle->getRealpath() . '/src/Resources/migration/demo.sql');
                            if ($this->writeFileSql() > 0)
                                return 1;
                        }
                    }
                }
            }

            // Legacy Demonstration
            $this->getLogger()->notice('Legacy Demonstration');
            $this->sqlContent = [];
            $this->getSql(__DIR__ . '/../../../../../src/Migrations/gibbon_demo.sql');
            if ($this->writeFileSql() > 0)
                return 1;
        }

        // Foreign Constraints
        $this->getLogger()->notice('Legacy Constraints');
        $this->sqlContent = [];
        $this->loadForeignConstraints();
        if ($this->writeFileSql() > 0)
            return 1;
        else
            $this->setModuleVersion($this->getModule(), 'foreign-constraint');


        foreach ($bundles as $bundle) {
            if (!$this->hasModuleVersion($this->getModule(), 'foreign-constraint')) {
                {
                    if (is_file($bundle->getRealpath() . '/src/Resources/migration/foreign-constraint.sql')) {
                        $this->getLogger()->notice('Foreign Constraint for ' . $bundle->getBasename(), 'foreign-constraint');
                        $this->sqlContent = [];
                        $this->getSql($bundle->getRealpath() . '/src/Resources/migration/foreign-constraint.sql');
                        if ($this->writeFileSql() > 0)
                            return 1;
                        else
                            $this->setModuleVersion($this->getModule(), 'foreign-constraint');
                    }
                }
            }
            $finder = new Finder();
            $updates = $finder->files()->in($bundle->getRealpath() . '/src/Resources/migration')->depth(0)->name('Version*.sql')->sortByName();
            if ($updates->hasResults()) {
                foreach($updates as $update) {
                    if (!$this->hasModuleVersion($this->getModule(), str_replace(['version', 'Version', '.sql'], '', $update->getBasename()))) {
                        {
                            $this->getLogger()->notice(sprintf('Update for <info>%s</info>.', $update->getBasename()));
                            $this->setSqlContent([]);
                            $this->getSql($update->getRealpath());
                            if ($this->writeFileSql() > 0) {
                                $this->getLogger()->error(sprintf('Installation for %s failed.', $update->getBasename()));
                                return 1;
                            }
                            else {
                                $this->setModuleVersion($this->getModule(), str_replace(['version', 'Version', '.sql'], '', $update->getBasename()));
                                $this->getLogger()->notice(sprintf('Update for %s completed', $update->getBasename()));
                            }
                        }
                    }

                }
            }
        }

        $this->loadForeignConstraints();
        if ($this->writeFileSql() > 0)
        {
            $this->getLogger()->error('Installation of legacy foreign constraints failed.');
            return 1;

        }

        $this->getLogger()->notice('Installation completed and database created.' );
    }

    /**
     * getLogger
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * setLogger
     * @param LoggerInterface $logger
     * @return UpgradeManager
     */
    public function setLogger(LoggerInterface $logger): UpgradeManager
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * writeModuleDetails
     * @param array $w
     * @return int
     */
    public function writeModuleDetails(array $w)
    {
        $module = $this->em->getRepository(Module::class)->findOneBy(['name' => $w['name']]) ?: new Module();
        $this->getLogger()->notice(sprintf('Creating or Modifying Module / Action / Permission entries for "%s" bundle.', $w['name']));
        $legacy = false;

        try {
            $module
                ->setName($w['name'])
                ->setEntryURL($w['entryURL'])
                ->setDescription($w['description'])
                ->setActive($w['active'])
                ->setCategory($w['category'])
                ->setVersion($w['version'])
                ->setAuthor($w['author'])
                ->setUrl($w['url'])
                ->setType($w['type']);
            $actions = [];
            foreach ($w['actions'] as $j=>$r) {
                $action = $this->em->getRepository(Action::class)->findOneBy(['name' => $r['name'], 'module' => $module]) ?: new Action();
                $action
                    ->setName($r['name'])
                    ->setPrecedence($r['precedence'])
                    ->setCategory($r['category'])
                    ->setDescription($r['description'])
                    ->setURLList($r['URLList'])
                    ->setEntryURL($r['entryURL'])
                    ->setEntrySidebar($r['entrySidebar'])
                    ->setMenuShow($r['menuShow'])
                    ->setDefaultPermissionAdmin($r['defaultPermissionAdmin'])
                    ->setDefaultPermissionTeacher($r['defaultPermissionTeacher'])
                    ->setDefaultPermissionStudent($r['defaultPermissionStudent'])
                    ->setDefaultPermissionParent($r['defaultPermissionParent'])
                    ->setDefaultPermissionSupport($r['defaultPermissionSupport'])
                    ->setCategoryPermissionStaff($r['categoryPermissionStaff'])
                    ->setCategoryPermissionStudent($r['categoryPermissionStudent'])
                    ->setCategoryPermissionParent($r['categoryPermissionParent'])
                    ->setCategoryPermissionOther($r['categoryPermissionOther'])
                    ->setModule($module);

                //legacy
                if (key_exists('permissions', $r)) {
                    $r['roles'] = $r['permissions'];
                    if (!$legacy)
                        $this->getLogger()->notice(sprintf('Legacy permissions where identified and used for "%s" bundle.', $w['name']));
                    $legacy = true;
                }

                if (isset($r['roles'])) {
                    foreach ($r['roles'] as $t) {
                        $role = $this->em->getRepository(Role::class)->findOneByName($t);
                        $action->addRole($role);
                    }
                }
                $actions[] = $action;
            }

        } catch (\Exception $e) {
            $this->getLogger()->error(sprintf('Error for "%s" bundle: %s', $w['name'], $e->getMessage()));
        }

        $exitCode = 0;

        try {
            $this->em->beginTransaction();
            $this->em->persist($module);
            foreach($actions as $action)
                $this->em->persist($action);
            $this->em->commit();
        } catch (PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
            $exitCode = 1;
        }

        if ($exitCode > 0) {
            $this->getLogger()->error(sprintf('Some errors occurred while installing the "%s" bundle to the Module Table.', $w['name']));
        }

        $this->setModule($module);

        return $exitCode;
    }

    /**
     * writeEventDetails
     * @param array $details
     * @param string $name
     * @return int
     */
    public function writeEventDetails(array $details, string $name): int
    {
        if (empty($details))
            return 0;
        $module = $this->em->getRepository(Module::class)->findOneBy(['name' => $name]) ?: new Module();
        $this->getLogger()->notice(sprintf('Writing events for "%s" bundle.', $name));
        $exitCode = 0;
        $count = 0;
        try {
            $this->em->beginTransaction();
            foreach($details as $item)
            {
                $event = new NotificationEvent();
                $action = $this->em->getRepository(Action::class)->findOneBy(['name' => $item['action']]) ?: null;
                $event->setEvent($item['event'])
                    ->setModule($module)
                    ->setScopes(implode(',',$item['scopes']))
                    ->setActive($item['active'])
                    ->setAction($action)
                ;
                $this->em->persist($event);
                $count++;
            }
            $this->em->commit();
            $this->getLogger()->notice(sprintf('%s events for "%s" bundle were created.', strval($count), $name));
        } catch (PDOException $e) {
            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
            $exitCode = 1;
        }

        return $exitCode;
    }

    /**
     * @return string
     */
    public static function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Version.
     *
     * @param string $version
     * @return UpgradeManager
     */
    public function setVersion(string $version): UpgradeManager
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param Module $module
     * @return UpgradeManager
     */
    public function setModule(Module $module): UpgradeManager
    {
        $this->module = $module;
        return $this;
    }

    /**
     * addSql
     * @param string $line
     * @return $this
     */
    private function addSql(string $line): self
    {
        $this->getSqlContent();
        $this->sqlContent[] = $line;
        return $this;
    }

    /**
     * getSqlContent
     * @return array
     */
    public function getSqlContent(): array
    {
        return $this->sqlContent = $this->sqlContent ?: [];
    }

    /**
     * writeFileSql
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function writeFileSql(): int
    {
        try {
            $this->em->beginTransaction();
            foreach ($this->getSqlContent() as $sql) {
                $sql = str_replace('__prefix__', $this->getPrefix(), $sql);
                $sql = str_replace(' IF NOT EXISTS', '', $sql);
                if ('' !== trim($sql)) {
                    $this->em->getConnection()->exec($sql);
//                    $this->logger->debug($sql);
                }
            }
            $this->em->commit();
            return 0;
        } catch (UniqueConstraintViolationException $e) {
            return 0;
        } catch (TableExistsException | PDOException $e) {

            $this->em->rollback();
            $this->getLogger()->error($e->getMessage());
            return 1;
        }
    }

    /**
     * loadGibbonLegacyTables
     */
    private function loadGibbonLegacyTables()  : void
    {
        $this->logger->notice('The Legacy tables begin.');
        $this->addSql('CREATE TABLE gibbonAlarm (gibbonAlarmID INT(5) UNSIGNED AUTO_INCREMENT, type VARCHAR(8) DEFAULT NULL, status VARCHAR(7) DEFAULT \'Past\' NOT NULL, timestampStart DATETIME DEFAULT NULL, timestampEnd DATETIME DEFAULT NULL, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_E3BDDFEBCC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonAlarmID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonAlarmConfirm (gibbonAlarmConfirmID INT(8) UNSIGNED AUTO_INCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonAlarmID INT(5) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_8E3CE4BCBA3565E5 (gibbonAlarmID), INDEX IDX_8E3CE4BCCC6782D6 (gibbonPersonID), UNIQUE INDEX gibbonAlarmID (gibbonAlarmID, gibbonPersonID), PRIMARY KEY(gibbonAlarmConfirmID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonApplicationForm (gibbonApplicationFormID INT(12) UNSIGNED AUTO_INCREMENT, gibbonApplicationFormHash VARCHAR(40) DEFAULT NULL, surname VARCHAR(60) NOT NULL, firstName VARCHAR(60) NOT NULL, preferredName VARCHAR(60) NOT NULL, officialName VARCHAR(150) NOT NULL, nameInCharacters VARCHAR(20) NOT NULL, gender VARCHAR(12) DEFAULT \'Unspecified\', username VARCHAR(20) DEFAULT NULL, status VARCHAR(12) DEFAULT \'Pending\' NOT NULL, dob DATE DEFAULT NULL, email VARCHAR(75) DEFAULT NULL, homeAddress LONGTEXT DEFAULT NULL, homeAddressDistrict VARCHAR(255) DEFAULT NULL, homeAddressCountry VARCHAR(255) DEFAULT NULL, phone1Type VARCHAR(6) NOT NULL, phone1CountryCode VARCHAR(7) NOT NULL, phone1 VARCHAR(20) NOT NULL, phone2Type VARCHAR(6) NOT NULL, phone2CountryCode VARCHAR(7) NOT NULL, phone2 VARCHAR(20) NOT NULL, countryOfBirth VARCHAR(30) NOT NULL, citizenship1 VARCHAR(255) NOT NULL, citizenship1Passport VARCHAR(30) NOT NULL, nationalIDCardNumber VARCHAR(30) NOT NULL, residencyStatus VARCHAR(255) NOT NULL, visaExpiryDate DATE DEFAULT NULL, dayType VARCHAR(255) DEFAULT NULL, referenceEmail VARCHAR(100) DEFAULT NULL, schoolName1 VARCHAR(50) NOT NULL, schoolAddress1 VARCHAR(255) NOT NULL, schoolGrades1 VARCHAR(20) NOT NULL, schoolLanguage1 VARCHAR(50) NOT NULL, schoolDate1 DATE DEFAULT NULL, schoolName2 VARCHAR(50) NOT NULL, schoolAddress2 VARCHAR(255) NOT NULL, schoolGrades2 VARCHAR(20) NOT NULL, schoolLanguage2 VARCHAR(50) NOT NULL, schoolDate2 DATE DEFAULT NULL, siblingName1 VARCHAR(50) NOT NULL, siblingDOB1 DATE DEFAULT NULL, siblingSchool1 VARCHAR(50) NOT NULL, siblingSchoolJoiningDate1 DATE DEFAULT NULL, siblingName2 VARCHAR(50) NOT NULL, siblingDOB2 DATE DEFAULT NULL, siblingSchool2 VARCHAR(50) NOT NULL, siblingSchoolJoiningDate2 DATE DEFAULT NULL, siblingName3 VARCHAR(50) NOT NULL, siblingDOB3 DATE DEFAULT NULL, siblingSchool3 VARCHAR(50) NOT NULL, siblingSchoolJoiningDate3 DATE DEFAULT NULL, languageHomePrimary VARCHAR(30) NOT NULL, languageHomeSecondary VARCHAR(30) NOT NULL, languageFirst VARCHAR(30) NOT NULL, languageSecond VARCHAR(30) NOT NULL, languageThird VARCHAR(30) NOT NULL, medicalInformation LONGTEXT NOT NULL, sen VARCHAR(1) DEFAULT NULL, senDetails LONGTEXT NOT NULL, languageChoice VARCHAR(100) DEFAULT NULL, languageChoiceExperience LONGTEXT DEFAULT NULL, scholarshipInterest VARCHAR(1) DEFAULT \'N\' NOT NULL, scholarshipRequired VARCHAR(1) DEFAULT \'N\' NOT NULL, payment VARCHAR(7) DEFAULT \'Family\' NOT NULL, companyName VARCHAR(100) DEFAULT NULL, companyContact VARCHAR(100) DEFAULT NULL, companyAddress VARCHAR(255) DEFAULT NULL, companyEmail LONGTEXT DEFAULT NULL, companyCCFamily VARCHAR(1) DEFAULT NULL COMMENT \'When company is billed, should family receive a copy?\', companyPhone VARCHAR(20) DEFAULT NULL, companyAll VARCHAR(1) DEFAULT NULL, gibbonFinanceFeeCategoryIDList LONGTEXT DEFAULT NULL, agreement VARCHAR(1) DEFAULT NULL, parent1title VARCHAR(5) DEFAULT NULL, parent1surname VARCHAR(60) DEFAULT NULL, parent1firstName VARCHAR(60) DEFAULT NULL, parent1preferredName VARCHAR(60) DEFAULT NULL, parent1officialName VARCHAR(150) DEFAULT NULL, parent1nameInCharacters VARCHAR(20) DEFAULT NULL, parent1gender VARCHAR(12) DEFAULT \'Unspecified\', parent1relationship VARCHAR(50) DEFAULT NULL, parent1languageFirst VARCHAR(30) DEFAULT NULL, parent1languageSecond VARCHAR(30) DEFAULT NULL, parent1citizenship1 VARCHAR(255) DEFAULT NULL, parent1nationalIDCardNumber VARCHAR(30) DEFAULT NULL, parent1residencyStatus VARCHAR(255) DEFAULT NULL, parent1visaExpiryDate DATE DEFAULT NULL, parent1email VARCHAR(75) DEFAULT NULL, parent1phone1Type VARCHAR(6) DEFAULT NULL, parent1phone1CountryCode VARCHAR(7) DEFAULT NULL, parent1phone1 VARCHAR(20) DEFAULT NULL, parent1phone2Type VARCHAR(6) DEFAULT NULL, parent1phone2CountryCode VARCHAR(7) DEFAULT NULL, parent1phone2 VARCHAR(20) DEFAULT NULL, parent1profession VARCHAR(30) DEFAULT NULL, parent1employer VARCHAR(30) DEFAULT NULL, parent2title VARCHAR(5) DEFAULT NULL, parent2surname VARCHAR(60) DEFAULT NULL, parent2firstName VARCHAR(60) DEFAULT NULL, parent2preferredName VARCHAR(60) DEFAULT NULL, parent2officialName VARCHAR(150) DEFAULT NULL, parent2nameInCharacters VARCHAR(20) DEFAULT NULL, parent2gender VARCHAR(12) DEFAULT \'Unspecified\', parent2relationship VARCHAR(50) DEFAULT NULL, parent2languageFirst VARCHAR(30) DEFAULT NULL, parent2languageSecond VARCHAR(30) DEFAULT NULL, parent2citizenship1 VARCHAR(255) DEFAULT NULL, parent2nationalIDCardNumber VARCHAR(30) DEFAULT NULL, parent2residencyStatus VARCHAR(255) DEFAULT NULL, parent2visaExpiryDate DATE DEFAULT NULL, parent2email VARCHAR(75) DEFAULT NULL, parent2phone1Type VARCHAR(6) DEFAULT NULL, parent2phone1CountryCode VARCHAR(7) DEFAULT NULL, parent2phone1 VARCHAR(20) DEFAULT NULL, parent2phone2Type VARCHAR(6) DEFAULT NULL, parent2phone2CountryCode VARCHAR(7) DEFAULT NULL, parent2phone2 VARCHAR(20) DEFAULT NULL, parent2profession VARCHAR(30) DEFAULT NULL, parent2employer VARCHAR(30) DEFAULT NULL, timestamp DATETIME DEFAULT NULL, priority INT(1), milestones LONGTEXT NOT NULL, notes LONGTEXT NOT NULL, dateStart DATE DEFAULT NULL, howDidYouHear VARCHAR(255) DEFAULT NULL, howDidYouHearMore VARCHAR(255) DEFAULT NULL, paymentMade VARCHAR(10) DEFAULT \'N\' NOT NULL, studentID VARCHAR(10) DEFAULT NULL, privacy LONGTEXT DEFAULT NULL, fields LONGTEXT NOT NULL COMMENT \'Serialised array of custom field values\', parent1fields LONGTEXT NOT NULL COMMENT \'Serialised array of custom field values\', parent2fields LONGTEXT NOT NULL COMMENT \'Serialised array of custom field values\', AcademicYearIDEntry INT(3) UNSIGNED, gibbonYearGroupIDEntry INT(3) UNSIGNED, parent1gibbonPersonID INT(10) UNSIGNED, gibbonRollGroupID INT(5) UNSIGNED, gibbonFamilyID INT(7) UNSIGNED, gibbonPaymentID INT(14) UNSIGNED, INDEX IDX_A309B59CF9B7736F (AcademicYearIDEntry), INDEX IDX_A309B59C9DE35FD8 (gibbonYearGroupIDEntry), INDEX IDX_A309B59C7DF7AB4B (parent1gibbonPersonID), INDEX IDX_A309B59CA85AE4EC (gibbonRollGroupID), INDEX IDX_A309B59C51F0BB1F (gibbonFamilyID), INDEX IDX_A309B59CA0F353A3 (gibbonPaymentID), PRIMARY KEY(gibbonApplicationFormID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonApplicationFormFile (gibbonApplicationFormFileID INT(14) UNSIGNED AUTO_INCREMENT, name VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, gibbonApplicationFormID INT(12) UNSIGNED, INDEX IDX_86B3B2D2772C4226 (gibbonApplicationFormID), PRIMARY KEY(gibbonApplicationFormFileID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonApplicationFormLink (gibbonApplicationFormLinkID INT(12) UNSIGNED AUTO_INCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonApplicationFormID1 INT(12) UNSIGNED, gibbonApplicationFormID2 INT(12) UNSIGNED, INDEX IDX_3C801D3351A64608 (gibbonApplicationFormID1), INDEX IDX_3C801D33C8AF17B2 (gibbonApplicationFormID2), UNIQUE INDEX link (gibbonApplicationFormID1, gibbonApplicationFormID2), PRIMARY KEY(gibbonApplicationFormLinkID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonApplicationFormRelationship (gibbonApplicationFormRelationshipID INT(14) UNSIGNED AUTO_INCREMENT, relationship VARCHAR(50) NOT NULL, gibbonApplicationFormID INT(12) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_5E0017E7772C4226 (gibbonApplicationFormID), INDEX IDX_5E0017E7CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonApplicationFormRelationshipID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonBehaviour (gibbonBehaviourID INT(12) UNSIGNED AUTO_INCREMENT, date DATE NOT NULL, type VARCHAR(8) NOT NULL, descriptor VARCHAR(100) DEFAULT NULL, level VARCHAR(100) DEFAULT NULL, comment LONGTEXT NOT NULL, followup LONGTEXT NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, AcademicYearID INT(3) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_64915B371FA7520 (AcademicYearID), INDEX IDX_64915B3FE417281 (gibbonPlannerEntryID), INDEX IDX_64915B3FF59AAB0 (gibbonPersonIDCreator), INDEX gibbonPersonID (gibbonPersonID), PRIMARY KEY(gibbonBehaviourID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonBehaviourLetter (gibbonBehaviourLetterID INT(10) UNSIGNED AUTO_INCREMENT, letterLevel VARCHAR(1) NOT NULL, status VARCHAR(7) NOT NULL, recordCountAtCreation INT(3), body LONGTEXT NOT NULL, recipientList LONGTEXT NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, AcademicYearID INT(3) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_5F61F91071FA7520 (AcademicYearID), INDEX IDX_5F61F910CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonBehaviourLetterID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonCourse` (
  `gibbonCourseID` int(8) UNSIGNED AUTO_INCREMENT,
  `name` CHAR(60) COLLATE utf8_unicode_ci NOT NULL,
  `nameShort` CHAR(12) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `map` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\' COMMENT \'Should this course be included in curriculum maps and other summaries?\',
  `gibbonYearGroupIDList` CHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `orderBy` int(3) DEFAULT NULL,
  `academic_year` int(3) UNSIGNED DEFAULT NULL,
  `gibbonDepartmentID` int(4) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonCourseID`),
  UNIQUE KEY `nameYear` (`academic_year`,`name`),
  UNIQUE KEY `nameShortYear` (`academic_year`,`nameShort`),
  KEY `department` (`gibbonDepartmentID`),
  KEY `academic_year` (`academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonCourseClass` (
    `gibbonCourseClassID` int(8)  UNSIGNED AUTO_INCREMENT,
  `name` CHAR(30) COLLATE utf8_unicode_ci NOT NULL,
  `nameShort` CHAR(8) COLLATE utf8_unicode_ci NOT NULL,
  `reportable` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `attendance` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `gibbonCourseID` int(8) UNSIGNED DEFAULT NULL,
  `gibbonScaleIDTarget` int(5) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonCourseClassID`),
  UNIQUE KEY `nameCourse` (`name`,`gibbonCourseID`),
  UNIQUE KEY `nameShortCourse` (`nameShort`,`gibbonCourseID`),
  KEY `IDX_455FF3977DD4B430` (`gibbonScaleIDTarget`),
  KEY `gibbonCourseID` (`gibbonCourseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonCourseClassMap (gibbonCourseClassMapID INT(8) UNSIGNED AUTO_INCREMENT, gibbonCourseClassID INT(8) UNSIGNED, gibbonRollGroupID INT(5) UNSIGNED, gibbonYearGroupID INT(3) UNSIGNED, INDEX IDX_97F9BC70A85AE4EC (gibbonRollGroupID), INDEX IDX_97F9BC70427372F (gibbonYearGroupID), UNIQUE INDEX gibbonCourseClassID (gibbonCourseClassID), PRIMARY KEY(gibbonCourseClassMapID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonCourseClassPerson` (
  `gibbonCourseClassPersonID` int(10) UNSIGNED AUTO_INCREMENT,
  `role` CHAR(16) COLLATE utf8_unicode_ci NOT NULL,
  `reportable` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `gibbonCourseClassID` int(8) UNSIGNED DEFAULT NULL,
  `gibbonPersonID` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonCourseClassPersonID`),
  UNIQUE KEY `courseClassPerson` (`gibbonCourseClassID`,`gibbonPersonID`),
  KEY `IDX_D9B888E9CC6782D6` (`gibbonPersonID`),
  KEY `gibbonCourseClassID` (`gibbonCourseClassID`),
  KEY `gibbonPersonID` (`gibbonPersonID`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
        $this->addSql('CREATE TABLE gibbonCrowdAssessDiscuss (gibbonCrowdAssessDiscussID INT(16) UNSIGNED AUTO_INCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, comment LONGTEXT NOT NULL, gibbonPlannerEntryHomeworkID INT(16) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonCrowdAssessDiscussIDReplyTo INT(16) UNSIGNED, INDEX IDX_D17E708617B9ED44 (gibbonPlannerEntryHomeworkID), INDEX IDX_D17E7086CC6782D6 (gibbonPersonID), INDEX IDX_D17E7086D96E9809 (gibbonCrowdAssessDiscussIDReplyTo), PRIMARY KEY(gibbonCrowdAssessDiscussID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceBillingSchedule (gibbonFinanceBillingScheduleID INT(6) UNSIGNED AUTO_INCREMENT, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, active VARCHAR(1) DEFAULT \'Y\' NOT NULL, invoiceIssueDate DATE DEFAULT NULL, invoiceDueDate DATE DEFAULT NULL, timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, AcademicYearID INT(3) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_EC0D8C7D71FA7520 (AcademicYearID), INDEX IDX_EC0D8C7DFF59AAB0 (gibbonPersonIDCreator), INDEX IDX_EC0D8C7DAE8C8C10 (gibbonPersonIDUpdate), PRIMARY KEY(gibbonFinanceBillingScheduleID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceBudget (gibbonFinanceBudgetID INT(4) UNSIGNED AUTO_INCREMENT, name VARCHAR(30) NOT NULL, nameShort VARCHAR(8) NOT NULL, active VARCHAR(1) DEFAULT \'Y\' NOT NULL, category VARCHAR(255) NOT NULL, timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_EE793C02FF59AAB0 (gibbonPersonIDCreator), INDEX IDX_EE793C02AE8C8C10 (gibbonPersonIDUpdate), UNIQUE INDEX name (name), UNIQUE INDEX nameShort (nameShort), PRIMARY KEY(gibbonFinanceBudgetID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceBudgetCycle (gibbonFinanceBudgetCycleID INT(6) UNSIGNED AUTO_INCREMENT, name VARCHAR(7) NOT NULL, status VARCHAR(8) DEFAULT \'Upcoming\' NOT NULL, dateStart DATE NOT NULL, dateEnd DATE NOT NULL, sequenceNumber INT(6), timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_2AA76753FF59AAB0 (gibbonPersonIDCreator), INDEX IDX_2AA76753AE8C8C10 (gibbonPersonIDUpdate), UNIQUE INDEX name (name), PRIMARY KEY(gibbonFinanceBudgetCycleID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceBudgetCycleAllocation (gibbonFinanceBudgetCycleAllocationID INT(10) UNSIGNED AUTO_INCREMENT, value NUMERIC(14, 2) DEFAULT \'0.00\' NOT NULL, gibbonFinanceBudgetID INT(4) UNSIGNED, gibbonFinanceBudgetCycleID INT(6) UNSIGNED, INDEX IDX_B27D799BC8A9346 (gibbonFinanceBudgetID), INDEX IDX_B27D799B5393B3F1 (gibbonFinanceBudgetCycleID), PRIMARY KEY(gibbonFinanceBudgetCycleAllocationID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceBudgetPerson (gibbonFinanceBudgetPersonID INT(8) UNSIGNED AUTO_INCREMENT, access VARCHAR(6) NOT NULL, gibbonFinanceBudgetID INT(4) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_AF223270C8A9346 (gibbonFinanceBudgetID), INDEX IDX_AF223270CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonFinanceBudgetPersonID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceExpense (gibbonFinanceExpenseID INT(14) UNSIGNED AUTO_INCREMENT, title VARCHAR(60) NOT NULL, body LONGTEXT NOT NULL, status VARCHAR(10) NOT NULL, cost NUMERIC(12, 2) NOT NULL, countAgainstBudget VARCHAR(1) DEFAULT \'Y\' NOT NULL, purchaseBy VARCHAR(6) DEFAULT \'School\' NOT NULL, purchaseDetails LONGTEXT NOT NULL, paymentMethod VARCHAR(16) DEFAULT NULL, paymentDate DATE DEFAULT NULL, paymentAmount NUMERIC(12, 2) DEFAULT NULL, paymentID VARCHAR(100) DEFAULT NULL, paymentReimbursementReceipt VARCHAR(255) NOT NULL, paymentReimbursementStatus VARCHAR(10) DEFAULT NULL, timestampCreator DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, statusApprovalBudgetCleared VARCHAR(1) DEFAULT \'N\' NOT NULL, gibbonFinanceBudgetID INT(4) UNSIGNED, gibbonFinanceBudgetCycleID INT(6) UNSIGNED, gibbonPersonIDPayment INT(10) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_47ECFF5C8A9346 (gibbonFinanceBudgetID), INDEX IDX_47ECFF55393B3F1 (gibbonFinanceBudgetCycleID), INDEX IDX_47ECFF52E77C4DE (gibbonPersonIDPayment), INDEX IDX_47ECFF5FF59AAB0 (gibbonPersonIDCreator), PRIMARY KEY(gibbonFinanceExpenseID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceExpenseApprover (gibbonFinanceExpenseApproverID INT(4) UNSIGNED AUTO_INCREMENT, sequenceNumber INT(4), timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, gibbonPersonID INT(10) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_38833027CC6782D6 (gibbonPersonID), INDEX IDX_38833027FF59AAB0 (gibbonPersonIDCreator), INDEX IDX_38833027AE8C8C10 (gibbonPersonIDUpdate), PRIMARY KEY(gibbonFinanceExpenseApproverID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceExpenseLog (gibbonFinanceExpenseLogID INT(16) UNSIGNED AUTO_INCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, action VARCHAR(24) NOT NULL, comment LONGTEXT NOT NULL, gibbonFinanceExpenseID INT(14) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_FFA208A073C3AD9D (gibbonFinanceExpenseID), INDEX IDX_FFA208A0CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonFinanceExpenseLogID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceFee (gibbonFinanceFeeID INT(6) UNSIGNED AUTO_INCREMENT, name VARCHAR(100) NOT NULL, nameShort VARCHAR(6) NOT NULL, description LONGTEXT NOT NULL, active VARCHAR(1) DEFAULT \'Y\' NOT NULL, fee NUMERIC(12, 2) NOT NULL, timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, AcademicYearID INT(3) UNSIGNED, gibbonFinanceFeeCategoryID INT(4) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_7D222CFC71FA7520 (AcademicYearID), INDEX IDX_7D222CFCB05DE109 (gibbonFinanceFeeCategoryID), INDEX IDX_7D222CFCFF59AAB0 (gibbonPersonIDCreator), INDEX IDX_7D222CFCAE8C8C10 (gibbonPersonIDUpdate), PRIMARY KEY(gibbonFinanceFeeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceFeeCategory (gibbonFinanceFeeCategoryID INT(4) UNSIGNED AUTO_INCREMENT, name VARCHAR(100) NOT NULL, nameShort VARCHAR(6) NOT NULL, description LONGTEXT NOT NULL, active VARCHAR(1) NOT NULL, timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_14C5A939FF59AAB0 (gibbonPersonIDCreator), INDEX IDX_14C5A939AE8C8C10 (gibbonPersonIDUpdate), PRIMARY KEY(gibbonFinanceFeeCategoryID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceInvoice (gibbonFinanceInvoiceID INT(14) UNSIGNED AUTO_INCREMENT, invoiceTo VARCHAR(8) DEFAULT \'Family\' NOT NULL, billingScheduleType VARCHAR(12) DEFAULT \'Ad Hoc\' NOT NULL, separated VARCHAR(1) DEFAULT NULL COMMENT \'Has this invoice been separated from its schedule in gibbonFinanceBillingSchedule? Only applies to scheduled invoices. Separation takes place during invoice issueing.\', status VARCHAR(16) DEFAULT \'Pending\' NOT NULL, gibbonFinanceFeeCategoryIDList LONGTEXT DEFAULT NULL, invoiceIssueDate DATE DEFAULT NULL, invoiceDueDate DATE DEFAULT NULL, paidDate DATE DEFAULT NULL, paidAmount NUMERIC(13, 2) DEFAULT NULL COMMENT \'The current running total amount paid to this invoice\', reminderCount INT(3), notes LONGTEXT NOT NULL, `key` VARCHAR(40) NOT NULL, timestampCreator DATETIME DEFAULT NULL, timestampUpdate DATETIME DEFAULT NULL, AcademicYearID INT(3) UNSIGNED, gibbonFinanceInvoiceeID INT(10) UNSIGNED, gibbonFinanceBillingScheduleID INT(6) UNSIGNED, gibbonPaymentID INT(14) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDUpdate INT(10) UNSIGNED, INDEX IDX_B921551771FA7520 (AcademicYearID), INDEX IDX_B9215517739BAD1F (gibbonFinanceInvoiceeID), INDEX IDX_B92155176F4C4787 (gibbonFinanceBillingScheduleID), INDEX IDX_B9215517A0F353A3 (gibbonPaymentID), INDEX IDX_B9215517FF59AAB0 (gibbonPersonIDCreator), INDEX IDX_B9215517AE8C8C10 (gibbonPersonIDUpdate), PRIMARY KEY(gibbonFinanceInvoiceID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceInvoicee (gibbonFinanceInvoiceeID INT(10) UNSIGNED AUTO_INCREMENT, invoiceTo VARCHAR(8) NOT NULL, companyName VARCHAR(100) DEFAULT NULL, companyContact VARCHAR(100) DEFAULT NULL, companyAddress VARCHAR(255) DEFAULT NULL, companyEmail LONGTEXT DEFAULT NULL, companyCCFamily VARCHAR(1) DEFAULT NULL COMMENT \'When company is billed, should family receive a copy?\', companyPhone VARCHAR(20) DEFAULT NULL, companyAll VARCHAR(1) DEFAULT NULL COMMENT \'Should company pay all invoices?.\', gibbonFinanceFeeCategoryIDList LONGTEXT DEFAULT NULL COMMENT \'If companyAll is N, list category IDs for campany to pay here.\', gibbonPersonID INT(10) UNSIGNED, INDEX IDX_6CB0DEC8CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonFinanceInvoiceeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceInvoiceeUpdate (gibbonFinanceInvoiceeUpdateID INT(12) UNSIGNED AUTO_INCREMENT, status VARCHAR(8) DEFAULT \'Pending\' NOT NULL, invoiceTo VARCHAR(8) NOT NULL, companyName VARCHAR(100) DEFAULT NULL, companyContact VARCHAR(100) DEFAULT NULL, companyAddress VARCHAR(255) DEFAULT NULL, companyEmail LONGTEXT DEFAULT NULL, companyCCFamily VARCHAR(1) DEFAULT NULL COMMENT \'When company is billed, should family receive a copy?\', companyPhone VARCHAR(20) DEFAULT NULL, companyAll VARCHAR(1) DEFAULT NULL COMMENT \'Should company pay all invoices?.\', gibbonFinanceFeeCategoryIDList LONGTEXT DEFAULT NULL COMMENT \'If companyAll is N, list category IDs for campany to pay here.\', timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, AcademicYearID INT(3) UNSIGNED, gibbonFinanceInvoiceeID INT(10) UNSIGNED, gibbonPersonIDUpdater INT(10) UNSIGNED, INDEX IDX_848117171FA7520 (AcademicYearID), INDEX IDX_8481171739BAD1F (gibbonFinanceInvoiceeID), INDEX IDX_848117171106375 (gibbonPersonIDUpdater), INDEX gibbonInvoiceeIndex (gibbonFinanceInvoiceeID, AcademicYearID), PRIMARY KEY(gibbonFinanceInvoiceeUpdateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFinanceInvoiceFee (gibbonFinanceInvoiceFeeID INT(15) UNSIGNED AUTO_INCREMENT, feeType VARCHAR(12) DEFAULT \'Ad Hoc\' NOT NULL, separated VARCHAR(1) DEFAULT NULL COMMENT \'Has this fee been separated from its parent in gibbonFinanceFee? Only applies to Standard fees. Separation takes place during invoice issueing.\', name VARCHAR(100) DEFAULT NULL, description LONGTEXT DEFAULT NULL, fee NUMERIC(12, 2) DEFAULT NULL, sequenceNumber INT(10), gibbonFinanceInvoiceID INT(14) UNSIGNED, gibbonFinanceFeeID INT(6) UNSIGNED, gibbonFinanceFeeCategoryID INT(6) UNSIGNED, INDEX IDX_3CC82E56F51FBF6 (gibbonFinanceInvoiceID), INDEX IDX_3CC82E569B02DC4A (gibbonFinanceFeeID), INDEX IDX_3CC82E56B05DE109 (gibbonFinanceFeeCategoryID), PRIMARY KEY(gibbonFinanceInvoiceFeeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonFirstAid (gibbonFirstAidID INT(10) UNSIGNED AUTO_INCREMENT, description LONGTEXT NOT NULL, actionTaken LONGTEXT NOT NULL, followUp LONGTEXT NOT NULL, date DATE NOT NULL, timeIn TIME NOT NULL, timeOut TIME DEFAULT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPersonIDPatient INT(10) UNSIGNED, gibbonCourseClassID INT(8) UNSIGNED, gibbonPersonIDFirstAider INT(10) UNSIGNED, AcademicYearID INT(3) UNSIGNED, INDEX IDX_ABF0052759859738 (gibbonPersonIDPatient), INDEX IDX_ABF00527B67991E (gibbonCourseClassID), INDEX IDX_ABF0052722759506 (gibbonPersonIDFirstAider), INDEX IDX_ABF0052771FA7520 (AcademicYearID), PRIMARY KEY(gibbonFirstAidID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonGroup (gibbonGroupID INT(8) UNSIGNED AUTO_INCREMENT, name VARCHAR(30) NOT NULL, timestampCreated DATETIME DEFAULT NULL, timestampUpdated DATETIME DEFAULT CURRENT_TIMESTAMP, gibbonPersonIDOwner INT(10) UNSIGNED, AcademicYearID INT(3) UNSIGNED, INDEX IDX_FAE2DDF3659378D6 (gibbonPersonIDOwner), INDEX IDX_FAE2DDF371FA7520 (AcademicYearID), PRIMARY KEY(gibbonGroupID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonGroupPerson (gibbonGroupPersonID INT(10) UNSIGNED AUTO_INCREMENT, gibbonGroupID INT(8) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_15367BAAD62085CF (gibbonGroupID), INDEX IDX_15367BAACC6782D6 (gibbonPersonID), UNIQUE INDEX gibbonGroupID (gibbonGroupID, gibbonPersonID), PRIMARY KEY(gibbonGroupPersonID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonHook (gibbonHookID INT(4) UNSIGNED AUTO_INCREMENT, name VARCHAR(50) NOT NULL, type VARCHAR(20) DEFAULT NULL, options LONGTEXT NOT NULL, gibbonModuleID INT(4) UNSIGNED, INDEX IDX_5418FD5ECB86AD4B (gibbonModuleID), UNIQUE INDEX name (name, type), PRIMARY KEY(gibbonHookID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql("CREATE TABLE gibbonImportHistory (id INT(10) AUTO_INCREMENT, performed_by INT(10) UNSIGNED, import_type VARCHAR(32) NOT NULL, last_modified DATETIME NOT NULL, column_order LONGTEXT NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_7DB5226499EB8EA2 (performed_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;");
        $this->addSql('CREATE TABLE gibbonInternalAssessmentColumn (gibbonInternalAssessmentColumnID INT(10) UNSIGNED AUTO_INCREMENT, groupingID INT(8) UNSIGNED, name VARCHAR(20) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, attachment VARCHAR(255) NOT NULL, attainment VARCHAR(1) DEFAULT \'Y\' NOT NULL, effort VARCHAR(1) DEFAULT \'Y\' NOT NULL, comment VARCHAR(1) DEFAULT \'Y\' NOT NULL, uploadedResponse VARCHAR(1) DEFAULT \'N\' NOT NULL, complete VARCHAR(1) NOT NULL, completeDate DATE DEFAULT NULL, viewableStudents VARCHAR(1) NOT NULL, viewableParents VARCHAR(1) NOT NULL, gibbonCourseClassID INT(8) UNSIGNED, idAttainment INT(5) UNSIGNED, idEffort INT(5) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDLastEdit INT(10) UNSIGNED, INDEX IDX_E0A1D88AB67991E (gibbonCourseClassID), INDEX IDX_E0A1D88A2C639785 (idAttainment), INDEX IDX_E0A1D88AD395ACF8 (idEffort), INDEX IDX_E0A1D88AFF59AAB0 (gibbonPersonIDCreator), INDEX IDX_E0A1D88A519966BA (gibbonPersonIDLastEdit), PRIMARY KEY(gibbonInternalAssessmentColumnID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonInternalAssessmentEntry (gibbonInternalAssessmentEntryID INT(12) UNSIGNED AUTO_INCREMENT, attainmentValue VARCHAR(10) DEFAULT NULL, attainmentDescriptor VARCHAR(100) DEFAULT NULL, effortValue VARCHAR(10) DEFAULT NULL, effortDescriptor VARCHAR(100) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, response LONGTEXT DEFAULT NULL, gibbonInternalAssessmentColumnID INT(10) UNSIGNED, gibbonPersonIDStudent INT(10) UNSIGNED, gibbonPersonIDLastEdit INT(10) UNSIGNED, INDEX IDX_B09C6F558B7A9BC (gibbonInternalAssessmentColumnID), INDEX IDX_B09C6F5F47CEFE0 (gibbonPersonIDStudent), INDEX IDX_B09C6F5519966BA (gibbonPersonIDLastEdit), PRIMARY KEY(gibbonInternalAssessmentEntryID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonLanguage (gibbonLanguageID INT(4) UNSIGNED AUTO_INCREMENT, name VARCHAR(30) NOT NULL, PRIMARY KEY(gibbonLanguageID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonLog (gibbonLogID INT(16) UNSIGNED AUTO_INCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, title VARCHAR(50) NOT NULL, serialisedArray LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ip VARCHAR(15) DEFAULT NULL, gibbonModuleID INT(4) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, AcademicYearID INT(3) UNSIGNED, INDEX IDX_C0122755CB86AD4B (gibbonModuleID), INDEX IDX_C0122755CC6782D6 (gibbonPersonID), INDEX IDX_C012275571FA7520 (AcademicYearID), PRIMARY KEY(gibbonLogID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonMarkbookColumn` (
  `gibbonMarkbookColumnID` int(10) UNSIGNED AUTO_INCREMENT,
  `groupingID` int(8) UNSIGNED DEFAULT NULL,
  `type` CHAR(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` CHAR(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `date` date DEFAULT NULL,
  `sequenceNumber` int(3) UNSIGNED DEFAULT NULL,
  `attachment` CHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `attainment` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `attainmentWeighting` decimal(5,2) DEFAULT NULL,
  `attainmentRaw` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'N\',
  `attainmentRawMax` decimal(8,2) DEFAULT NULL,
  `effort` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `comment` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `uploadedResponse` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `complete` CHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  `completeDate` date DEFAULT NULL,
  `viewableStudents` CHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  `viewableParents` CHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  `gibbonCourseClassID` int(8) UNSIGNED NOT NULL,
  `gibbonHookID` int(4) UNSIGNED DEFAULT NULL,
  `gibbonUnitID` int(10) UNSIGNED DEFAULT NULL,
  `gibbonPlannerEntryID` int(14) UNSIGNED DEFAULT NULL,
  `gibbonAcademicYearTermID` int(5) UNSIGNED DEFAULT NULL,
  `idAttainment` int(5) UNSIGNED DEFAULT NULL,
  `idEffort` int(5) UNSIGNED DEFAULT NULL,
  `gibbonRubricIDAttainment` int(8) UNSIGNED DEFAULT NULL,
  `gibbonRubricIDEffort` int(8) UNSIGNED DEFAULT NULL,
  `gibbonPersonIDCreator` int(10) UNSIGNED DEFAULT NULL,
  `gibbonPersonIDLastEdit` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonMarkbookColumnID`),
  UNIQUE KEY `nameCourseClass` (`name`,`gibbonCourseClassID`),
  KEY `IDX_AA57806EF6E7C959` (`gibbonHookID`),
  KEY `IDX_AA57806E46DE4A3D` (`gibbonUnitID`),
  KEY `IDX_AA57806EFE417281` (`gibbonPlannerEntryID`),
  KEY `IDX_AA57806E88C7C454` (`gibbonAcademicYearTermID`),
  KEY `IDX_AA57806E2C639785` (`idAttainment`),
  KEY `IDX_AA57806ED395ACF8` (`idEffort`),
  KEY `IDX_AA57806E2151BB77` (`gibbonRubricIDAttainment`),
  KEY `IDX_AA57806EBA294907` (`gibbonRubricIDEffort`),
  KEY `IDX_AA57806EFF59AAB0` (`gibbonPersonIDCreator`),
  KEY `IDX_AA57806E519966BA` (`gibbonPersonIDLastEdit`),
  KEY `gibbonCourseClassID` (`gibbonCourseClassID`),
  KEY `completeDate` (`completeDate`),
  KEY `complete` (`complete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonMarkbookEntry` (
  `gibbonMarkbookEntryID` int(12) UNSIGNED AUTO_INCREMENT,
  `modifiedAssessment` CHAR(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attainmentValue` CHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attainmentValueRaw` CHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attainmentDescriptor` CHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attainmentConcern` CHAR(1) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT \'`P` denotes that student has exceed their personal target\',
  `effortValue` CHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `effortDescriptor` CHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `effortConcern` CHAR(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `response` CHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gibbonMarkbookColumnID` int(10) UNSIGNED DEFAULT NULL,
  `gibbonPersonIDStudent` int(10) UNSIGNED DEFAULT NULL,
  `gibbonPersonIDLastEdit` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonMarkbookEntryID`),
  UNIQUE KEY `columnStudent` (`gibbonMarkbookColumnID`,`gibbonPersonIDStudent`),
  KEY `IDX_22F46391519966BA` (`gibbonPersonIDLastEdit`),
  KEY `gibbonPersonIDStudent` (`gibbonPersonIDStudent`),
  KEY `gibbonMarkbookColumnID` (`gibbonMarkbookColumnID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMarkbookTarget (gibbonMarkbookTargetID INT(14) UNSIGNED AUTO_INCREMENT, gibbonCourseClassID INT(8) UNSIGNED, gibbonPersonIDStudent INT(10) UNSIGNED, gibbonScaleGradeID INT(7) UNSIGNED, INDEX IDX_916B28ECB67991E (gibbonCourseClassID), INDEX IDX_916B28ECF47CEFE0 (gibbonPersonIDStudent), INDEX IDX_916B28EC5E440573 (gibbonScaleGradeID), UNIQUE INDEX coursePerson (gibbonCourseClassID, gibbonPersonIDStudent), PRIMARY KEY(gibbonMarkbookTargetID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMarkbookWeight (gibbonMarkbookWeightID INT(10) UNSIGNED AUTO_INCREMENT, type VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, reportable VARCHAR(1) DEFAULT \'Y\' NOT NULL, calculate VARCHAR(4) DEFAULT \'year\' NOT NULL, weighting NUMERIC(5, 2) NOT NULL, gibbonCourseClassID INT(8) UNSIGNED, INDEX IDX_D0C95251B67991E (gibbonCourseClassID), PRIMARY KEY(gibbonMarkbookWeightID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMedicalCondition (gibbonMedicalConditionID INT(4) UNSIGNED AUTO_INCREMENT, name VARCHAR(80) NOT NULL, UNIQUE INDEX name (name), PRIMARY KEY(gibbonMedicalConditionID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMessenger (gibbonMessengerID INT(12) UNSIGNED AUTO_INCREMENT, email VARCHAR(1) DEFAULT \'N\' NOT NULL, messageWall VARCHAR(1) DEFAULT \'N\' NOT NULL, messageWall_date1 DATE DEFAULT NULL, messageWall_date2 DATE DEFAULT NULL, messageWall_date3 DATE DEFAULT NULL, sms VARCHAR(1) DEFAULT \'N\' NOT NULL, subject VARCHAR(60) NOT NULL, body LONGTEXT NOT NULL, timestamp DATETIME DEFAULT NULL, emailReport LONGTEXT NOT NULL, emailReceipt VARCHAR(1) DEFAULT NULL, emailReceiptText LONGTEXT DEFAULT NULL, smsReport LONGTEXT NOT NULL, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_C7127C4CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonMessengerID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMessengerCannedResponse (gibbonMessengerCannedResponseID INT(10) UNSIGNED AUTO_INCREMENT, subject VARCHAR(30) NOT NULL, body LONGTEXT NOT NULL, timestampCreator DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_C83786BFFF59AAB0 (gibbonPersonIDCreator), PRIMARY KEY(gibbonMessengerCannedResponseID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMessengerReceipt (gibbonMessengerReceiptID INT(14) UNSIGNED AUTO_INCREMENT, targetType VARCHAR(16) NOT NULL, targetID VARCHAR(30) NOT NULL, contactType VARCHAR(5) DEFAULT NULL, contactDetail VARCHAR(255) DEFAULT NULL, `key` VARCHAR(40) DEFAULT NULL, confirmed VARCHAR(1) DEFAULT NULL, confirmedTimestamp DATETIME DEFAULT NULL, gibbonMessengerID INT(12) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_30BB77081B4FC86A (gibbonMessengerID), INDEX IDX_30BB7708CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonMessengerReceiptID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonMessengerTarget (gibbonMessengerTargetID INT(14) UNSIGNED AUTO_INCREMENT, type VARCHAR(16) DEFAULT NULL, id VARCHAR(30) NOT NULL, parents VARCHAR(1) DEFAULT \'N\' NOT NULL, students VARCHAR(1) DEFAULT \'N\' NOT NULL, staff VARCHAR(1) DEFAULT \'N\' NOT NULL, gibbonMessengerID INT(12) UNSIGNED, INDEX IDX_62C2BBE11B4FC86A (gibbonMessengerID), PRIMARY KEY(gibbonMessengerTargetID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonOutcome` (
  `gibbonOutcomeID` int(8) UNSIGNED AUTO_INCREMENT,
  `name` CHAR(100) COLLATE utf8_unicode_ci NOT NULL,
  `nameShort` CHAR(14) COLLATE utf8_unicode_ci NOT NULL,
  `category` CHAR(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `active` CHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  `scope` CHAR(16) COLLATE utf8_unicode_ci NOT NULL,
  `gibbonYearGroupIDList` CHAR(255) COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:simple_array)\',
  `gibbonDepartmentID` int(4) UNSIGNED DEFAULT NULL,
  `gibbonPersonIDCreator` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonOutcomeID`),
  UNIQUE KEY `nameDepartment` (`name`,`gibbonDepartmentID`),
  UNIQUE KEY `nameShortDepartment` (`nameShort`,`gibbonDepartmentID`),
  KEY `IDX_307340756DFE7E92` (`gibbonDepartmentID`),
  KEY `IDX_30734075FF59AAB0` (`gibbonPersonIDCreator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPayment (gibbonPaymentID INT(14) UNSIGNED AUTO_INCREMENT, foreignTable VARCHAR(50) NOT NULL, foreignTableID INT(14) UNSIGNED, type VARCHAR(16) DEFAULT \'Online\' NOT NULL, status VARCHAR(8) DEFAULT \'Complete\' NOT NULL COMMENT \'Complete means paid in one go, partial is part of a set of payments, and final is last in a set of payments.\', amount NUMERIC(13, 2) NOT NULL, gateway VARCHAR(6) DEFAULT NULL, onlineTransactionStatus VARCHAR(12) DEFAULT NULL, paymentToken VARCHAR(50) DEFAULT NULL, paymentPayerID VARCHAR(50) DEFAULT NULL, paymentTransactionID VARCHAR(50) DEFAULT NULL, paymentReceiptID VARCHAR(50) DEFAULT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_6DE7A9BACC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonPaymentID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPersonMedical (gibbonPersonMedicalID INT(10) UNSIGNED AUTO_INCREMENT, bloodType VARCHAR(3) NOT NULL, longTermMedication VARCHAR(1) NOT NULL, longTermMedicationDetails LONGTEXT NOT NULL, tetanusWithin10Years VARCHAR(1) NOT NULL, comment LONGTEXT NOT NULL, gibbonPersonID INT(10) UNSIGNED, INDEX gibbonPersonID (gibbonPersonID), PRIMARY KEY(gibbonPersonMedicalID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPersonMedicalCondition (gibbonPersonMedicalConditionID INT(12) UNSIGNED AUTO_INCREMENT, name VARCHAR(100) NOT NULL, triggers VARCHAR(255) NOT NULL, reaction VARCHAR(255) NOT NULL, response VARCHAR(255) NOT NULL, medication VARCHAR(255) NOT NULL, lastEpisode DATE DEFAULT NULL, lastEpisodeTreatment VARCHAR(255) NOT NULL, comment LONGTEXT NOT NULL, gibbonPersonMedicalID INT(10) UNSIGNED, id INT(3) UNSIGNED, INDEX IDX_9F35C9A7891EFB5B (id), INDEX gibbonPersonMedicalID (gibbonPersonMedicalID), PRIMARY KEY(gibbonPersonMedicalConditionID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPersonMedicalConditionUpdate (gibbonPersonMedicalConditionUpdateID INT(14) UNSIGNED AUTO_INCREMENT, name VARCHAR(80) NOT NULL, triggers VARCHAR(255) NOT NULL, reaction VARCHAR(255) NOT NULL, response VARCHAR(255) NOT NULL, medication VARCHAR(255) NOT NULL, lastEpisode DATE DEFAULT NULL, lastEpisodeTreatment VARCHAR(255) NOT NULL, comment LONGTEXT NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPersonMedicalUpdateID INT(12) UNSIGNED, gibbonPersonMedicalConditionID INT(12) UNSIGNED, gibbonPersonMedicalID INT(10) UNSIGNED, id INT(3) UNSIGNED, gibbonPersonIDUpdater INT(10) UNSIGNED, INDEX IDX_4E2F6CEC41D19174 (gibbonPersonMedicalUpdateID), INDEX IDX_4E2F6CEC122DAC35 (gibbonPersonMedicalConditionID), INDEX IDX_4E2F6CEC65737DEB (gibbonPersonMedicalID), INDEX IDX_4E2F6CEC891EFB5B (id), INDEX IDX_4E2F6CEC71106375 (gibbonPersonIDUpdater), PRIMARY KEY(gibbonPersonMedicalConditionUpdateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPersonMedicalSymptoms (gibbonPersonMedicalSymptomsID INT(14) UNSIGNED AUTO_INCREMENT, symptoms LONGTEXT NOT NULL, date DATE NOT NULL, timestampTaken DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPersonID INT(10) UNSIGNED, gibbonPersonIDTaker INT(10) UNSIGNED, INDEX IDX_2C6BF3A5CC6782D6 (gibbonPersonID), INDEX IDX_2C6BF3A511A14ED (gibbonPersonIDTaker), PRIMARY KEY(gibbonPersonMedicalSymptomsID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPersonMedicalUpdate (gibbonPersonMedicalUpdateID INT(12) UNSIGNED AUTO_INCREMENT, status VARCHAR(8) DEFAULT \'Pending\' NOT NULL, bloodType VARCHAR(3) NOT NULL, longTermMedication VARCHAR(1) NOT NULL, longTermMedicationDetails LONGTEXT NOT NULL, tetanusWithin10Years VARCHAR(1) NOT NULL, comment LONGTEXT NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, AcademicYearID INT(3) UNSIGNED, gibbonPersonMedicalID INT(10) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonPersonIDUpdater INT(10) UNSIGNED, INDEX IDX_EEB6690471FA7520 (AcademicYearID), INDEX IDX_EEB6690465737DEB (gibbonPersonMedicalID), INDEX IDX_EEB66904CC6782D6 (gibbonPersonID), INDEX IDX_EEB6690471106375 (gibbonPersonIDUpdater), INDEX gibbonMedicalIndex (gibbonPersonID, gibbonPersonMedicalID, AcademicYearID), PRIMARY KEY(gibbonPersonMedicalUpdateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPersonUpdate (gibbonPersonUpdateID INT(12) UNSIGNED AUTO_INCREMENT, status VARCHAR(8) DEFAULT \'Pending\' NOT NULL, title VARCHAR(5) NOT NULL, surname VARCHAR(60) NOT NULL, firstName VARCHAR(60) NOT NULL, preferredName VARCHAR(60) NOT NULL, officialName VARCHAR(150) NOT NULL, nameInCharacters VARCHAR(60) NOT NULL, dob DATE DEFAULT NULL, email VARCHAR(75) DEFAULT NULL, emailAlternate VARCHAR(75) DEFAULT NULL, address1 LONGTEXT NOT NULL, address1District VARCHAR(255) NOT NULL, address1Country VARCHAR(255) NOT NULL, address2 LONGTEXT NOT NULL, address2district VARCHAR(255) NOT NULL, address2country VARCHAR(255) NOT NULL, phone1type VARCHAR(6) NOT NULL, phone1CountryCode VARCHAR(7) NOT NULL, phone1 VARCHAR(20) NOT NULL, phone2type VARCHAR(6) NOT NULL, phone2CountryCode VARCHAR(7) NOT NULL, phone2 VARCHAR(20) NOT NULL, phone3type VARCHAR(6) NOT NULL, phone3CountryCode VARCHAR(7) NOT NULL, phone3 VARCHAR(20) NOT NULL, phone4type VARCHAR(6) NOT NULL, phone4CountryCode VARCHAR(7) NOT NULL, phone4 VARCHAR(20) NOT NULL, languageFirst VARCHAR(30) NOT NULL, languageSecond VARCHAR(30) NOT NULL, languageThird VARCHAR(30) NOT NULL, countryOfBirth VARCHAR(30) NOT NULL, ethnicity VARCHAR(255) NOT NULL, citizenship1 VARCHAR(255) NOT NULL, citizenship1passport VARCHAR(30) NOT NULL, citizenship2 VARCHAR(255) NOT NULL, citizenship2passport VARCHAR(30) NOT NULL, religion VARCHAR(30) NOT NULL, nationalIDCardCountry VARCHAR(30) NOT NULL, nationalIDCardNumber VARCHAR(30) NOT NULL, residencyStatus VARCHAR(255) NOT NULL, visaExpiryDate DATE DEFAULT NULL, profession VARCHAR(90) DEFAULT NULL, employer VARCHAR(90) DEFAULT NULL, jobTitle VARCHAR(90) DEFAULT NULL, emergency1name VARCHAR(90) DEFAULT NULL, emergency1number1 VARCHAR(30) DEFAULT NULL, emergency1number2 VARCHAR(30) DEFAULT NULL, emergency1relationship VARCHAR(30) DEFAULT NULL, emergency2name VARCHAR(90) DEFAULT NULL, emergency2number1 VARCHAR(30) DEFAULT NULL, emergency2number2 VARCHAR(30) DEFAULT NULL, emergency2relationship VARCHAR(30) DEFAULT NULL, vehicleRegistration VARCHAR(20) NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, privacy LONGTEXT DEFAULT NULL, fields LONGTEXT NOT NULL COMMENT \'Serialised array of custom field values\', AcademicYearID INT(3) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonPersonIDUpdater INT(10) UNSIGNED, INDEX IDX_D3CBB18C71FA7520 (AcademicYearID), INDEX IDX_D3CBB18CCC6782D6 (gibbonPersonID), INDEX IDX_D3CBB18C71106375 (gibbonPersonIDUpdater), INDEX gibbonPersonIndex (gibbonPersonID, AcademicYearID), PRIMARY KEY(gibbonPersonUpdateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntry (gibbonPlannerEntryID INT(14) UNSIGNED AUTO_INCREMENT, date DATE DEFAULT NULL, timeStart TIME DEFAULT NULL, timeEnd TIME DEFAULT NULL, name VARCHAR(50) NOT NULL, summary VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, teachersNotes LONGTEXT NOT NULL, homework VARCHAR(1) DEFAULT \'N\' NOT NULL, homeworkDueDateTime DATETIME DEFAULT NULL, homeworkDetails LONGTEXT NOT NULL, homeworkSubmission VARCHAR(1) NOT NULL, homeworkSubmissionDateOpen DATE DEFAULT NULL, homeworkSubmissionDrafts VARCHAR(1) DEFAULT NULL, homeworkSubmissionType VARCHAR(10) NOT NULL, homeworkSubmissionRequired VARCHAR(10) DEFAULT \'Optional\', homeworkCrowdAssess VARCHAR(1) NOT NULL, homeworkCrowdAssessOtherTeachersRead VARCHAR(1) NOT NULL, homeworkCrowdAssessOtherParentsRead VARCHAR(1) NOT NULL, homeworkCrowdAssessClassmatesParentsRead VARCHAR(1) NOT NULL, homeworkCrowdAssessSubmitterParentsRead VARCHAR(1) NOT NULL, homeworkCrowdAssessOtherStudentsRead VARCHAR(1) NOT NULL, homeworkCrowdAssessClassmatesRead VARCHAR(1) NOT NULL, viewableStudents VARCHAR(1) DEFAULT \'Y\' NOT NULL, viewableParents VARCHAR(1) DEFAULT \'N\' NOT NULL, gibbonCourseClassID INT(8) UNSIGNED, gibbonUnitID INT(10) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDLastEdit INT(10) UNSIGNED, INDEX IDX_B35E3CEE46DE4A3D (gibbonUnitID), INDEX IDX_B35E3CEEFF59AAB0 (gibbonPersonIDCreator), INDEX IDX_B35E3CEE519966BA (gibbonPersonIDLastEdit), INDEX gibbonCourseClassID (gibbonCourseClassID), PRIMARY KEY(gibbonPlannerEntryID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntryDiscuss (gibbonPlannerEntryDiscussID INT(16) UNSIGNED AUTO_INCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, comment LONGTEXT NOT NULL, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonPlannerEntryDiscussIDReplyTo INT(16) UNSIGNED, INDEX IDX_A2D5383EFE417281 (gibbonPlannerEntryID), INDEX IDX_A2D5383ECC6782D6 (gibbonPersonID), INDEX IDX_A2D5383E18B0DB2F (gibbonPlannerEntryDiscussIDReplyTo), PRIMARY KEY(gibbonPlannerEntryDiscussID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntryGuest (gibbonPlannerEntryGuestID INT(16) UNSIGNED AUTO_INCREMENT, role VARCHAR(16) NOT NULL, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_E9A57557FE417281 (gibbonPlannerEntryID), INDEX IDX_E9A57557CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonPlannerEntryGuestID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntryHomework (gibbonPlannerEntryHomeworkID INT(16) UNSIGNED AUTO_INCREMENT, type VARCHAR(4) NOT NULL, version VARCHAR(5) NOT NULL, status VARCHAR(9) NOT NULL, location VARCHAR(255) DEFAULT NULL, count INT(1), timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_ED6C8A2EFE417281 (gibbonPlannerEntryID), INDEX IDX_ED6C8A2ECC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonPlannerEntryHomeworkID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntryOutcome (gibbonPlannerEntryOutcomeID INT(16) UNSIGNED AUTO_INCREMENT, sequenceNumber INT(4), content LONGTEXT NOT NULL, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonOutcomeID INT(8) UNSIGNED, INDEX IDX_57C2DE1CFE417281 (gibbonPlannerEntryID), INDEX IDX_57C2DE1C35479F6A (gibbonOutcomeID), PRIMARY KEY(gibbonPlannerEntryOutcomeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntryStudentHomework (gibbonPlannerEntryStudentHomeworkID INT(14) UNSIGNED AUTO_INCREMENT, homeworkDueDateTime DATETIME NOT NULL, homeworkDetails LONGTEXT NOT NULL, homeworkComplete VARCHAR(1) DEFAULT \'N\' NOT NULL, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_F458CB62FE417281 (gibbonPlannerEntryID), INDEX IDX_F458CB62CC6782D6 (gibbonPersonID), INDEX gibbonPlannerEntryID (gibbonPlannerEntryID, gibbonPersonID), PRIMARY KEY(gibbonPlannerEntryStudentHomeworkID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerEntryStudentTracker (gibbonPlannerEntryStudentTrackerID INT(16) UNSIGNED AUTO_INCREMENT, homeworkComplete DATETIME NOT NULL, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_936AA4E7FE417281 (gibbonPlannerEntryID), INDEX IDX_936AA4E7CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonPlannerEntryStudentTrackerID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonPlannerParentWeeklyEmailSummary (gibbonPlannerParentWeeklyEmailSummaryID INT(14) UNSIGNED AUTO_INCREMENT, weekOfYear INT(2), `key` VARCHAR(40) NOT NULL, confirmed VARCHAR(1) DEFAULT \'N\' NOT NULL, AcademicYearID INT(3) UNSIGNED, gibbonPersonIDParent INT(10) UNSIGNED, gibbonPersonIDStudent INT(10) UNSIGNED, INDEX IDX_2E7C18B771FA7520 (AcademicYearID), INDEX IDX_2E7C18B7B27D927 (gibbonPersonIDParent), INDEX IDX_2E7C18B7F47CEFE0 (gibbonPersonIDStudent), UNIQUE INDEX `key` (`key`), PRIMARY KEY(gibbonPlannerParentWeeklyEmailSummaryID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonResource (gibbonResourceID INT(14) UNSIGNED AUTO_INCREMENT, name VARCHAR(60) NOT NULL, description LONGTEXT NOT NULL, gibbonYearGroupIDList VARCHAR(255) NOT NULL, type VARCHAR(4) NOT NULL, category VARCHAR(255) NOT NULL, purpose VARCHAR(255) NOT NULL, tags LONGTEXT NOT NULL, content LONGTEXT NOT NULL, timestamp DATETIME DEFAULT NULL, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_E9941D14CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonResourceID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonResourceTag (gibbonResourceTagID INT(12) UNSIGNED AUTO_INCREMENT, tag VARCHAR(100) NOT NULL, count INT(6), INDEX tag_2 (tag), UNIQUE INDEX tag (tag), PRIMARY KEY(gibbonResourceTagID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonRubric (gibbonRubricID INT(8) UNSIGNED AUTO_INCREMENT, name VARCHAR(50) NOT NULL, category VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, active VARCHAR(1) NOT NULL, scope VARCHAR(10) NOT NULL, gibbonYearGroupIDList VARCHAR(255) NOT NULL, gibbonDepartmentID INT(4) UNSIGNED, `gibbonScaleID` INT(5) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_AFE9B66C6DFE7E92 (gibbonDepartmentID), INDEX IDX_AFE9B66C5F72BC3 (`gibbonScaleID`), INDEX IDX_AFE9B66CFF59AAB0 (gibbonPersonIDCreator), PRIMARY KEY(gibbonRubricID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonRubricCell (gibbonRubricCellID INT(11) UNSIGNED AUTO_INCREMENT, contents LONGTEXT NOT NULL, gibbonRubricID INT(8) UNSIGNED, gibbonRubricColumnID INT(9) UNSIGNED, gibbonRubricRowID INT(9) UNSIGNED, INDEX gibbonRubricID (gibbonRubricID), INDEX gibbonRubricColumnID (gibbonRubricColumnID), INDEX gibbonRubricRowID (gibbonRubricRowID), PRIMARY KEY(gibbonRubricCellID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonRubricColumn (gibbonRubricColumnID INT(9) UNSIGNED AUTO_INCREMENT, title VARCHAR(20) NOT NULL, sequenceNumber INT(2), visualise VARCHAR(1) DEFAULT \'Y\' NOT NULL, gibbonRubricID INT(8) UNSIGNED, gibbonScaleGradeID INT(7) UNSIGNED, INDEX IDX_E31DA4325E440573 (gibbonScaleGradeID), INDEX gibbonRubricID (gibbonRubricID), PRIMARY KEY(gibbonRubricColumnID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonRubricEntry (gibbonRubricEntry INT(14) UNSIGNED AUTO_INCREMENT, contextDBTable VARCHAR(255) NOT NULL COMMENT \'Which database table is this entry related to?\', contextDBTableID INT(20) UNSIGNED, gibbonRubricID INT(8) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonRubricCellID INT(11) UNSIGNED, INDEX gibbonRubricID (gibbonRubricID), INDEX gibbonPersonID (gibbonPersonID), INDEX gibbonRubricCellID (gibbonRubricCellID), INDEX contextDBTable (contextDBTable), INDEX contextDBTableID (contextDBTableID), PRIMARY KEY(gibbonRubricEntry)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonRubricRow (gibbonRubricRowID INT(9) UNSIGNED AUTO_INCREMENT, title VARCHAR(40) NOT NULL, sequenceNumber INT(2), gibbonRubricID INT(8) UNSIGNED, gibbonOutcomeID INT(8) UNSIGNED, INDEX IDX_96F9D13235479F6A (gibbonOutcomeID), INDEX gibbonRubricID (gibbonRubricID), PRIMARY KEY(gibbonRubricRowID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonStaff` (
  `gibbonStaffID` int(10) UNSIGNED AUTO_INCREMENT,
  `type` CHAR(20) COLLATE utf8_unicode_ci NOT NULL,
  `initials` CHAR(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jobTitle` CHAR(100) COLLATE utf8_unicode_ci NOT NULL,
  `smartWorkflowHelp` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Y\',
  `firstAidQualified` CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'N\',
  `firstAidExpiry` date DEFAULT NULL,
  `countryOfOrigin` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `qualifications` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `biography` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `biographicalGrouping` CHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT \'Used for group staff when creating a staff directory.\',
  `biographicalGroupingPriority` int(3) DEFAULT NULL,
  `gibbonPersonID` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonStaffID`),
  UNIQUE KEY `staff` (`gibbonPersonID`),
  UNIQUE KEY `initials` (`initials`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffAbsence (gibbonStaffAbsenceID INT(14) UNSIGNED AUTO_INCREMENT, reason VARCHAR(60) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, commentConfidential LONGTEXT DEFAULT NULL, status VARCHAR(16) DEFAULT \'Approved\', coverageRequired VARCHAR(1) DEFAULT \'N\' NOT NULL, timestampApproval DATETIME DEFAULT NULL, notesApproval LONGTEXT DEFAULT NULL, timestampCreator DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, notificationSent VARCHAR(1) DEFAULT \'N\' NOT NULL, notificationList LONGTEXT DEFAULT NULL, gibbonCalendarEventID LONGTEXT DEFAULT NULL, gibbonStaffAbsenceTypeID INT(6) UNSIGNED, AcademicYearID INT(3) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonPersonIDApproval INT(10) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonGroupID INT(8) UNSIGNED, UNIQUE INDEX UNIQ_2FE5FEF78A15C624 (gibbonStaffAbsenceTypeID), UNIQUE INDEX UNIQ_2FE5FEF771FA7520 (AcademicYearID), UNIQUE INDEX UNIQ_2FE5FEF7CC6782D6 (gibbonPersonID), UNIQUE INDEX UNIQ_2FE5FEF79794905 (gibbonPersonIDApproval), UNIQUE INDEX UNIQ_2FE5FEF7FF59AAB0 (gibbonPersonIDCreator), UNIQUE INDEX UNIQ_2FE5FEF7D62085CF (gibbonGroupID), PRIMARY KEY(gibbonStaffAbsenceID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffAbsenceDate (gibbonStaffAbsenceDateID INT(14) UNSIGNED AUTO_INCREMENT, date DATE DEFAULT NULL, allDay VARCHAR(1) DEFAULT \'Y\', timeStart TIME DEFAULT NULL, timeEnd TIME DEFAULT NULL, value NUMERIC(2, 1) DEFAULT \'1\' NOT NULL, gibbonStaffAbsenceID INT(14) UNSIGNED, UNIQUE INDEX UNIQ_269FD270102BE4BE (gibbonStaffAbsenceID), PRIMARY KEY(gibbonStaffAbsenceDateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffApplicationForm (gibbonStaffApplicationFormID INT(12) UNSIGNED AUTO_INCREMENT, surname VARCHAR(60) DEFAULT NULL, firstName VARCHAR(60) DEFAULT NULL, preferredName VARCHAR(60) DEFAULT NULL, officialName VARCHAR(150) DEFAULT NULL, nameInCharacters VARCHAR(60) DEFAULT NULL, gender VARCHAR(12) DEFAULT NULL, status VARCHAR(12) DEFAULT \'Pending\' NOT NULL, dob DATE DEFAULT NULL, email VARCHAR(75) DEFAULT NULL, homeAddress LONGTEXT DEFAULT NULL, homeAddressDistrict VARCHAR(255) DEFAULT NULL, homeAddressCountry VARCHAR(255) DEFAULT NULL, phone1Type VARCHAR(6) DEFAULT NULL, phone1CountryCode VARCHAR(7) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, countryOfBirth VARCHAR(30) DEFAULT NULL, citizenship1 VARCHAR(255) DEFAULT NULL, citizenship1Passport VARCHAR(30) DEFAULT NULL, nationalIDCardNumber VARCHAR(30) DEFAULT NULL, residencyStatus VARCHAR(255) DEFAULT NULL, visaExpiryDate DATE DEFAULT NULL, languageFirst VARCHAR(30) DEFAULT NULL, languageSecond VARCHAR(30) DEFAULT NULL, languageThird VARCHAR(30) DEFAULT NULL, agreement VARCHAR(1) DEFAULT NULL, timestamp DATETIME DEFAULT NULL, priority INT(1), milestones LONGTEXT NOT NULL, notes LONGTEXT NOT NULL, dateStart DATE DEFAULT NULL, questions LONGTEXT NOT NULL, fields LONGTEXT NOT NULL COMMENT \'Serialised array of custom field values\', referenceEmail1 VARCHAR(100) NOT NULL, referenceEmail2 VARCHAR(100) NOT NULL, gibbonStaffJobOpeningID INT(10) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_48D734D9B060E48C (gibbonStaffJobOpeningID), INDEX IDX_48D734D9CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonStaffApplicationFormID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffApplicationFormFile (gibbonStaffApplicationFormFileID INT(14) UNSIGNED AUTO_INCREMENT, name VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, gibbonStaffApplicationFormID INT(12) UNSIGNED, INDEX IDX_E2EDB80B609DA10E (gibbonStaffApplicationFormID), PRIMARY KEY(gibbonStaffApplicationFormFileID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffContract (gibbonStaffContractID INT(12) UNSIGNED AUTO_INCREMENT, title VARCHAR(100) NOT NULL, status VARCHAR(8) NOT NULL, dateStart DATE NOT NULL, dateEnd DATE DEFAULT NULL, salaryScale VARCHAR(255) DEFAULT NULL, salaryAmount NUMERIC(12, 2) DEFAULT NULL, salaryPeriod VARCHAR(255) DEFAULT NULL, responsibility VARCHAR(255) DEFAULT NULL, responsibilityAmount NUMERIC(12, 2) DEFAULT NULL, responsibilityPeriod VARCHAR(255) DEFAULT NULL, housingAmount NUMERIC(12, 2) DEFAULT NULL, housingPeriod VARCHAR(255) DEFAULT NULL, travelAmount NUMERIC(12, 2) DEFAULT NULL, travelPeriod VARCHAR(255) DEFAULT NULL, retirementAmount NUMERIC(12, 2) DEFAULT NULL, retirementPeriod VARCHAR(255) DEFAULT NULL, bonusAmount NUMERIC(12, 2) DEFAULT NULL, bonusPeriod VARCHAR(255) DEFAULT NULL, education LONGTEXT NOT NULL, notes LONGTEXT NOT NULL, contractUpload VARCHAR(255) DEFAULT NULL, timestampCreator DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonStaffID INT(10) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_28B78AEC76DF47DD (gibbonStaffID), INDEX IDX_28B78AECFF59AAB0 (gibbonPersonIDCreator), PRIMARY KEY(gibbonStaffContractID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffCoverage (gibbonStaffCoverageID INT(14) UNSIGNED AUTO_INCREMENT, status VARCHAR(12) DEFAULT \'Requested\' NOT NULL, requestType VARCHAR(12) DEFAULT \'Broadcast\' NOT NULL, substituteTypes VARCHAR(255) DEFAULT NULL, timestampStatus DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, notesStatus LONGTEXT DEFAULT NULL, timestampCoverage DATETIME DEFAULT NULL, notesCoverage LONGTEXT DEFAULT NULL, attachmentType VARCHAR(4) DEFAULT NULL, attachmentContent LONGTEXT DEFAULT NULL, notificationSent VARCHAR(1) DEFAULT \'N\' NOT NULL, notificationList LONGTEXT DEFAULT NULL, gibbonStaffAbsenceID INT(14) UNSIGNED, AcademicYearID INT(3) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, gibbonPersonIDStatus INT(10) UNSIGNED, gibbonPersonIDCoverage INT(10) UNSIGNED, UNIQUE INDEX UNIQ_946E51DE102BE4BE (gibbonStaffAbsenceID), UNIQUE INDEX UNIQ_946E51DE71FA7520 (AcademicYearID), UNIQUE INDEX UNIQ_946E51DECC6782D6 (gibbonPersonID), UNIQUE INDEX UNIQ_946E51DE4DA9DC74 (gibbonPersonIDStatus), UNIQUE INDEX UNIQ_946E51DE4ACF2F45 (gibbonPersonIDCoverage), PRIMARY KEY(gibbonStaffCoverageID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffCoverageDate (gibbonStaffCoverageDateID INT(14) UNSIGNED AUTO_INCREMENT, date DATE DEFAULT NULL, allDay VARCHAR(1) DEFAULT \'Y\' NOT NULL, timeStart TIME DEFAULT NULL, timeEnd TIME DEFAULT NULL, value NUMERIC(2, 1) DEFAULT NULL, reason VARCHAR(255) DEFAULT NULL, gibbonStaffCoverageID INT(14) UNSIGNED, gibbonStaffAbsenceDateID INT(14) UNSIGNED, gibbonPersonIDUnavailable INT(10) UNSIGNED, UNIQUE INDEX UNIQ_AD45031D12047EA7 (gibbonStaffCoverageID), UNIQUE INDEX UNIQ_AD45031D56318FAB (gibbonStaffAbsenceDateID), UNIQUE INDEX UNIQ_AD45031DFED701B3 (gibbonPersonIDUnavailable), PRIMARY KEY(gibbonStaffCoverageDateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStaffJobOpening (gibbonStaffJobOpeningID INT(10) UNSIGNED AUTO_INCREMENT, type VARCHAR(20) NOT NULL, jobTitle VARCHAR(100) NOT NULL, jdateOpen DATE NOT NULL, active VARCHAR(1) DEFAULT \'Y\' NOT NULL, description LONGTEXT NOT NULL, timestampCreator DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_C9D57E5CFF59AAB0 (gibbonPersonIDCreator), PRIMARY KEY(gibbonStaffJobOpeningID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStudentEnrolment (
	gibbonStudentEnrolmentID INT(8) UNSIGNED AUTO_INCREMENT, 
	rollOrder INT(2), 
	gibbonPersonID INT(10) UNSIGNED, 
	gibbonSchoolYearID INT(3) UNSIGNED, 
	gibbonYearGroupID INT(3) UNSIGNED, 
	gibbonRollGroupID INT(5) UNSIGNED, 
	INDEX gibbonPersonID (gibbonPersonID), 
	INDEX gibbonSchoolYearID (gibbonSchoolYearID), 
	INDEX gibbonYearGroupID (gibbonYearGroupID), 
	INDEX gibbonRollGroupID (gibbonRollGroupID), 
	INDEX gibbonPersonIndex (gibbonPersonID, gibbonSchoolYearID), 
	PRIMARY KEY(gibbonStudentEnrolmentID)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonStudentNote (gibbonStudentNoteID INT(12) UNSIGNED AUTO_INCREMENT, title VARCHAR(50) NOT NULL, note LONGTEXT NOT NULL, timestamp DATETIME DEFAULT NULL, gibbonPersonID INT(10) UNSIGNED, gibbonStudentNoteCategoryID INT(5) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, INDEX IDX_48CB2167CC6782D6 (gibbonPersonID), INDEX IDX_48CB21671E9DC1FF (gibbonStudentNoteCategoryID), INDEX IDX_48CB2167FF59AAB0 (gibbonPersonIDCreator), PRIMARY KEY(gibbonStudentNoteID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonSubstitute (gibbonSubstituteID INT(10) UNSIGNED AUTO_INCREMENT, active VARCHAR(1) DEFAULT \'Y\', type VARCHAR(60) DEFAULT NULL, details VARCHAR(255) DEFAULT NULL, priority INT(2), gibbonPersonID INT(10) UNSIGNED, UNIQUE INDEX gibbonPersonID (gibbonPersonID), PRIMARY KEY(gibbonSubstituteID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTheme (gibbonThemeID INT(4) UNSIGNED AUTO_INCREMENT, name VARCHAR(30) NOT NULL, description VARCHAR(100) NOT NULL, active VARCHAR(1) DEFAULT \'N\' NOT NULL, version VARCHAR(6) NOT NULL, author VARCHAR(40) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(gibbonThemeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTT (gibbonTTID INT(8) UNSIGNED AUTO_INCREMENT, name VARCHAR(30) NOT NULL, nameShort VARCHAR(12) NOT NULL, nameShortDisplay VARCHAR(24) DEFAULT \'Day Of The Week\' NOT NULL, gibbonYearGroupIDList VARCHAR(255) NOT NULL, active VARCHAR(1) NOT NULL, AcademicYearID INT(3) UNSIGNED, INDEX IDX_9431F94371FA7520 (AcademicYearID), PRIMARY KEY(gibbonTTID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTColumn (gibbonTTColumnID INT(6) UNSIGNED AUTO_INCREMENT, name VARCHAR(30) NOT NULL, nameShort VARCHAR(12) NOT NULL, PRIMARY KEY(gibbonTTColumnID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTColumnRow (gibbonTTColumnRowID INT(8) UNSIGNED AUTO_INCREMENT, name VARCHAR(12) NOT NULL, nameShort VARCHAR(4) NOT NULL, timeStart TIME NOT NULL, timeEnd TIME NOT NULL, type VARCHAR(8) NOT NULL, gibbonTTColumnID INT(6) UNSIGNED, INDEX gibbonTTColumnID (gibbonTTColumnID), PRIMARY KEY(gibbonTTColumnRowID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE `gibbonTTDay` (
  `gibbonTTDayID` int(10) UNSIGNED AUTO_INCREMENT,
  `name` CHAR(12) COLLATE utf8_unicode_ci NOT NULL,
  `nameShort` CHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `color` CHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `fontColor` CHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `gibbonTTID` int(8) UNSIGNED DEFAULT NULL,
  `gibbonTTColumnID` int(6) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`gibbonTTDayID`),
  UNIQUE KEY `nameShortTT` (`gibbonTTID`,`nameShort`),
  UNIQUE KEY `nameTT` (`gibbonTTID`,`name`),
  KEY `IDX_3B9106B3EE6A175` (`gibbonTTID`),
  KEY `gibbonTTColumnID` (`gibbonTTColumnID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTDayDate (gibbonTTDayDateID INT(10) UNSIGNED AUTO_INCREMENT, date DATE NOT NULL, gibbonTTDayID INT(10) UNSIGNED, INDEX gibbonTTDayID (gibbonTTDayID), PRIMARY KEY(gibbonTTDayDateID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTDayRowClass (gibbonTTDayRowClassID INT(12) UNSIGNED AUTO_INCREMENT, gibbonTTColumnRowID INT(8) UNSIGNED, gibbonTTDayID INT(10) UNSIGNED, gibbonCourseClassID INT(8) UNSIGNED, facility INT(10) UNSIGNED, INDEX IDX_C832432E375800E5 (gibbonTTDayID), INDEX gibbonCourseClassID (gibbonCourseClassID), INDEX facility (facility), INDEX gibbonTTColumnRowID (gibbonTTColumnRowID), PRIMARY KEY(gibbonTTDayRowClassID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTDayRowClassException (gibbonTTDayRowClassExceptionID INT(14) UNSIGNED AUTO_INCREMENT, gibbonTTDayRowClassID INT(12) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_D25EB853F501B20E (gibbonTTDayRowClassID), INDEX IDX_D25EB853CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonTTDayRowClassExceptionID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTImport (gibbonTTImportID INT(14) UNSIGNED AUTO_INCREMENT, courseNameShort VARCHAR(6) NOT NULL, classNameShort VARCHAR(5) NOT NULL, dayName VARCHAR(12) NOT NULL, rowName VARCHAR(12) NOT NULL, teacherUsernameList LONGTEXT NOT NULL, spaceName VARCHAR(30) NOT NULL, PRIMARY KEY(gibbonTTImportID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTSpaceBooking (gibbonTTSpaceBookingID INT(12) UNSIGNED AUTO_INCREMENT, foreignKey VARCHAR(30) DEFAULT \'gibbonFacilityID\' NOT NULL, foreignKeyID INT(10) UNSIGNED, date DATE NOT NULL, timeStart TIME NOT NULL, timeEnd TIME NOT NULL, gibbonPersonID INT(10) UNSIGNED, INDEX IDX_1A34AD71CC6782D6 (gibbonPersonID), PRIMARY KEY(gibbonTTSpaceBookingID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonTTSpaceChange (gibbonTTSpaceChangeID INT(12) UNSIGNED AUTO_INCREMENT, date DATE NOT NULL, gibbonTTDayRowClassID INT(12) UNSIGNED, facility INT(10) UNSIGNED, gibbonPersonID INT(10) UNSIGNED, INDEX facility (facility), INDEX IDX_772A323ECC6782D6 (gibbonPersonID), INDEX gibbonTTDayRowClassID (gibbonTTDayRowClassID), INDEX date (date), PRIMARY KEY(gibbonTTSpaceChangeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonUnit (gibbonUnitID INT(10) UNSIGNED AUTO_INCREMENT, name VARCHAR(40) NOT NULL, active VARCHAR(1) DEFAULT \'Y\' NOT NULL, description LONGTEXT NOT NULL, tags LONGTEXT NOT NULL, map VARCHAR(1) DEFAULT \'Y\' NOT NULL COMMENT \'Should this unit be included in curriculum maps and other summaries?\', ordering INT(2), attachment VARCHAR(255) NOT NULL, details LONGTEXT NOT NULL, license VARCHAR(50) DEFAULT NULL, sharedPublic VARCHAR(1) DEFAULT NULL, gibbonCourseID INT(8) UNSIGNED, gibbonPersonIDCreator INT(10) UNSIGNED, gibbonPersonIDLastEdit INT(10) UNSIGNED, INDEX IDX_2CFBB258ACDCF59E (gibbonCourseID), INDEX IDX_2CFBB258FF59AAB0 (gibbonPersonIDCreator), INDEX IDX_2CFBB258519966BA (gibbonPersonIDLastEdit), PRIMARY KEY(gibbonUnitID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonUnitBlock (gibbonUnitBlockID INT(12) UNSIGNED AUTO_INCREMENT, title VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, length VARCHAR(3) NOT NULL, contents LONGTEXT NOT NULL, teachersNotes LONGTEXT NOT NULL, sequenceNumber INT(4), gibbonUnitID INT(10) UNSIGNED, INDEX IDX_7D624DA246DE4A3D (gibbonUnitID), PRIMARY KEY(gibbonUnitBlockID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonUnitClass (gibbonUnitClassID INT(12) UNSIGNED AUTO_INCREMENT, running VARCHAR(1) DEFAULT \'N\' NOT NULL, gibbonUnitID INT(10) UNSIGNED, gibbonCourseClassID INT(8) UNSIGNED, INDEX IDX_1332C31F46DE4A3D (gibbonUnitID), INDEX IDX_1332C31FB67991E (gibbonCourseClassID), PRIMARY KEY(gibbonUnitClassID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonUnitClassBlock (gibbonUnitClassBlockID INT(14) UNSIGNED AUTO_INCREMENT, title VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, length VARCHAR(3) NOT NULL, contents LONGTEXT NOT NULL, teachersNotes LONGTEXT NOT NULL, sequenceNumber INT(4), complete VARCHAR(1) DEFAULT \'N\' NOT NULL, gibbonUnitClassID INT(12) UNSIGNED, gibbonPlannerEntryID INT(14) UNSIGNED, gibbonUnitBlockID INT(12) UNSIGNED, INDEX IDX_829289F1DEE4ED9C (gibbonUnitClassID), INDEX IDX_829289F1FE417281 (gibbonPlannerEntryID), INDEX IDX_829289F1858FFD1E (gibbonUnitBlockID), PRIMARY KEY(gibbonUnitClassBlockID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');
        $this->addSql('CREATE TABLE gibbonUnitOutcome (gibbonUnitOutcomeID INT(12) UNSIGNED AUTO_INCREMENT, sequenceNumber INT(4), content LONGTEXT NOT NULL, gibbonUnitID INT(10) UNSIGNED, gibbonOutcomeID INT(8) UNSIGNED, INDEX IDX_6D39303A46DE4A3D (gibbonUnitID), INDEX IDX_6D39303A35479F6A (gibbonOutcomeID), PRIMARY KEY(gibbonUnitOutcomeID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1');

        $this->logger->notice('The Legacy tables end.');

    }

    /**
     * loadForeignConstraints
     */
    private function loadForeignConstraints()
    {
        $this->logger->notice('The Legacy foreign constraint begins.');
        $this->addSql('ALTER TABLE gibbonAlarm ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonAlarmConfirm ADD CONSTRAINT FOREIGN KEY (gibbonAlarmID) REFERENCES gibbonAlarm (gibbonAlarmID)');
        $this->addSql('ALTER TABLE gibbonAlarmConfirm ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonApplicationForm ADD CONSTRAINT FOREIGN KEY (AcademicYearIDEntry) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonApplicationForm ADD CONSTRAINT FOREIGN KEY (gibbonYearGroupIDEntry) REFERENCES gibbonYearGroup (id)');
        $this->addSql('ALTER TABLE gibbonApplicationForm ADD CONSTRAINT FOREIGN KEY (parent1gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonApplicationForm ADD CONSTRAINT FOREIGN KEY (gibbonRollGroupID) REFERENCES gibbonRollGroup (id)');
        $this->addSql('ALTER TABLE gibbonApplicationForm ADD CONSTRAINT FOREIGN KEY (gibbonFamilyID) REFERENCES gibbonFamily (id)');
        $this->addSql('ALTER TABLE gibbonApplicationForm ADD CONSTRAINT FOREIGN KEY (gibbonPaymentID) REFERENCES gibbonPayment (gibbonPaymentID)');
        $this->addSql('ALTER TABLE gibbonApplicationFormFile ADD CONSTRAINT FOREIGN KEY (gibbonApplicationFormID) REFERENCES gibbonApplicationForm (gibbonApplicationFormID)');
        $this->addSql('ALTER TABLE gibbonApplicationFormLink ADD CONSTRAINT FOREIGN KEY (gibbonApplicationFormID1) REFERENCES gibbonApplicationForm (gibbonApplicationFormID)');
        $this->addSql('ALTER TABLE gibbonApplicationFormLink ADD CONSTRAINT FOREIGN KEY (gibbonApplicationFormID2) REFERENCES gibbonApplicationForm (gibbonApplicationFormID)');
        $this->addSql('ALTER TABLE gibbonApplicationFormRelationship ADD CONSTRAINT FOREIGN KEY (gibbonApplicationFormID) REFERENCES gibbonApplicationForm (gibbonApplicationFormID)');
        $this->addSql('ALTER TABLE gibbonApplicationFormRelationship ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonBehaviour ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonBehaviour ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonBehaviour ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonBehaviour ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonBehaviourLetter ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonBehaviourLetter ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonCourse ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonCourse ADD CONSTRAINT FOREIGN KEY (gibbonDepartmentID) REFERENCES gibbonDepartment (id)');
        $this->addSql('ALTER TABLE gibbonCourseClass ADD CONSTRAINT FOREIGN KEY (gibbonCourseID) REFERENCES gibbonCourse (gibbonCourseID)');
        $this->addSql('ALTER TABLE gibbonCourseClass ADD CONSTRAINT FOREIGN KEY (gibbonScaleIDTarget) REFERENCES gibbonScale (id)');
        $this->addSql('ALTER TABLE gibbonCourseClassMap ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonCourseClassMap ADD CONSTRAINT FOREIGN KEY (gibbonRollGroupID) REFERENCES gibbonRollGroup (id)');
        $this->addSql('ALTER TABLE gibbonCourseClassMap ADD CONSTRAINT FOREIGN KEY (gibbonYearGroupID) REFERENCES gibbonYearGroup (id)');
        $this->addSql('ALTER TABLE gibbonCourseClassPerson ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonCourseClassPerson ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonCrowdAssessDiscuss ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryHomeworkID) REFERENCES gibbonPlannerEntryHomework (gibbonPlannerEntryHomeworkID)');
        $this->addSql('ALTER TABLE gibbonCrowdAssessDiscuss ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonCrowdAssessDiscuss ADD CONSTRAINT FOREIGN KEY (gibbonCrowdAssessDiscussIDReplyTo) REFERENCES gibbonCrowdAssessDiscuss (gibbonCrowdAssessDiscussID)');
        $this->addSql('ALTER TABLE gibbonFinanceBillingSchedule ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBillingSchedule ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBillingSchedule ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBudget ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBudget ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBudgetCycle ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBudgetCycle ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceBudgetCycleAllocation ADD CONSTRAINT FOREIGN KEY (gibbonFinanceBudgetID) REFERENCES gibbonFinanceBudget (gibbonFinanceBudgetID)');
        $this->addSql('ALTER TABLE gibbonFinanceBudgetCycleAllocation ADD CONSTRAINT FOREIGN KEY (gibbonFinanceBudgetCycleID) REFERENCES gibbonFinanceBudgetCycle (gibbonFinanceBudgetCycleID)');
        $this->addSql('ALTER TABLE gibbonFinanceBudgetPerson ADD CONSTRAINT FOREIGN KEY (gibbonFinanceBudgetID) REFERENCES gibbonFinanceBudget (gibbonFinanceBudgetID)');
        $this->addSql('ALTER TABLE gibbonFinanceBudgetPerson ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceExpense ADD CONSTRAINT FOREIGN KEY (gibbonFinanceBudgetID) REFERENCES gibbonFinanceBudget (gibbonFinanceBudgetID)');
        $this->addSql('ALTER TABLE gibbonFinanceExpense ADD CONSTRAINT FOREIGN KEY (gibbonFinanceBudgetCycleID) REFERENCES gibbonFinanceBudgetCycle (gibbonFinanceBudgetCycleID)');
        $this->addSql('ALTER TABLE gibbonFinanceExpense ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDPayment) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceExpense ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceExpenseApprover ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceExpenseApprover ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceExpenseApprover ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceExpenseLog ADD CONSTRAINT FOREIGN KEY (gibbonFinanceExpenseID) REFERENCES gibbonFinanceExpense (gibbonFinanceExpenseID)');
        $this->addSql('ALTER TABLE gibbonFinanceExpenseLog ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceFee ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonFinanceFee ADD CONSTRAINT FOREIGN KEY (gibbonFinanceFeeCategoryID) REFERENCES gibbonFinanceFeeCategory (gibbonFinanceFeeCategoryID)');
        $this->addSql('ALTER TABLE gibbonFinanceFee ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceFee ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceFeeCategory ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceFeeCategory ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoice ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoice ADD CONSTRAINT FOREIGN KEY (gibbonFinanceInvoiceeID) REFERENCES gibbonFinanceInvoicee (gibbonFinanceInvoiceeID)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoice ADD CONSTRAINT FOREIGN KEY (gibbonFinanceBillingScheduleID) REFERENCES gibbonFinanceBillingSchedule (gibbonFinanceBillingScheduleID)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoice ADD CONSTRAINT FOREIGN KEY (gibbonPaymentID) REFERENCES gibbonPayment (gibbonPaymentID)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoice ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoice ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdate) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoicee ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoiceeUpdate ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoiceeUpdate ADD CONSTRAINT FOREIGN KEY (gibbonFinanceInvoiceeID) REFERENCES gibbonFinanceInvoicee (gibbonFinanceInvoiceeID)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoiceeUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdater) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoiceFee ADD CONSTRAINT FOREIGN KEY (gibbonFinanceInvoiceID) REFERENCES gibbonFinanceInvoice (gibbonFinanceInvoiceID)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoiceFee ADD CONSTRAINT FOREIGN KEY (gibbonFinanceFeeID) REFERENCES gibbonFinanceFee (gibbonFinanceFeeID)');
        $this->addSql('ALTER TABLE gibbonFinanceInvoiceFee ADD CONSTRAINT FOREIGN KEY (gibbonFinanceFeeCategoryID) REFERENCES gibbonFinanceFeeCategory (gibbonFinanceFeeCategoryID)');
        $this->addSql('ALTER TABLE gibbonFirstAid ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDPatient) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFirstAid ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonFirstAid ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDFirstAider) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonFirstAid ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonGroup ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDOwner) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonGroup ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonGroupPerson ADD CONSTRAINT FOREIGN KEY (gibbonGroupID) REFERENCES gibbonGroup (gibbonGroupID)');
        $this->addSql('ALTER TABLE gibbonGroupPerson ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonHook ADD CONSTRAINT FOREIGN KEY (gibbonModuleID) REFERENCES gibbonModule (id)');
        $this->addSql("ALTER TABLE gibbonImportHistory ADD CONSTRAINT FOREIGN KEY (performed_by) REFERENCES gibbonPerson (id)");
        $this->addSql('ALTER TABLE gibbonInternalAssessmentColumn ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentColumn ADD CONSTRAINT FOREIGN KEY (idAttainment) REFERENCES gibbonScale (id)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentColumn ADD CONSTRAINT FOREIGN KEY (idEffort) REFERENCES gibbonScale (id)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentColumn ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentColumn ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDLastEdit) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentEntry ADD CONSTRAINT FOREIGN KEY (gibbonInternalAssessmentColumnID) REFERENCES gibbonInternalAssessmentColumn (gibbonInternalAssessmentColumnID)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDStudent) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonInternalAssessmentEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDLastEdit) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonLog ADD CONSTRAINT FOREIGN KEY (gibbonModuleID) REFERENCES gibbonModule (id)');
        $this->addSql('ALTER TABLE gibbonLog ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonLog ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonHookID) REFERENCES gibbonHook (gibbonHookID)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonUnitID) REFERENCES gibbonUnit (gibbonUnitID)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonAcademicYearTermID) REFERENCES gibbonAcademicYearTerm (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (idAttainment) REFERENCES gibbonScale (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (idEffort) REFERENCES gibbonScale (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonRubricIDAttainment) REFERENCES gibbonRubric (gibbonRubricID)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonRubricIDEffort) REFERENCES gibbonRubric (gibbonRubricID)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookColumn ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDLastEdit) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookEntry ADD CONSTRAINT FOREIGN KEY (gibbonMarkbookColumnID) REFERENCES gibbonMarkbookColumn (gibbonMarkbookColumnID)');
        $this->addSql('ALTER TABLE gibbonMarkbookEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDStudent) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDLastEdit) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookTarget ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonMarkbookTarget ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDStudent) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookTarget ADD CONSTRAINT FOREIGN KEY (gibbonScaleGradeID) REFERENCES gibbonScaleGrade (id)');
        $this->addSql('ALTER TABLE gibbonMarkbookWeight ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonMessenger ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMessengerCannedResponse ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMessengerReceipt ADD CONSTRAINT FOREIGN KEY (gibbonMessengerID) REFERENCES gibbonMessenger (gibbonMessengerID)');
        $this->addSql('ALTER TABLE gibbonMessengerReceipt ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonMessengerTarget ADD CONSTRAINT FOREIGN KEY (gibbonMessengerID) REFERENCES gibbonMessenger (gibbonMessengerID)');
        $this->addSql('ALTER TABLE gibbonOutcome ADD CONSTRAINT FOREIGN KEY (gibbonDepartmentID) REFERENCES gibbonDepartment (id)');
        $this->addSql('ALTER TABLE gibbonOutcome ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPayment ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedical ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalCondition ADD CONSTRAINT FOREIGN KEY (gibbonPersonMedicalID) REFERENCES gibbonPersonMedical (gibbonPersonMedicalID)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalCondition ADD CONSTRAINT FOREIGN KEY (id) REFERENCES gibbonAlertLevel (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalConditionUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonMedicalUpdateID) REFERENCES gibbonPersonMedicalUpdate (gibbonPersonMedicalUpdateID)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalConditionUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonMedicalConditionID) REFERENCES gibbonPersonMedicalCondition (gibbonPersonMedicalConditionID)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalConditionUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonMedicalID) REFERENCES gibbonPersonMedical (gibbonPersonMedicalID)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalConditionUpdate ADD CONSTRAINT FOREIGN KEY (id) REFERENCES gibbonAlertLevel (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalConditionUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdater) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalSymptoms ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalSymptoms ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDTaker) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalUpdate ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonMedicalID) REFERENCES gibbonPersonMedical (gibbonPersonMedicalID)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonMedicalUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdater) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonUpdate ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonPersonUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPersonUpdate ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUpdater) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntry ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntry ADD CONSTRAINT FOREIGN KEY (gibbonUnitID) REFERENCES gibbonUnit (gibbonUnitID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDLastEdit) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryDiscuss ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryDiscuss ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryDiscuss ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryDiscussIDReplyTo) REFERENCES gibbonPlannerEntryDiscuss (gibbonPlannerEntryDiscussID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryGuest ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryGuest ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryHomework ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryHomework ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryOutcome ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryOutcome ADD CONSTRAINT FOREIGN KEY (gibbonOutcomeID) REFERENCES gibbonOutcome (gibbonOutcomeID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryStudentHomework ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryStudentHomework ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryStudentTracker ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonPlannerEntryStudentTracker ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerParentWeeklyEmailSummary ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonPlannerParentWeeklyEmailSummary ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDParent) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonPlannerParentWeeklyEmailSummary ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDStudent) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonResource ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonRubric ADD CONSTRAINT FOREIGN KEY (gibbonDepartmentID) REFERENCES gibbonDepartment (id)');
        $this->addSql('ALTER TABLE gibbonRubric ADD CONSTRAINT FOREIGN KEY (gibbonScaleID) REFERENCES gibbonScale (id)');
        $this->addSql('ALTER TABLE gibbonRubric ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonRubricCell ADD CONSTRAINT FOREIGN KEY (gibbonRubricID) REFERENCES gibbonRubric (gibbonRubricID)');
        $this->addSql('ALTER TABLE gibbonRubricCell ADD CONSTRAINT FOREIGN KEY (gibbonRubricColumnID) REFERENCES gibbonRubricColumn (gibbonRubricColumnID)');
        $this->addSql('ALTER TABLE gibbonRubricCell ADD CONSTRAINT FOREIGN KEY (gibbonRubricRowID) REFERENCES gibbonRubricRow (gibbonRubricRowID)');
        $this->addSql('ALTER TABLE gibbonRubricColumn ADD CONSTRAINT FOREIGN KEY (gibbonRubricID) REFERENCES gibbonRubric (gibbonRubricID)');
        $this->addSql('ALTER TABLE gibbonRubricColumn ADD CONSTRAINT FOREIGN KEY (gibbonScaleGradeID) REFERENCES gibbonScaleGrade (id)');
        $this->addSql('ALTER TABLE gibbonRubricEntry ADD CONSTRAINT FOREIGN KEY (gibbonRubricID) REFERENCES gibbonRubric (gibbonRubricID)');
        $this->addSql('ALTER TABLE gibbonRubricEntry ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonRubricEntry ADD CONSTRAINT FOREIGN KEY (gibbonRubricCellID) REFERENCES gibbonRubricCell (gibbonRubricCellID)');
        $this->addSql('ALTER TABLE gibbonRubricRow ADD CONSTRAINT FOREIGN KEY (gibbonRubricID) REFERENCES gibbonRubric (gibbonRubricID)');
        $this->addSql('ALTER TABLE gibbonRubricRow ADD CONSTRAINT FOREIGN KEY (gibbonOutcomeID) REFERENCES gibbonOutcome (gibbonOutcomeID)');
        $this->addSql('ALTER TABLE gibbonStaff ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffAbsence ADD CONSTRAINT FOREIGN KEY (gibbonStaffAbsenceTypeID) REFERENCES gibbonStaffAbsenceType (id)');
        $this->addSql('ALTER TABLE gibbonStaffAbsence ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonStaffAbsence ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffAbsence ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDApproval) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffAbsence ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffAbsence ADD CONSTRAINT FOREIGN KEY (gibbonGroupID) REFERENCES gibbonGroup (gibbonGroupID)');
        $this->addSql('ALTER TABLE gibbonStaffAbsenceDate ADD CONSTRAINT FOREIGN KEY (gibbonStaffAbsenceID) REFERENCES gibbonStaffAbsence (gibbonStaffAbsenceID)');
        $this->addSql('ALTER TABLE gibbonStaffApplicationForm ADD CONSTRAINT FOREIGN KEY (gibbonStaffJobOpeningID) REFERENCES gibbonStaffJobOpening (gibbonStaffJobOpeningID)');
        $this->addSql('ALTER TABLE gibbonStaffApplicationForm ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffApplicationFormFile ADD CONSTRAINT FOREIGN KEY (gibbonStaffApplicationFormID) REFERENCES gibbonStaffApplicationForm (gibbonStaffApplicationFormID)');
        $this->addSql('ALTER TABLE gibbonStaffContract ADD CONSTRAINT FOREIGN KEY (gibbonStaffID) REFERENCES gibbonStaff (gibbonStaffID)');
        $this->addSql('ALTER TABLE gibbonStaffContract ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffCoverage ADD CONSTRAINT FOREIGN KEY (gibbonStaffAbsenceID) REFERENCES gibbonStaffAbsence (gibbonStaffAbsenceID)');
        $this->addSql('ALTER TABLE gibbonStaffCoverage ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonStaffCoverage ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffCoverage ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDStatus) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffCoverage ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCoverage) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffCoverageDate ADD CONSTRAINT FOREIGN KEY (gibbonStaffCoverageID) REFERENCES gibbonStaffCoverage (gibbonStaffCoverageID)');
        $this->addSql('ALTER TABLE gibbonStaffCoverageDate ADD CONSTRAINT FOREIGN KEY (gibbonStaffAbsenceDateID) REFERENCES gibbonStaffAbsenceDate (gibbonStaffAbsenceDateID)');
        $this->addSql('ALTER TABLE gibbonStaffCoverageDate ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDUnavailable) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStaffJobOpening ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStudentEnrolment ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStudentEnrolment ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonStudentEnrolment ADD CONSTRAINT FOREIGN KEY (gibbonYearGroupID) REFERENCES gibbonYearGroup (id)');
        $this->addSql('ALTER TABLE gibbonStudentEnrolment ADD CONSTRAINT FOREIGN KEY (gibbonRollGroupID) REFERENCES gibbonRollGroup (id)');
        $this->addSql('ALTER TABLE gibbonStudentNote ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonStudentNote ADD CONSTRAINT FOREIGN KEY (gibbonStudentNoteCategoryID) REFERENCES gibbonStudentNoteCategory (id)');
        $this->addSql('ALTER TABLE gibbonStudentNote ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonSubstitute ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonTT ADD CONSTRAINT FOREIGN KEY (AcademicYearID) REFERENCES gibbonAcademicYear (id)');
        $this->addSql('ALTER TABLE gibbonTTColumnRow ADD CONSTRAINT FOREIGN KEY (gibbonTTColumnID) REFERENCES gibbonTTColumn (gibbonTTColumnID)');
        $this->addSql('ALTER TABLE gibbonTTDay ADD CONSTRAINT FOREIGN KEY (gibbonTTID) REFERENCES gibbonTT (gibbonTTID)');
        $this->addSql('ALTER TABLE gibbonTTDay ADD CONSTRAINT FOREIGN KEY (gibbonTTColumnID) REFERENCES gibbonTTColumn (gibbonTTColumnID)');
        $this->addSql('ALTER TABLE gibbonTTDayDate ADD CONSTRAINT FOREIGN KEY (gibbonTTDayID) REFERENCES gibbonTTDay (gibbonTTDayID)');
        $this->addSql('ALTER TABLE gibbonTTDayRowClass ADD CONSTRAINT FOREIGN KEY (gibbonTTColumnRowID) REFERENCES gibbonTTColumnRow (gibbonTTColumnRowID)');
        $this->addSql('ALTER TABLE gibbonTTDayRowClass ADD CONSTRAINT FOREIGN KEY (gibbonTTDayID) REFERENCES gibbonTTDay (gibbonTTDayID)');
        $this->addSql('ALTER TABLE gibbonTTDayRowClass ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonTTDayRowClass ADD CONSTRAINT FOREIGN KEY (facility) REFERENCES gibbonFacility (id)');
        $this->addSql('ALTER TABLE gibbonTTDayRowClassException ADD CONSTRAINT FOREIGN KEY (gibbonTTDayRowClassID) REFERENCES gibbonTTDayRowClass (gibbonTTDayRowClassID)');
        $this->addSql('ALTER TABLE gibbonTTDayRowClassException ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonTTSpaceBooking ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonTTSpaceChange ADD CONSTRAINT FOREIGN KEY (gibbonTTDayRowClassID) REFERENCES gibbonTTDayRowClass (gibbonTTDayRowClassID)');
        $this->addSql('ALTER TABLE gibbonTTSpaceChange ADD CONSTRAINT FOREIGN KEY (facility) REFERENCES gibbonFacility (id)');
        $this->addSql('ALTER TABLE gibbonTTSpaceChange ADD CONSTRAINT FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonUnit ADD CONSTRAINT FOREIGN KEY (gibbonCourseID) REFERENCES gibbonCourse (gibbonCourseID)');
        $this->addSql('ALTER TABLE gibbonUnit ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDCreator) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonUnit ADD CONSTRAINT FOREIGN KEY (gibbonPersonIDLastEdit) REFERENCES gibbonPerson (id)');
        $this->addSql('ALTER TABLE gibbonUnitBlock ADD CONSTRAINT FOREIGN KEY (gibbonUnitID) REFERENCES gibbonUnit (gibbonUnitID)');
        $this->addSql('ALTER TABLE gibbonUnitClass ADD CONSTRAINT FOREIGN KEY (gibbonUnitID) REFERENCES gibbonUnit (gibbonUnitID)');
        $this->addSql('ALTER TABLE gibbonUnitClass ADD CONSTRAINT FOREIGN KEY (gibbonCourseClassID) REFERENCES gibbonCourseClass (gibbonCourseClassID)');
        $this->addSql('ALTER TABLE gibbonUnitClassBlock ADD CONSTRAINT FOREIGN KEY (gibbonUnitClassID) REFERENCES gibbonUnitClass (gibbonUnitClassID)');
        $this->addSql('ALTER TABLE gibbonUnitClassBlock ADD CONSTRAINT FOREIGN KEY (gibbonPlannerEntryID) REFERENCES gibbonPlannerEntry (gibbonPlannerEntryID)');
        $this->addSql('ALTER TABLE gibbonUnitClassBlock ADD CONSTRAINT FOREIGN KEY (gibbonUnitBlockID) REFERENCES gibbonUnitBlock (gibbonUnitBlockID)');
        $this->addSql('ALTER TABLE gibbonUnitOutcome ADD CONSTRAINT FOREIGN KEY (gibbonUnitID) REFERENCES gibbonUnit (gibbonUnitID)');
        $this->addSql('ALTER TABLE gibbonUnitOutcome ADD CONSTRAINT FOREIGN KEY (gibbonOutcomeID) REFERENCES gibbonOutcome (gibbonOutcomeID)');
        $this->logger->notice('The Legacy foreign constraint ends.');
    }

    /**
     * installDemo
     * @return bool
     */
    private function installDemo(): bool
    {
        $installation = $this->getParameter('installation', []);
        if (isset($installation['demo']))
            return $installation['demo'] === true;
        return false;
    }

    /**
     * getParameter
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getParameter(string $name, $default = null)
    {
        if ($this->hasParameter($name))
            return $this->bag->get($name);
        return $default;
    }

    /**
     * hasParameter
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return $this->bag->has($name);
    }
}
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
 * Date: 16/10/2019
 * Time: 14:23
 */

namespace App\Modules\System\Entity;

use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Module
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ModuleUpgradeRepository")
 * @ORM\Table(name="ModuleUpgrade",uniqueConstraints={@ORM\UniqueConstraint(name="module_version", columns={"module","version"})})
 * @UniqueEntity(fields={"module","version"})
 * @ORM\HasLifecycleCallbacks()
 */
class ModuleUpgrade implements EntityInterface
{
    CONST VERSION = '20200401';

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=127)
     * @Assert\NotBlank()
     */
    private $table;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     */
    private $tableVersion;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getTableSectionList")
     */
    private $tableSection;

    /**
     * @var string[]
     */
    private static $tableSectionList = [
        'core',
        'foreign_constraints',
        'install',
    ];

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank()
     */
    private $executedAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param int|null $id
     * @return ModuleUpgrade
     */
    public function setId(?int $id): ModuleUpgrade
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Table.
     *
     * @param string $table
     * @return ModuleUpgrade
     */
    public function setTable(string $table): ModuleUpgrade
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTableVersion(): ?string
    {
        return $this->tableVersion;
    }

    /**
     * TableVersion.
     *
     * @param string|null $tableVersion
     * @return ModuleUpgrade
     */
    public function setTableVersion(?string $tableVersion): ModuleUpgrade
    {
        $this->tableVersion = $tableVersion;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTableSection(): ?string
    {
        return $this->tableSection;
    }

    /**
     * TableSection.
     *
     * @param string|null $tableSection
     * @return ModuleUpgrade
     */
    public function setTableSection(?string $tableSection): ModuleUpgrade
    {
        $this->tableSection = $tableSection;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getTableSectionList(): array
    {
        return self::$tableSectionList;
    }

    /**
     * getExecutedAt
     * @return \DateTimeImmutable|null
     */
    public function getExecutedAt(): ?\DateTimeImmutable
    {
        return $this->executedAt;
    }

    /**
     * setExecutedAt
     * @param \DateTimeImmutable|null $executedAt
     * @return ModuleUpgrade
     */
    public function setExecutedAt(?\DateTimeImmutable $executedAt): ModuleUpgrade
    {
        $this->executedAt = $executedAt;
        return $this;
    }

    /**
     * generateExecutedAt
     * @return ModuleUpgrade
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function generateExecutedAt(): ModuleUpgrade
    {
        if (null === $this->getExecutedAt())
            $this->setExecutedAt(new \DateTimeImmutable());
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__ModuleUpgrade` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `module` int(4) UNSIGNED DEFAULT NULL,
                    `version` CHAR(20) COLLATE utf8mb4_general_ci NOT NULL,
                    `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `module_version` (`module`,`version`),
                    KEY `module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__ModuleUpgrade`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);';
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
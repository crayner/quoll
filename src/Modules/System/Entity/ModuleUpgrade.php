<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
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

use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Module
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ModuleUpgradeRepository")
 * @ORM\Table(name="ModuleUpgrade")
 * @ORM\HasLifecycleCallbacks()
 */
class ModuleUpgrade extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=127)
     * @Assert\NotBlank()
     */
    private $tableName;

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
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return ModuleUpgrade
     */
    public function setId(?string $id): ModuleUpgrade
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * TableName.
     *
     * @param string $tableName
     * @return ModuleUpgrade
     */
    public function setTableName(string $tableName): ModuleUpgrade
    {
        $this->tableName = $tableName;
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
                    `table_name` varchar(127) COLLATE utf8mb4_general_ci NOT NULL,
                    `table_version` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
                    `table_section` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
                    `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
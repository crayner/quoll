<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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

use App\Modules\System\Entity\Module;
use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Module
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ModuleUpgradeRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="ModuleUpgrade",uniqueConstraints={@ORM\UniqueConstraint(name="module_version", columns={"module","version"})})
 * @UniqueEntity(fields={"module","version"})
 * @ORM\HasLifecycleCallbacks()
 */
class ModuleUpgrade implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="App\Modules\System\Entity\Module", inversedBy="upgradeLogs")
     * @ORM\JoinColumn(name="module",referencedColumnName="id",nullable=false)
     */
    private $module;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     */
    private $version;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable")
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
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param Module|null $module
     * @return ModuleUpgrade
     */
    public function setModule(?Module $module): ModuleUpgrade
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Version.
     *
     * @param string|null $version
     * @return ModuleUpgrade
     */
    public function setVersion(?string $version): ModuleUpgrade
    {
        $this->version = $version;
        return $this;
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

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__ModuleUpgrade` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `module` int(4) UNSIGNED DEFAULT NULL,
                    `version` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                    `executed_at` datetime NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `module_version` (`module`,`version`),
                    KEY `module` (`module`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__ModuleUpgrade`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}
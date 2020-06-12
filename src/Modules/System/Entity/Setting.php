<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 25/11/2018
 * Time: 10:00
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Setting
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\SettingRepository")
 * @ORM\Table(name="Setting",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="scope_display",columns={"scope","name_display"}),
 *     @ORM\UniqueConstraint(name="scope_name",columns={"scope","name"})})
 * @UniqueEntity({"name","scope"})
 * @UniqueEntity({"nameDisplay","scope"})
 */
class Setting extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $scope;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     */
    private $nameDisplay;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $value;

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
     * @return Setting
     */
    public function setId(?string $id): Setting
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string|null $scope
     * @return Setting
     */
    public function setScope(?string $scope): Setting
    {
        $this->scope = mb_substr($scope, 0, 50);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Setting
     */
    public function setName(?string $name): Setting
    {
        $this->name = mb_substr($name, 0, 50);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameDisplay(): ?string
    {
        return $this->nameDisplay;
    }

    /**
     * @param string|null $nameDisplay
     * @return Setting
     */
    public function setNameDisplay(?string $nameDisplay): Setting
    {
        $this->nameDisplay = mb_substr($nameDisplay, 0, 60);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Setting
     */
    public function setDescription(?string $description): Setting
    {
        $this->description = mb_substr($description, 0, 255);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Setting
     */
    public function setValue(?string $value): Setting
    {
        $this->value = $value;
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

    /**
     * create
     * @return array|string[]
     * 12/06/2020 10:19
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Setting` (
                    `id` CHAR(36) NOT NULL,
                    `scope` VARCHAR(50) NOT NULL,
                    `name` VARCHAR(50) NOT NULL,
                    `name_display` VARCHAR(60) NOT NULL,
                    `description` VARCHAR(191) DEFAULT NULL,
                    `value` LONGTEXT DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `scope_name` (`scope`,`name`),
                    UNIQUE KEY `scope_display` (`scope`,`name_display`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * coreData
     * @return array
     * 12/06/2020 10:18
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents('SettingCoreData.yaml'));
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

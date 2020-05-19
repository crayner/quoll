<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\System\Entity;

use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Hook
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\HookRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Hook",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name", "type"})},
 *     indexes={@ORM\Index(name="module",columns={"module"})})
 */
class Hook implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(4) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=20, nullable=true)
     */
    private $type;

    /**
     * @var array 
     */
    private static $typeList = ['Public Home Page','Student Profile','Parental Dashboard','Staff Dashboard','Student Dashboard'];

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $options;

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="Module")
     * @ORM\JoinColumn(name="module", referencedColumnName="id", nullable=false)
     */
    private $module;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Hook
     */
    public function setId(?int $id): Hook
    {
        $this->id = $id;
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
     * @return Hook
     */
    public function setName(?string $name): Hook
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Hook
     */
    public function setType(?string $type): Hook
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : null ;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOptions(): ?string
    {
        return $this->options;
    }

    /**
     * @param string|null $options
     * @return Hook
     */
    public function setOptions(?string $options): Hook
    {
        $this->options = $options;
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
     * @param Module|null $module
     * @return Hook
     */
    public function setModule(?Module $module): Hook
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }

    public function create(): string
    {
        return 'CREATE TABLE `__pefix__Hook` (
                    `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(50) COLLATE ut8mb4_unicode_ci NOT NULL,
                    `type` varchar(20) COLLATE ut8mb4_unicode_ci DEFAULT NULL,
                    `options` longtext COLLATE ut8mb4_unicode_ci NOT NULL,
                    `module` int(4) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`,`type`),
                    KEY `module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Hook`
                    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}
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
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Util\FileHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class DepartmentResource
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\DepartmentResourceRepository")
 * @ORM\Table(name="DepartmentResource", indexes={@ORM\Index(name="department",columns={"department"})})
 * @ORM\HasLifecycleCallbacks()
 */
class DepartmentResource extends AbstractEntity
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
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="resources")
     * @ORM\JoinColumn(name="department",referencedColumnName="id", nullable=false)
     */
    private $department;

    /**
     * @var string
     * @ORM\Column(length=16)
     */
    private $type = 'Link';

    /**
     * @var array
     */
    private static $typeList = ['Link', 'File'];

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $url;

    /**
     * @var string|null
     */
    private $oldUrl;

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
     * @return DepartmentResource
     */
    public function setId(?string $id): DepartmentResource
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * @param Department|null $department
     * @return DepartmentResource
     */
    public function setDepartment(?Department $department): DepartmentResource
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DepartmentResource
     */
    public function setType(string $type): DepartmentResource
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Link';
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
     * @return DepartmentResource
     */
    public function setName(?string $name): DepartmentResource
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return DepartmentResource
     */
    public function setUrl(?string $url): DepartmentResource
    {
        $this->setOldUrl($this->getUrl());
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOldUrl(): ?string
    {
        return $this->oldUrl;
    }

    /**
     * OldUrl.
     *
     * @param string|null $oldUrl
     * @return DepartmentResource
     */
    public function setOldUrl(?string $oldUrl): DepartmentResource
    {
        $this->oldUrl = $oldUrl;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'department' => $this->getDepartment() ? $this->getDepartment()->getId(): null,
            'url' => $this->getUrl(),
        ];
    }

    /**
     * onPersist
     * @ORM\PreUpdate()
     */
    public function removeOldFiles(): DepartmentResource
    {
        if ($this->getOldUrl())
        {
            $file = realpath($this->getOldUrl()) ?: realpath(__DIR__ . '/../../public' . $this->getOldUrl()) ?: null;
            if (is_file($file))
                unlink($file);
        }
        $this->oldUrl = null;

        return $this;
    }

    /**
     * onRemove
     * @ORM\PreRemove()
     */
    public function RemoveFiles(): DepartmentResource
    {
        $this->oldUrl = $this->getUrl();
        return $this->removeOldFiles();
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__DepartmentResource` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `type` varchar(16) NOT NULL,
                    `name` varchar(100) NOT NULL,
                    `url` varchar(191) NOT NULL,
                    `department` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `department` (`department`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__DepartmentResource`
  ADD CONSTRAINT FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
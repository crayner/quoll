<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AttendanceCode
 * @package App\Modules\Attendance\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceCodeRepository")
 * @ORM\Table(name="AttendanceCode",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="sort_order",columns={"sort_order"}),
 *     @ORM\UniqueConstraint(name="code",columns={"code"})})
 * @UniqueEntity({"name"})
 * @UniqueEntity({"code"})
 * @UniqueEntity({"sortOrder"})
 * @ORM\HasLifecycleCallbacks()
 */
class AttendanceCode extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     * @Assert\Choice(callback="getTypeList")
     */
    private $type = 'Additional';

    /**
     * @var array
     */
    private static $typeList = ['Core','Additional'];

    /**
     * @var string|null
     * @ORM\Column(length=3)
     * @Assert\Choice(callback="getDirectionList")
     */
    private $direction = 'In';

    /**
     * @var array
     */
    private static $directionList = ['In','Out'];

    /**
     * @var string|null
     * @ORM\Column(length=14)
     * @Assert\Choice(callback="getScopeList")
     */
    private $scope = 'Onsite';

    /**
     * @var array
     */
    private static $scopeList = ['Onsite','Onsite - Late','Offsite','Offsite - Left'];

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $active;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $reportable;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $future;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array")
     */
    private $securityRoles;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1,max=999)
     */
    private $sortOrder;

    /**
     * AttendanceCode constructor.
     */
    public function __construct()
    {
        $this->scope = 'Onsite';
        $this->type = 'Additional';
        $this->direction = 'In';
        $this->active = 'Y';
        $this->reportable = 'Y';
        $this->future = 'Y';
        $this->sortOrder = ProviderFactory::getRepository(AttendanceCode::class)->nextSortOrder();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return AttendanceCode
     */
    public function setId(?string $id): AttendanceCode
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
     * @return AttendanceCode
     */
    public function setName(?string $name): AttendanceCode
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Code.
     *
     * @param string|null $code
     * @return AttendanceCode
     */
    public function setCode(?string $code): AttendanceCode
    {
        $this->code = $code;
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
     * @return AttendanceCode
     */
    public function setType(?string $type): AttendanceCode
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : '';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDirection(): ?string
    {
        return $this->direction;
    }

    /**
     * @param string|null $direction
     * @return AttendanceCode
     */
    public function setDirection(?string $direction): AttendanceCode
    {
        $this->direction = in_array($direction, self::getDirectionList()) ? $direction :  '';
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
     * @return AttendanceCode
     */
    public function setScope(?string $scope): AttendanceCode
    {
        $this->scope = in_array($scope, self::getScopeList()) ? $scope : '';
        return $this;
    }

    /**
     * isActive
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return self::checkBoolean($this->active, 'N');
    }

    /**
     * @param string|null $active
     * @return AttendanceCode
     */
    public function setActive(?string $active): AttendanceCode
    {
        $this->active = self::checkBoolean($active, 'N');
        return $this;
    }

    /**
     * @return string|null
     */
    public function isReportable(): bool
    {
        return $this->getReportable() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getReportable(): ?string
    {
        return self::checkBoolean($this->reportable, 'N');
    }

    /**
     * @param string|null $reportable
     * @return AttendanceCode
     */
    public function setReportable(?string $reportable): AttendanceCode
    {
        $this->reportable = self::checkBoolean($reportable,'N');
        return $this;
    }

    /**
     * isFuture
     * @return bool
     */
    public function isFuture(): bool
    {
        return $this->getFuture() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getFuture(): ?string
    {
        return self::checkBoolean($this->future, 'N');
    }

    /**
     * @param string|null $future
     * @return AttendanceCode
     */
    public function setFuture(?string $future): AttendanceCode
    {
        $this->future = self::checkBoolean($future, 'N');
        return $this;
    }

    /**
     * @return array|null
     */
    public function getSecurityRoles(): ?array
    {
        return $this->securityRoles;
    }

    /**
     * SecurityRoles.
     *
     * @param array|null $securityRoles
     * @return AttendanceCode
     */
    public function setSecurityRoles($securityRoles): AttendanceCode
    {
        if ($securityRoles instanceof ArrayCollection) {
            $result = [];
            foreach($securityRoles as $role)
            {
                if ($role instanceof Role)
                    $result[] = $role->getId();
                else
                    $result[] = $role;
            }
            $securityRoles = $result;
        }
        $this->securityRoles = $securityRoles;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int|null $sortOrder
     * @return AttendanceCode
     */
    public function setSortOrder(?int $sortOrder): AttendanceCode
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * nextSortOrder
     * @return AttendanceCode
     * @ORM\PrePersist()
     * 13/06/2020 09:05
     */
    public function nextSortOrder(): AttendanceCode
    {
        if ($this->getSortOrder() === null) {
            return $this->setSortOrder(ProviderFactory::getRepository(AttendanceCode::class)->nextSortOrder());
        }
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
     * @return array
     */
    public static function getDirectionList(): array
    {
        return self::$directionList;
    }

    /**
     * @return array
     */
    public static function getScopeList(): array
    {
        return self::$scopeList;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        return [
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'direction' => $this->getDirection(),
            'scope' => $this->getScope(),
            'scope_filter' => explode(' - ', $this->getScope())[0],
            'id' => $this->getId(),
            'active' => $this->isActive() ? TranslationHelper::translate('Yes', [], 'messages') :  TranslationHelper::translate('No', [], 'messages'),
            'canDelete' => ProviderFactory::create(AttendanceCode::class)->canDelete($this),
        ];
    }

    /**
     * create
     * @return array|string[]
     * 12/06/2020 16:46
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__AttendanceCode` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(30) NOT NULL,
                    `code` varchar(4) NOT NULL,
                    `type` varchar(12) NOT NULL,
                    `direction` varchar(3) NOT NULL,
                    `scope` varchar(14) NOT NULL,
                    `active` varchar(1) NOT NULL,
                    `reportable` varchar(1) NOT NULL,
                    `future` varchar(1) NOT NULL,
                    `security_roles` longtext NOT NULL COMMENT '(DC2Type:simple_array)',
                    `sort_order` smallint(6) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `sort_order` (`sort_order`),
                    UNIQUE KEY `code` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * getVersion
     * @return string
     * 12/06/2020 13:48
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * coreData
     * @return array
     * 12/06/2020 18:11
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents('AttendanceCodeCoreData.yaml'));
    }
}
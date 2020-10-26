<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
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

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private ?string $name;

    /**
     * @var string|null
     * @ORM\Column(length=4)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private ?string $code;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     * @Assert\Choice(callback="getTypeList")
     */
    private string $type = 'Additional';

    /**
     * @var array
     */
    private static array $typeList = ['Core','Additional'];

    /**
     * @var string|null
     * @ORM\Column(length=3)
     * @Assert\Choice(callback="getDirectionList")
     */
    private string $direction = 'In';

    /**
     * @var array
     */
    private static array $directionList = ['In','Out'];

    /**
     * @var string|null
     * @ORM\Column(length=14)
     * @Assert\Choice(callback="getScopeList")
     */
    private string $scope = 'Onsite';

    /**
     * @var array
     */
    private static array $scopeList = ['Onsite','Onsite - Late','Offsite','Offsite - Left'];

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private bool $active;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private bool $reportable;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private bool $future;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array")
     */
    private ?array $securityRoles;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1,max=999)
     */
    private $sortOrder;

    /**
     * @var boolean
     * @ORM\Column(type="boolean",name="default_code")
     */
    private bool $defaultCode = false;

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
        $this->sortOrder = $this->nextSortOrder();
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
     * @param string $type
     * @return AttendanceCode
     */
    public function setType(string $type): AttendanceCode
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
     * @param string $direction
     * @return AttendanceCode
     */
    public function setDirection(string $direction): AttendanceCode
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
     * @param string $scope
     * @return AttendanceCode
     */
    public function setScope(string $scope): AttendanceCode
    {
        $this->scope = in_array($scope, self::getScopeList()) ? $scope : '';
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool $active
     * @return AttendanceCode
     */
    public function setActive(bool $active): AttendanceCode
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        return (bool)$this->reportable;
    }

    /**
     * @param bool $reportable
     * @return AttendanceCode
     */
    public function setReportable(bool $reportable): AttendanceCode
    {
        $this->reportable = $reportable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFuture(): bool
    {
        return (bool)$this->future;
    }

    /**
     * @param bool $future
     * @return AttendanceCode
     */
    public function setFuture(bool $future): AttendanceCode
    {
        $this->future = $future;
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
     * @param array $securityRoles
     * @return AttendanceCode
     */
    public function setSecurityRoles(array $securityRoles): AttendanceCode
    {
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
     * DefaultCode
     *
     * @return bool
     */
    public function isDefaultCode(): bool
    {
        return $this->defaultCode;
    }

    /**
     * DefaultCode
     *
     * @param bool $defaultCode
     * @return AttendanceCode
     */
    public function setDefaultCode(bool $defaultCode): AttendanceCode
    {
        $this->defaultCode = $defaultCode;
        return $this;
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
            'active' => $this->translateBoolean($this->isActive()),
            'default' => $this->translateBoolean($this->isDefaultCode()),
            'canDelete' => ProviderFactory::create(AttendanceCode::class)->canDelete($this),
        ];
    }

    /**
     * coreData
     * @return array
     * 12/06/2020 18:11
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/AttendanceCodeCoreData.yaml'));
    }

    /**
     * clearDefaultCode
     *
     * 26/10/2020 11:59
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function clearDefaultCode()
    {
        if ($this->isDefaultCode()) ProviderFactory::getRepository(AttendanceCode::class)->clearDefaultCode($this);
    }
}
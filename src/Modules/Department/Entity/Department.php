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
namespace App\Modules\Department\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Staff\Entity\Staff;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Department
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Department\Repository\DepartmentRepository")
 * @ORM\Table(name="Department",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={ "name"}),
 *     @ORM\UniqueConstraint(name="abbreviation",columns={ "abbreviation"})}
 * )
 * @UniqueEntity({"name"})
 * @UniqueEntity({"abbreviation"})
 */
class Department extends AbstractEntity
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
     * @ORM\Column(length=16,options={"default": "Learning Area"})
     * @Assert\Choice(callback="getTypeList")
     */
    private $type = "Learning Area";

    /**
     * @var array
     */
    private static $typeList = ['Learning Area', 'Administration'];

    /**
     * @var string
     * @ORM\Column(length=40)
     * @Assert\NotBlank()
     * @Assert\Length(max=40)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=4,name="abbreviation")
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $abbreviation;

    /**
     * @var array
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $subjectListing = [];

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $blurb;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $logo;

    /**
     * @var DepartmentStaff|null
     * @ORM\OneToMany(targetEntity="App\Modules\Department\Entity\DepartmentStaff",mappedBy="department",orphanRemoval=true)
     */
    private $staff;

    /**
     * @var Collection|DepartmentResource[]|null
     * @ORM\OneToMany(targetEntity="DepartmentResource",mappedBy="department",cascade={"persist","remove"},orphanRemoval=true)
     */
    private $resources;

    /**
     * Department constructor.
     */
    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

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
     * @return Department
     */
    public function setId(?string $id): Department
    {
        $this->id = $id;
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
     * @return Department
     */
    public function setType(string $type): Department
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Learning Area';
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
     * @param string $name
     * @return Department
     */
    public function setName(string $name): Department
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @param string $abbreviation
     * @return Department
     */
    public function setAbbreviation(string $abbreviation): Department
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubjectListing(): array
    {
        $this->subjectListing =  $this->subjectListing ?: [];

        foreach($this->subjectListing as $q=>$w) {
            $this->subjectListing[$q] = trim($w);
        }

        return $this->subjectListing;
    }

    /**
     * SubjectListing.
     *
     * @param array $subjectListing
     * @return Department
     */
    public function setSubjectListing(array $subjectListing): Department
    {
        $this->subjectListing = $subjectListing;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBlurb(): ?string
    {
        return $this->blurb;
    }

    /**
     * @param string|null $blurb
     * @return Department
     */
    public function setBlurb(?string $blurb): Department
    {
        $this->blurb = $blurb;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @param string|null $logo
     * @return Department
     */
    public function setLogo(?string $logo): Department
    {
        $this->logo = $logo;
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
     * getStaff
     * @return Collection|null
     */
    public function getStaff(): ?Collection
    {
        if (null === $this->staff)
            $this->staff = new ArrayCollection();

        if ($this->staff instanceof PersistentCollection)
            $this->staff->initialize();

        $iterator = $this->staff->getIterator();

        $iterator->uasort(
            function ($a, $b) {
                return $a->getStaff()->getPerson()->getFullNameReversed() < $b->getStaff()->getPerson()->getFullNameReversed() ? -1 : 1 ;
            }
        );

        $this->staff  = new ArrayCollection(iterator_to_array($iterator, false));

        return $this->staff;
    }

    /**
     * Staff.
     *
     * @param DepartmentStaff|null $staff
     * @return Department
     */
    public function setStaff(?Collection $staff): Department
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * getResources
     * @return Collection
     */
    public function getResources(): Collection
    {
        if (null === $this->resources)
            $this->resources = new ArrayCollection();
        if ($this->resources instanceof PersistentCollection)
            $this->resources->initialize();

        return $this->resources;
    }

    /**
     * Resources.
     *
     * @param Collection|null $resources
     * @return Department
     */
    public function setResources(?Collection $resources): Department
    {
        $this->resources = $resources;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' (' . $this->getAbbreviation() . ')';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 17/07/2020 09:58
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getName(),
            'abbr' => $this->getAbbreviation(),
            'type' => TranslationHelper::translate($this->getType()),
            'canDelete' => true,
            'staff' => $this->getStaffNames(),
        ];
    }

    /**
     * getStaffNames
     * @return string
     */
    public function getStaffNames(): string
    {
        $result = [];
        foreach($this->getStaff() as $staff)
            $result[] = $staff->getStaff()->getPerson()->getFullNameReversed();
        if (empty($result))
            $result[] = TranslationHelper::translate('None', [], 'Department');
        return implode("\n<br/>", $result);
    }

    /**
     * isHeadTeacher
     *
     * 12/11/2020 08:34
     * @param Staff $staff
     * @return bool
     */
    public function isHeadTeacher(Staff $staff): bool
    {
        $result = $this->getStaff()->filter(function (DepartmentStaff $ds) use ($staff) {
            if ($ds->getStaff()->isEqualTo($staff) && $ds->isHeadTeacher())
                return $ds;
        });
        return $result->count() > 0;
    }
}

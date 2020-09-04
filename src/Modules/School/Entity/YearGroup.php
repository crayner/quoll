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
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use App\Modules\Staff\Entity\Staff;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YearGroup
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\YearGroupRepository")
 * @ORM\Table(name="YearGroup",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation", columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="sort_order", columns={"sort_order"})},
 *     indexes={@ORM\Index(name="head_of_year", columns={"head_of_year"})})
 * @UniqueEntity({"name"})
 * @UniqueEntity({"abbreviation"})
 * @UniqueEntity({"sortOrder"})
 */
class YearGroup extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var string|null
     * @ORM\Column(length=15)
     * @Assert\NotBlank()
     */
    private ?string $name;

    /**
     * @var string|null
     * @ORM\Column(length=4,name="abbreviation")
     * @Assert\NotBlank()
     */
    private ?string $abbreviation;

    /**
     * @var int|null
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=999)
    ")
     */
    private ?int $sortOrder;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="head_of_year",referencedColumnName="id")
     * @Assert\Valid
     */
    private ?Staff $headOfYear;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return YearGroup
     */
    public function setId(?string $id): YearGroup
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
     * @return YearGroup
     */
    public function setName(?string $name): YearGroup
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
     * @param string|null $abbreviation
     * @return YearGroup
     */
    public function setAbbreviation(?string $abbreviation): YearGroup
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return intval($this->sortOrder);
    }

    /**
     * @param int $sortOrder
     * @return YearGroup
     */
    public function setSortOrder(int $sortOrder): YearGroup
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getHeadOfYear(): ?Staff
    {
        return $this->headOfYear;
    }

    /**
     * @param Staff|null $headOfYear
     * @return YearGroup
     */
    public function setHeadOfYear(?Staff $headOfYear): YearGroup
    {
        $this->headOfYear = $headOfYear;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'short') {
            return [
                $this->getId(),
                $this->getName(),
                $this->getAbbreviation(),
                $this->getHeadOfYear() ? $this->getHeadOfYear()->getFullName() : '',
            ];
        }
        return [
            'name' => $this->getName(),
            'abbr' => $this->getAbbreviation(),
            'canDelete' => $this->canDelete(),
            'head' => $this->getHeadOfYear() ? $this->getHeadOfYear()->getFullName() : '',
            'sortOrder' => $this->getSortOrder(),
        ];
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(YearGroup::class)->canDelete($this);
    }

    /**
     * coreData
     * @return array|string[]
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/YearGroupCoreData.yaml'));
    }

    /**
     * getNextSortOrder
     * @return int
     * 2/06/2020 16:53
     */
    public static function getNextSortOrder(): int
    {
        return ProviderFactory::getRepository(YearGroup::class)->findNextSortOrder();
    }
}

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
 *     @ORM\UniqueConstraint(name="sort_order", columns={"sort_order"})})
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
     * getSortOrder
     *
     * 6/10/2020 15:12
     * @return int
     */
    public function getSortOrder(): int
    {
        return isset($this->sortOrder) ? intval($this->sortOrder) : self::getNextSortOrder();
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
            ];
        }
        return [
            'name' => $this->getName(),
            'abbr' => $this->getAbbreviation(),
            'canDelete' => $this->canDelete(),
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

    /**
     * getNextYearGroup
     *
     * 10/10/2020 09:24
     * @return YearGroup|null
     */
    public function getNextYearGroup(): ?YearGroup
    {
        return ProviderFactory::getRepository(YearGroup::class)->findNextYearGroup($this);
    }

    /**
     * isEqualTo
     *
     * 10/10/2020 09:32
     * @param YearGroup $yearGroup
     * @return bool
     */
    public function isEqualTo(YearGroup $yearGroup): bool
    {
        return $yearGroup->getId() === $this->getId();
    }
}

<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:03
 */
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\ImageRemovalTrait;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\People\Entity\Person;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class House
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\HouseRepository")
 * @ORM\Table(name="House",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation", columns={"abbreviation"})})
 * @UniqueEntity({"abbreviation"})
 * @UniqueEntity({"name"})
 * @ORM\HasLifecycleCallbacks()
 */
class House extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use ImageRemovalTrait;

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
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     * @Assert\Length(max=10)
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(nullable=true,length=191)
     */
    private $logo;

    /**
     * @var array
     * List of Files to be removed on delete.
     */
    private $filePropertyList = [
        'logo',
    ];

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
     * @return House
     */
    public function setId(?string $id): House
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
     * @return House
     */
    public function setName(?string $name): House
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
     * setAbbreviation
     * @param string|null $abbreviation
     * @return House
     */
    public function setAbbreviation(?string $abbreviation): House
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return $this->isFileInPublic($this->logo) ? $this->logo : '/build/static/BlankImageSquare.png';
    }

    /**
     * @param string|null $logo
     * @return House
     */
    public function setLogo(?string $logo): House
    {
        if ($this->isFileInPublic($logo)) {
            $this->logo = $logo;
        }
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
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getName(),
            'logo' => $this->getLogo(),
            'short' => $this->getAbbreviation(),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(House::class)->canDelete($this);
    }
}

<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
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

use App\Manager\EntityInterface;
use App\Manager\Traits\ImageRemovalTrait;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\People\Entity\Person;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class House
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\HouseRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="House", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}), @ORM\UniqueConstraint(name="nameShort", columns={"nameShort"})})
 * @ORM\HasLifecycleCallbacks()
 */
class House implements EntityInterface
{
    use ImageRemovalTrait;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(3) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=30,unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=10,name="nameShort",unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=10)
     */
    private $nameShort;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return House
     */
    public function setId(?int $id): House
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
    public function getNameShort(): ?string
    {
        return $this->nameShort;
    }

    /**
     * setNameShort
     * @param string|null $nameShort
     * @return House
     */
    public function setNameShort(?string $nameShort): House
    {
        $this->nameShort = $nameShort;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return $this->isFileInPublic($this->logo) ? $this->logo : '/build/static/DefaultLogo.png';
    }

    /**
     * @param string|null $logo
     * @return House
     */
    public function setLogo(?string $logo): House
    {
        if ($this->isFileInPublic($logo)) {
            $this->setExistingFile('logo', '/build/static/DefaultLogo.png');
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
        return $this->getName() . ' (' . $this->getNameShort() . ')';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        $result = [
            'name' => $this->getName(),
            'logo' => $this->getLogo(),
            'short' => $this->getNameShort(),
            'canDelete' => $this->canDelete(),
        ];
        return $result;
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return ! ProviderFactory::create(Person::class)->isHouseInUse($this);
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__House` (
                    `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameShort` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`) USING BTREE,
                    UNIQUE KEY `nameShort` (`nameShort`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }

}
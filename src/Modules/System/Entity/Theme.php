<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:49
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Theme
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\ThemeRepository")
 * @ORM\Table(name="Theme")
 */
class Theme extends AbstractEntity
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
     * @ORM\Column(length=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     */
    private $active = 'N';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable")
     */
    private $versionDate;

    /**
     * @var string|null
     * @ORM\Column(length=40)
     */
    private $author;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     */
    private $url;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Theme
     */
    public function setId(?int $id): Theme
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
     * @return Theme
     */
    public function setName(?string $name): Theme
    {
        $this->name = $name;
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
     * @return Theme
     */
    public function setDescription(?string $description): Theme
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return Theme
     */
    public function setActive(?string $active): Theme
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getVersionDate(): ?\DateTimeImmutable
    {
        return $this->versionDate;
    }

    /**
     * VersionDate.
     *
     * @param \DateTimeImmutable|null $versionDate
     * @return Theme
     */
    public function setVersionDate(?\DateTimeImmutable $versionDate): Theme
    {
        $this->versionDate = $versionDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string|null $author
     * @return Theme
     */
    public function setAuthor(?string $author): Theme
    {
        $this->author = $author;
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
     * @return Theme
     */
    public function setUrl(?string $url): Theme
    {
        $this->url = $url;
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
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'versionDate' => $this->versionDate,
            'author' => $this->author,
            'url' => $this->url,
        ];
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Theme` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(30) NOT NULL,
                    `description` CHAR(100) NOT NULL,
                    `active` CHAR(1) NOT NULL DEFAULT 'N',
                    `version_date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
                    `author` CHAR(40) NOT NULL,
                    `url` CHAR(191) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "";
    }

    /**
     * getVersion
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

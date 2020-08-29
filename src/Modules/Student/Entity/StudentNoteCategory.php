<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/05/2020
 * Time: 08:59
 */
namespace App\Modules\Student\Entity;

use App\Manager\AbstractEntity;
use App\Util\StringHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class StudentNoteCategory
 * @package App\Modules\Student\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Student\Repository\StudentNoteCategoryRepository")
 * @ORM\Table(name="StudentNoteCategory")
 */
class StudentNoteCategory extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id = null;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     */
    private ?string $name;

    /**
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $template;

    /**
     * @var bool
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private bool $active = true;

    /**
     * StudentNoteCategory constructor.
     */
    public function __construct()
    {
        $this->setActive('Y');
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
     * @return StudentNoteCategory
     */
    public function setId(?string $id): StudentNoteCategory
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     * @return StudentNoteCategory
     */
    public function setName(?string $name): StudentNoteCategory
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return StudentNoteCategory
     */
    public function setActive(bool $active): StudentNoteCategory
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param null|string $template
     * @return StudentNoteCategory
     */
    public function setTemplate(?string $template): StudentNoteCategory
    {
        $this->template = $template;
        return $this;
    }

    /**
     * toArray
     *
     * 18/08/2020 15:30
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'active' => StringHelper::getYesNo($this->isActive()),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return !$this->isActive();
    }

    /**
     * coreData
     *
     * 18/08/2020 15:30
     * @return array
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/StudentNoteCategoryCoreData.yaml'));
    }
}

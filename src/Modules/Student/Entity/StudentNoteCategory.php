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
use App\Manager\Traits\BooleanList;
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
     */
    private $name;

    /**
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    private $template;

    /**
     * @var string
     * @ORM\Column(length=1,options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $active;

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
        return $this->getActive() === 'Y';
    }

    /**
     * @return string
     */
    public function getActive(): string
    {
        return $this->active = self::checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return StudentNoteCategory
     */
    public function setActive(?string $active): StudentNoteCategory
    {
        $this->active = self::checkBoolean($active);
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

    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'active' => self::getYesNo($this->isActive()),
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

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__StudentNoteCategory` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(30) NOT NULL,
                    `template` longtext NULL DEFAULT NULL,
                    `active` CHAR(1) NOT NULL DEFAULT 'Y',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * coreData
     * @return array|string[]
     */
    public function coreData(): array
    {
        return Yaml::parse("
-
  name: 'Academic'
  active: 'Y'
-
  name: 'Pastoral'
  template: ''
  active: 'Y'
-
  name: 'Behaviour'
  template: ''
  active: 'Y'
-
  name: 'Other'
  template: ''
  active: 'Y'
");
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
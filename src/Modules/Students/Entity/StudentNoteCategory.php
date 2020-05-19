<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/05/2020
 * Time: 08:59
 */

namespace App\Modules\Students\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StudentNoteCategory
 * @package App\Modules\Students\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Students\Repository\StudentNoteCategoryRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="StudentNoteCategory")
 */
class StudentNoteCategory implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(5) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return StudentNoteCategory
     */
    public function setId(?int $id): StudentNoteCategory
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

    public function create(): string
    {
        return "CREATE TABLE `__prefix__StudentNoteCategory` (
                    `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(30) COLLATE ut8mb4_unicode_ci NOT NULL,
                    `template` longtext COLLATE ut8mb4_unicode_ci NULL DEFAULT NULL,
                    `active` varchar(1) COLLATE ut8mb4_unicode_ci NOT NULL DEFAULT 'Y',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public function coreData(): string
    {
        return "INSERT INTO `__prefix__StudentNoteCategory` (`name`, `active`) VALUES
                    ('Academic', 'Y'),
                    ('Pastoral', 'Y'),
                    ('Behaviour', 'Y'),
                    ('Other', 'Y');";
    }
}
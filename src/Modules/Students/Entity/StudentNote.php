<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:22
 */
namespace App\Modules\Students\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class StudentNote
 * @package App\Modules\Students\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Students\Repository\StudentNoteRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="StudentNote",
 *     @ORM\Indexes({@ORM\Index("person", columns={"person"}),
 *     @ORM\Index("student_note_category",columns={"student_note_category"}),
 *     @ORM\Index("person_creator",columns={"person_creator"}})
 * )
 */
class StudentNote implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="bigint", columnDefinition="INT(12) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var StudentNoteCategory|null
     * @ORM\ManyToOne(targetEntity="App\Moduyles\Students\Entity\StudentNoteCategory")
     * @ORM\JoinColumn(name="student_note_category",referencedColumnName="id")
     */
    private $studentNoteCategory;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $title;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $note;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person_creator", referencedColumnName="id", nullable=false)
     */
    private $creator;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $timestamp;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return StudentNote
     */
    public function setId(?int $id): StudentNote
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return StudentNote
     */
    public function setPerson(?Person $person): StudentNote
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return StudentNoteCategory|null
     */
    public function getStudentNoteCategory(): ?StudentNoteCategory
    {
        return $this->studentNoteCategory;
    }

    /**
     * @param StudentNoteCategory|null $studentNoteCategory
     * @return StudentNote
     */
    public function setStudentNoteCategory(?StudentNoteCategory $studentNoteCategory): StudentNote
    {
        $this->studentNoteCategory = $studentNoteCategory;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return StudentNote
     */
    public function setTitle(?string $title): StudentNote
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     * @return StudentNote
     */
    public function setNote(?string $note): StudentNote
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getCreator(): ?Person
    {
        return $this->creator;
    }

    /**
     * @param Person|null $creator
     * @return StudentNote
     */
    public function setCreator(?Person $creator): StudentNote
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTimeImmutable|null $timestamp
     * @return StudentNote
     */
    public function setTimestamp(?\DateTimeImmutable $timestamp): StudentNote
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    public function create(): string
    {
        return "CREATE TABLE `__prefix__StudentNote` (
                    `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                    `note` longtext COLLATE utf8_unicode_ci NOT NULL,
                    `timestamp` datetime DEFAULT NULL COMMENT '(DC2Type: datetime_immutable)',
                    `person` int(10) UNSIGNED DEFAULT NULL,
                    `student_note_category` int(5) UNSIGNED DEFAULT NULL,
                    `person_creator` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `person` (`person`) USING BTREE,
                    KEY `student_note_category` (`student_note_category`) USING BTREE,
                    KEY `person_creator` (`person_creator`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__StudentNote`
  ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT FOREIGN KEY (`student_note_category`) REFERENCES `__prefix__StudentNoteCategory` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT FOREIGN KEY (`person_creator`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    public function coreData(): string
    {
        return '';
    }
}
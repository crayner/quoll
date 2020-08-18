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
 * Date: 5/12/2018
 * Time: 16:22
 */
namespace App\Modules\Student\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class StudentNote
 * @package App\Modules\Student\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Student\Repository\StudentNoteRepository")
 * @ORM\Table(name="StudentNote",
 *     indexes={@ORM\Index("person", columns={"person"}),
 *     @ORM\Index("student_note_category",columns={"student_note_category"}),
 *     @ORM\Index("person_creator",columns={"person_creator"})}
 * )
 */
class StudentNote extends AbstractEntity
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id", nullable=false)
     */
    private ?Person $person;

    /**
     * @var StudentNoteCategory|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\StudentNoteCategory")
     * @ORM\JoinColumn(name="student_note_category",referencedColumnName="id")
     */
    private ?StudentNoteCategory $studentNoteCategory;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private ?string $title;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private ?string $note;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person_creator", referencedColumnName="id", nullable=false)
     */
    private ?Person $creator;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $timestamp;

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
     * @return StudentNote
     */
    public function setId(?string $id): StudentNote
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
     * @return DateTimeImmutable|null
     */
    public function getTimestamp(): ?DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @param DateTimeImmutable|null $timestamp
     * @return StudentNote
     */
    public function setTimestamp(?DateTimeImmutable $timestamp): StudentNote
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
}

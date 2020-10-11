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
 * Time: 16:11
 */
namespace App\Modules\Enrolment\Entity;

use App\Manager\AbstractEntity;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StudentEnrolment
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\StudentRollGroupRepository")
 * @ORM\Table(name="StudentRollGroup",
 *     indexes={@ORM\Index(name="student", columns={"student"}),
 *     @ORM\Index(name="roll_group", columns={"roll_group"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="student_roll_group",columns={"student","roll_group","roll_order"})}
 *    )
 * @UniqueEntity({"student","rollGroup","rollOrder"})
 */
class StudentRollGroup extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var Student|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\Student",inversedBy="studentRollGroups")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Student $student;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\RollGroup\Entity\RollGroup",inversedBy="studentRollGroups")
     * @ORM\JoinColumn(name="roll_group",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?RollGroup $rollGroup;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",nullable=true)
     * @Assert\Range(min=1,max=99)
     */
    private ?int $rollOrder;

    /**
     * StudentEnrolment constructor.
     * @param Student|null $student
     */
    public function __construct(?Student $student = null)
    {
        $this->setStudent($student);
    }

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
     * @return StudentRollGroup
     */
    public function setId(?string $id): StudentRollGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * @param Student|null $student
     * @return StudentRollGroup
     */
    public function setStudent(?Student $student): StudentRollGroup
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return $this->rollGroup;
    }

    /**
     * @param RollGroup|null $rollGroup
     * @return StudentRollGroup
     */
    public function setRollGroup(?RollGroup $rollGroup): StudentRollGroup
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * getRollOrder
     *
     * 8/09/2020 09:27
     * @return int|null
     */
    public function getRollOrder(): ?int
    {
        return $this->rollOrder = isset($this->rollOrder) ? $this->rollOrder : null;
    }

    /**
     * @param int $rollOrder
     * @return StudentRollGroup
     */
    public function setRollOrder(int $rollOrder): StudentRollGroup
    {
        $this->rollOrder = $rollOrder > 0 ? intval($rollOrder) : null;
        return $this;
    }

    /**
     * getName
     * @return string
     * 20/07/2020 09:23
     */
    public function getName(): string
    {
        return $this->getRollGroup() ? $this->getRollGroup()->getName() : '';
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

    /**
     * canDelete
     *
     * 10/09/2020 09:35
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(StudentRollGroup::class)->canDelete($this);
    }

    /**
     * __toString
     *
     * 10/09/2020 11:18
     * @return string
     */
    public function __toString(): string
    {
        $string = '';
        if ($this->getRollGroup()) $string = '('.$this->getRollGroup()->getName().') ';
        if ($this->getStudent()) $string .= $this->getStudent()->getFullName();

        $string .= ' - ' . $this->getId() ?: 'Unknown Student Enrolment';

        return trim($string, ' -:');
    }
}

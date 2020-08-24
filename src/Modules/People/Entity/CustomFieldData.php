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
 * Date: 17/05/2020
 * Time: 15:02
 */
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Manager\EntityInterface;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomFieldData
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\CustomFieldDataRepository")
 * @ORM\Table(name="CustomFieldData",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="staff_field",columns={"staff","custom_field"}),
 *      @ORM\UniqueConstraint(name="student_field",columns={"student","custom_field"}),
 *      @ORM\UniqueConstraint(name="care_giver_field",columns={"care_giver","custom_field"})},
 *     indexes={@ORM\Index(name="staff",columns={"staff"}),
 *      @ORM\Index(name="field",columns={"custom_field"}),
 *      @ORM\Index(name="student",columns={"student"}),
 *      @ORM\Index(name="care_giver",columns={"care_giver"})})
 * @UniqueEntity({"customField","staff"})
 * @UniqueEntity({"customField","student"})
 * @UniqueEntity({"customField","careGiver"})
 */
class CustomFieldData extends AbstractEntity
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
     * @var CustomField|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\CustomField")
     * @ORM\JoinColumn(name="custom_field", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $customField;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff",inversedBy="customData")
     * @ORM\JoinColumn(name="staff",referencedColumnName="id",nullable=true)
     */
    private $staff;

    /**
     * @var Student|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\Student",inversedBy="customData")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",nullable=true)
     */
    private $student;

    /**
     * @var CareGiver|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\CareGiver", inversedBy="customData")
     * @ORM\JoinColumn(name="care_giver",referencedColumnName="id",nullable=true)
     */
    private $careGiver;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $value;

    /**
     * @var boolean
     */
    private $booleanValue;

    /**
     * @var \DateTimeImmutable|null
     */
    private $dateTimeValue;

    /**
     * @var integer|null
     */
    private $integerValue;

    /**
     * CustomFieldData constructor.
     * @param EntityInterface|null $member
     * @param CustomField|null $customField
     */
    public function __construct(?EntityInterface $member = null, ?CustomField $customField = null)
    {
        if ($member instanceof Staff) $this->staff = $member;
        if ($member instanceof Student) $this->student = $member;
        if ($member instanceof CareGiver) $this->careGiver = $member;
        $this->customField = $customField;
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
     * @return CustomFieldData
     */
    public function setId(?string $id): CustomFieldData
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return CustomField|null
     */
    public function getCustomField(): ?CustomField
    {
        return $this->customField;
    }

    /**
     * CustomField.
     *
     * @param CustomField|null $customField
     * @return CustomFieldData
     */
    public function setCustomField(?CustomField $customField): CustomFieldData
    {
        $this->customField = $customField;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * @param Staff|null $staff
     * @return CustomFieldData
     */
    public function setStaff(?Staff $staff): CustomFieldData
    {
        $this->staff = $staff;
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
     * @return CustomFieldData
     */
    public function setStudent(?Student $student): CustomFieldData
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return CareGiver|null
     */
    public function getCareGiver(): ?CareGiver
    {
        return $this->careGiver;
    }

    /**
     * @param CareGiver|null $careGiver
     * @return CustomFieldData
     */
    public function setCareGiver(?CareGiver $careGiver): CustomFieldData
    {
        $this->careGiver = $careGiver;
        return $this;
    }

    /**
     * @return
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Value.
     *
     * @param string $value
     * @return CustomFieldData
     */
    public function setValue(?string $value): CustomFieldData
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBooleanValue(): bool
    {
        return $this->booleanValue = (bool)$this->getValue();
    }

    /**
     * @param bool $booleanValue
     * @return CustomFieldData
     */
    public function setBooleanValue(?bool $booleanValue): CustomFieldData
    {
        $this->booleanValue = (bool)$booleanValue;
        return $this->setValue(strval((bool)$booleanValue));
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateTimeValue(): ?\DateTimeImmutable
    {
        if ($this->getValue() !== null) {
            $this->dateTimeValue = new \DateTimeImmutable($this->getValue());
        }
        return $this->dateTimeValue;
    }

    /**
     * @param \DateTimeImmutable|null $dateTimeValue
     * @return CustomFieldData
     */
    public function setDateTimeValue(?\DateTimeImmutable $dateTimeValue): CustomFieldData
    {
        $this->dateTimeValue = $dateTimeValue;
        if ($dateTimeValue instanceof \DateTimeImmutable) {
            return $this->setValue($dateTimeValue->format('Y-m-d H:i:s'));
        }
        return $this;
    }

    /**
     * @return int|null
     */
    public function getIntegerValue(): ?int
    {
        if ($this->getValue() !== null) {
            $this->integerValue = intval($this->getValue());
        }
        return $this->integerValue;
    }

    /**
     * @param int|null $integerValue
     * @return CustomFieldData
     */
    public function setIntegerValue(?int $integerValue): CustomFieldData
    {
        $this->integerValue = intval($integerValue);
        return $this->setValue(strval($integerValue));
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }
}

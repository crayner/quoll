<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 24/06/2019
 * Time: 15:30
 */
namespace App\Modules\Staff\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class StaffAbsenceType
 * @package App\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Staff\Repository\StaffAbsenceTypeRepository")
 * @ORM\Table(name="StaffAbsenceType",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation", columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="sequence_number", columns={"sequence_number"})})
 * @UniqueEntity({"name"})
 * @UniqueEntity({"abbreviation"})
 * @UniqueEntity({"sequenceNumber"})
 */
class StaffAbsenceType extends AbstractEntity
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
     * @ORM\Column(length=60)
     * @Assert\NotBlank()
     * @Assert\Length(max=60)
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
     * @ORM\Column(length=1,options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $requiresApproval = 'N';

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $reasons;

    /**
     * @var integer
     * @ORM\Column(type="smallint",options={"default": 0})
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=99)
     */
    private $sequenceNumber = 0;

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
     * @return StaffAbsenceType
     */
    public function setId(?string $id): StaffAbsenceType
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
     * Name.
     *
     * @param string|null $name
     * @return StaffAbsenceType
     */
    public function setName(?string $name): StaffAbsenceType
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
     * Abbreviation.
     *
     * @param string|null $abbreviation
     * @return StaffAbsenceType
     */
    public function setAbbreviation(?string $abbreviation): StaffAbsenceType
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * isActive
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return self::checkBoolean($this->active);
    }

    /**
     * Active.
     *
     * @param string|null $active
     * @return StaffAbsenceType
     */
    public function setActive(?string $active): StaffAbsenceType
    {
        $this->active = self::checkBoolean($active);
        return $this;
    }

    /**
     * isRequiresApproval
     * @return bool
     */
    public function isRequiresApproval(): bool
    {
        return $this->getRequiresApproval() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getRequiresApproval(): ?string
    {
        return self::checkBoolean($this->requiresApproval, 'N');
    }

    /**
     * RequiresApproval.
     *
     * @param string|null $requiresApproval
     * @return StaffAbsenceType
     */
    public function setRequiresApproval(?string $requiresApproval): StaffAbsenceType
    {
        $this->requiresApproval = self::checkBoolean($requiresApproval, 'N');
        return $this;
    }

    /**
     * @return array|null
     */
    public function getReasons(): ?array
    {
        return $this->reasons;
    }

    /**
     * Reasons.
     *
     * @param array|null $reasons
     * @return StaffAbsenceType
     */
    public function setReasons(?array $reasons): StaffAbsenceType
    {
        $this->reasons = $reasons;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequenceNumber(): int
    {
        return intval($this->sequenceNumber);
    }

    /**
     * SequenceNumber.
     *
     * @param int|null $sequenceNumber
     * @return StaffAbsenceType
     */
    public function setSequenceNumber(?int $sequenceNumber): StaffAbsenceType
    {
        if (!($this->sequenceNumber > 0 && intval($sequenceNumber) === 0)) {
            $this->sequenceNumber = intval($sequenceNumber);
        }

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
            'id' => $this->getId(),
            'name' => $this->getName(),
            'abbreviation' => $this->getAbbreviation(),
            'reasons' => $this->getReasons(),
            'requiresApproval' => self::getYesNo($this->isRequiresApproval()),
            'active' => self::getYesNo($this->isActive()),
        ];
    }

    /**
     * coreData
     *
     * 26/08/2020 10:42
     * @return array
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/StaffAbsenceTypeCoreData.yaml'));
        
    }
}
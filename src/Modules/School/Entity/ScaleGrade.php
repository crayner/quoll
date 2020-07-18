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
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Provider\ProviderFactory;
use App\Util\StringHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ScaleGrade
 * @package App\Modules\Schoo\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\ScaleGradeRepository")
 * @ORM\Table(name="ScaleGrade",
 *     indexes={@ORM\Index(name="scale",columns={"scale"})},
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="scale_value", columns={"value","scale"}),
*           @ORM\UniqueConstraint(name="scale_sequence", columns={"sequence_number","scale"})})
 * @UniqueEntity({"value","scale"})
 * @UniqueEntity({"sequenceNumber","scale"})
 */
class ScaleGrade extends AbstractEntity
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
     * @var Scale|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Scale",inversedBy="scaleGrades")
     * @ORM\JoinColumn(name="scale", referencedColumnName="id")
     */
    private $scale;

    /**
     * @var string|null
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     * @Assert\Length(max=10)
     */
    private $value;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     * @Assert\Length(max=50)
     * @Assert\NotBlank()
     */
    private $descriptor;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1, max=99999)
     */
    private $sequenceNumber;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 0})
     */
    private $defaultGrade = false;

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
     * @return ScaleGrade
     */
    public function setId(?string $id): ScaleGrade
    {
        $this->id = $id;
        return $this;
    }

    /**
     * getScale
     * @return Scale|null
     */
    public function getScale(): ?Scale
    {
        return $this->scale;
    }

    /**
     * getScaleId
     * @return null|string
     */
    public function getScaleId(): ?string
    {
        return $this->getScale() ? $this->getScale()->getId() : null;
    }

    /**
     * @param Scale|null $scale
     * @return ScaleGrade
     */
    public function setScale(?Scale $scale): ScaleGrade
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return ScaleGrade
     */
    public function setValue(?string $value): ScaleGrade
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescriptor(): ?string
    {
        return $this->descriptor;
    }

    /**
     * @param string|null $descriptor
     * @return ScaleGrade
     */
    public function setDescriptor(?string $descriptor): ScaleGrade
    {
        $this->descriptor = $descriptor;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSequenceNumber(): ?int
    {
        if (intval($this->sequenceNumber) === 0) {
            return ProviderFactory::getRepository(ScaleGrade::class)->nextSequenceNumber($this->getScale());
        }
        return $this->sequenceNumber;
    }

    /**
     * @param int|null $sequenceNumber
     * @return ScaleGrade
     */
    public function setSequenceNumber(?int $sequenceNumber): ScaleGrade
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDefaultGrade(): bool
    {
        return (bool)$this->defaultGrade;
    }

    /**
     * @param bool|null $defaultGrade
     * @return ScaleGrade
     */
    public function setDefaultGrade(?bool $defaultGrade): ScaleGrade
    {
        $this->defaultGrade = (bool)$defaultGrade;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getScale()->__toString() . ': ' . $this->getValue();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'value' => $this->getValue(),
            'descriptor' => $this->getDescriptor(),
            'sequence' => $this->getSequenceNumber(),
            'id' => $this->getId(),
            'scaleId' => $this->getScaleId(),
            'default' => StringHelper::getYesNo($this->isDefaultGrade()),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * coreData
     * @return array
     * 4/07/2020 16:25
     */
    public function coreData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/ScaleGradeCoreData.yaml'));
    }

    /**
     * canDelete
     * @return bool
     * 18/07/2020 09:41
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(ScaleGrade::class)->canDelete($this);
    }
}

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
use App\Util\TranslationHelper;
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

    use BooleanList;

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
     * @Assert\Range(min=0, max=99999)
     */
    private $sequenceNumber;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     */
    private $defaultGrade = 'N';

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
     * defaultGrade
     * @return string|null
     */
    public function isDefaultGrade(): ?string
    {
        return $this->getDefaultGrade() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getDefaultGrade(): ?string
    {
        return $this->defaultGrade = self::checkBoolean($this->defaultGrade, 'N');
    }

    /**
     * @param string|null $defaultGrade
     * @return ScaleGrade
     */
    public function setDefaultGrade(?string $defaultGrade): ScaleGrade
    {
        $this->defaultGrade = self::checkBoolean($defaultGrade, 'N');
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
            'default' => self::getYesNo($this->isDefaultGrade()),
            'canDelete' => ProviderFactory::create(ScaleGrade::class)->canDelete($this),
        ];
    }

    /**
     * create
     * @return array|string[]
     * 2/06/2020 09:20
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__ScaleGrade` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `value` CHAR(10) NOT NULL,
                    `descriptor` CHAR(50) NOT NULL,
                    `sequence_number` smallint DEFAULT NULL,
                    `default_grade` CHAR(1) NOT NULL DEFAULT 'N',
                    `scale` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `id` (`id`,`value`),
                    UNIQUE KEY `scale_value` (`value`,`scale`),
                    UNIQUE KEY `scale_sequence` (`sequence_number`,`scale`),
                    KEY `scale` (`scale`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__ScaleGrade`
                    ADD CONSTRAINT FOREIGN KEY (`scale`) REFERENCES `__prefix__Scale` (`id`);';
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

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

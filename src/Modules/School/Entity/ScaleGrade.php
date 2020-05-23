<?php
/**
 * Created by PhpStorm.
 *
* Quoll
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
 *          @ORM\UniqueConstraint(name="scaleValue", columns={"scale","value"}),
*           @ORM\UniqueConstraint(name="scaleSequence", columns={"scale","sequence_number"})})
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
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Scale", inversedBy="scaleGrades")
     * @ORM\JoinColumn(name="scale", referencedColumnName="id", nullable=false)
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
     * @ORM\Column(length=1, options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
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
     * @return integer
     */
    public function getScaleId(): int
    {
        return $this->getScale() ? intval($this->getScale()->getId()) : 0;
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
     * @return string|null
     */
    public function defaultGradeGrade(): ?string
    {
        return $this->getDefaultGrade() === 'Y' ? true : false;
    }

    /**
     * defaultGrade
     * @return string|null
     */
    public function defaultGrade(): ?string
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
            'scale' => $this->getScaleId(),
            'default' => TranslationHelper::translate($this->defaultGradeGrade() ? 'Yes' : 'No', [], 'messages'),
            'canDelete' => ProviderFactory::create(ScaleGrade::class)->canDelete($this),
        ];
    }

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
                    UNIQUE KEY `scaleValue` (`scale`,`value`),
                    UNIQUE KEY `scaleSequence` (`sequence_number`,`scale`),
                    KEY `scale` (`scale`)
                ) ENGINE=InnoDB AUTO_INCREMENT=330 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__ScaleGrade`
                    ADD CONSTRAINT FOREIGN KEY (`scale`) REFERENCES `__prefix__Scale` (`id`);';
    }

    public function coreData(): array
    {
        return Yaml::parse(<<<JJJ
-
  value: 7
  descriptor: 7
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: 6
  descriptor: 6
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: 5
  descriptor: 5
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: 4
  descriptor: 4
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: 3
  descriptor: 3
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: 2
  descriptor: 2
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: 1
  descriptor: 1
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: "A"
  descriptor: "49–60"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBEE'}
-
  value: "B"
  descriptor: "40–48"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBEE'}
-
  value: "C"
  descriptor: "32–39"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBEE'}
-
  value: "D"
  descriptor: "22–31"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBEE'}
-
  value: "E"
  descriptor: "–21"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBEE'}
-
  value: "A*"
  descriptor: "A*"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "A"
  descriptor: "A"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "B"
  descriptor: "B"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "C"
  descriptor: "C"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "D"
  descriptor: "D"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "E"
  descriptor: "E"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "F"
  descriptor: "F"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "G"
  descriptor: "G"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "U"
  descriptor: "Unclassified"
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "100%"
  descriptor: "100%"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "99%"
  descriptor: "99%"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "98%"
  descriptor: "98%"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "97%"
  descriptor: "97%"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "96%"
  descriptor: "96%"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "95%"
  descriptor: "95%"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "94%"
  descriptor: "94%"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "93%"
  descriptor: "93%"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "92%"
  descriptor: "92%"
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "91%"
  descriptor: "91%"
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "90%"
  descriptor: "90%"
  sequenceNumber: 11
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "89%"
  descriptor: "89%"
  sequenceNumber: 12
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "88%"
  descriptor: "88%"
  sequenceNumber: 13
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "87%"
  descriptor: "87%"
  sequenceNumber: 14
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "86%"
  descriptor: "86%"
  sequenceNumber: 15
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "85%"
  descriptor: "85%"
  sequenceNumber: 16
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "84%"
  descriptor: "84%"
  sequenceNumber: 17
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "83%"
  descriptor: "83%"
  sequenceNumber: 18
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "82%"
  descriptor: "82%"
  sequenceNumber: 19
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "81%"
  descriptor: "81%"
  sequenceNumber: 20
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "80%"
  descriptor: "80%"
  sequenceNumber: 21
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "79%"
  descriptor: "79%"
  sequenceNumber: 22
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "78%"
  descriptor: "78%"
  sequenceNumber: 23
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "77%"
  descriptor: "77%"
  sequenceNumber: 24
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "76%"
  descriptor: "76%"
  sequenceNumber: 25
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "75%"
  descriptor: "75%"
  sequenceNumber: 26
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "74%"
  descriptor: "74%"
  sequenceNumber: 27
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "73%"
  descriptor: "73%"
  sequenceNumber: 28
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "72%"
  descriptor: "72%"
  sequenceNumber: 29
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "71%"
  descriptor: "71%"
  sequenceNumber: 30
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "70%"
  descriptor: "70%"
  sequenceNumber: 31
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "69%"
  descriptor: "69%"
  sequenceNumber: 32
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "68%"
  descriptor: "68%"
  sequenceNumber: 33
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "67%"
  descriptor: "67%"
  sequenceNumber: 34
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "66%"
  descriptor: "66%"
  sequenceNumber: 35
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "65%"
  descriptor: "65%"
  sequenceNumber: 36
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "64%"
  descriptor: "64%"
  sequenceNumber: 37
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "63%"
  descriptor: "63%"
  sequenceNumber: 38
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "62%"
  descriptor: "62%"
  sequenceNumber: 39
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "61%"
  descriptor: "61%"
  sequenceNumber: 40
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "60%"
  descriptor: "60%"
  sequenceNumber: 41
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "59%"
  descriptor: "59%"
  sequenceNumber: 42
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "58%"
  descriptor: "58%"
  sequenceNumber: 43
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "57%"
  descriptor: "57%"
  sequenceNumber: 44
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "56%"
  descriptor: "56%"
  sequenceNumber: 45
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "55%"
  descriptor: "55%"
  sequenceNumber: 46
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "54%"
  descriptor: "54%"
  sequenceNumber: 47
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "53%"
  descriptor: "53%"
  sequenceNumber: 48
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "52%"
  descriptor: "52%"
  sequenceNumber: 49
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "51%"
  descriptor: "51%"
  sequenceNumber: 50
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "50%"
  descriptor: "50%"
  sequenceNumber: 51
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "49%"
  descriptor: "49%"
  sequenceNumber: 52
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "48%"
  descriptor: "48%"
  sequenceNumber: 53
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "47%"
  descriptor: "47%"
  sequenceNumber: 54
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "46%"
  descriptor: "46%"
  sequenceNumber: 55
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "45%"
  descriptor: "45%"
  sequenceNumber: 56
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "44%"
  descriptor: "44%"
  sequenceNumber: 57
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "43%"
  descriptor: "43%"
  sequenceNumber: 58
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "42%"
  descriptor: "42%"
  sequenceNumber: 59
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "41%"
  descriptor: "41%"
  sequenceNumber: 60
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "40%"
  descriptor: "40%"
  sequenceNumber: 61
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "39%"
  descriptor: "39%"
  sequenceNumber: 62
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "38%"
  descriptor: "38%"
  sequenceNumber: 63
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "37%"
  descriptor: "37%"
  sequenceNumber: 64
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "36%"
  descriptor: "36%"
  sequenceNumber: 65
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "35%"
  descriptor: "35%"
  sequenceNumber: 66
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "34%"
  descriptor: "34%"
  sequenceNumber: 67
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "33%"
  descriptor: "33%"
  sequenceNumber: 68
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "32%"
  descriptor: "32%"
  sequenceNumber: 69
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "31%"
  descriptor: "31%"
  sequenceNumber: 70
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "30%"
  descriptor: "30%"
  sequenceNumber: 71
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "29%"
  descriptor: "29%"
  sequenceNumber: 72
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "28%"
  descriptor: "28%"
  sequenceNumber: 73
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "27%"
  descriptor: "27%"
  sequenceNumber: 74
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "26%"
  descriptor: "26%"
  sequenceNumber: 75
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "25%"
  descriptor: "25%"
  sequenceNumber: 76
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "24%"
  descriptor: "24%"
  sequenceNumber: 77
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "23%"
  descriptor: "23%"
  sequenceNumber: 78
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "22%"
  descriptor: "22%"
  sequenceNumber: 79
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "21%"
  descriptor: "21%"
  sequenceNumber: 80
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "20%"
  descriptor: "20%"
  sequenceNumber: 81
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "19%"
  descriptor: "19%"
  sequenceNumber: 82
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "18%"
  descriptor: "18%"
  sequenceNumber: 83
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "17%"
  descriptor: "17%"
  sequenceNumber: 84
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "16%"
  descriptor: "16%"
  sequenceNumber: 85
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "15%"
  descriptor: "15%"
  sequenceNumber: 86
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "14%"
  descriptor: "14%"
  sequenceNumber: 87
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "13%"
  descriptor: "13%"
  sequenceNumber: 88
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "12%"
  descriptor: "12%"
  sequenceNumber: 89
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "11%"
  descriptor: "11%"
  sequenceNumber: 90
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "10%"
  descriptor: "10%"
  sequenceNumber: 91
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "9%"
  descriptor: "9%"
  sequenceNumber: 92
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "8%"
  descriptor: "8%"
  sequenceNumber: 93
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "7%"
  descriptor: "7%"
  sequenceNumber: 94
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "6%"
  descriptor: "6%"
  sequenceNumber: 95
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "5%"
  descriptor: "5%"
  sequenceNumber: 96
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "4%"
  descriptor: "4%"
  sequenceNumber: 97
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "3%"
  descriptor: "3%"
  sequenceNumber: 98
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "2%"
  descriptor: "2%"
  sequenceNumber: 99
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "1%"
  descriptor: "2%"
  sequenceNumber: 100
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "%"
  descriptor: "%"
  sequenceNumber: 101
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "A+"
  descriptor: "A+"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "A"
  descriptor: "A"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "A-"
  descriptor: "A-"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "B+"
  descriptor: "B+"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "B"
  descriptor: "B"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "B-"
  descriptor: "B-"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "C+"
  descriptor: "C+"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "C"
  descriptor: "C"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "C-"
  descriptor: "C-"
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "D+"
  descriptor: "D+"
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "D"
  descriptor: "D"
  sequenceNumber: 11
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "D-"
  descriptor: "D-"
  sequenceNumber: 12
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "E+"
  descriptor: "E+"
  sequenceNumber: 13
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "E"
  descriptor: "E"
  sequenceNumber: 14
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "E-"
  descriptor: "E-"
  sequenceNumber: 15
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "F"
  descriptor: "F"
  sequenceNumber: 16
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "A"
  descriptor: "A"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: "B"
  descriptor: "B"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: "C"
  descriptor: "C"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: "D"
  descriptor: "D"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: "E"
  descriptor: "E"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: "F"
  descriptor: "F"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: 7
  descriptor: "Exceptional  Performance"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: 6
  descriptor: "Well Above Expected Level"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: 5
  descriptor: "Above Expected Level"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: 4
  descriptor: "At Expected Level"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: 3
  descriptor: "Below Expected Level"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: 2
  descriptor: "Well Below Expected Level"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: 1
  descriptor: "Cause For Concern"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: "Complete"
  descriptor: "Work complete"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'Comp'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'Comp'}
-
  value: "Late"
  descriptor: "Work submitted late"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'Comp'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'ICHK'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IB'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GCSE'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 102
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: '%'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 17
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'FLG'}
-
  value: "Incomplete"
  descriptor: "Work incomplete"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'SLG'}
-
  value: 60
  descriptor: 60
  sequenceNumber: 82
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 61
  descriptor: 61
  sequenceNumber: 81
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 62
  descriptor: 62
  sequenceNumber: 80
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 63
  descriptor: 63
  sequenceNumber: 79
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 64
  descriptor: 64
  sequenceNumber: 78
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 65
  descriptor: 65
  sequenceNumber: 77
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 66
  descriptor: 66
  sequenceNumber: 76
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 67
  descriptor: 67
  sequenceNumber: 75
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 68
  descriptor: 68
  sequenceNumber: 74
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 69
  descriptor: 69
  sequenceNumber: 73
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 70
  descriptor: 70
  sequenceNumber: 72
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 71
  descriptor: 71
  sequenceNumber: 71
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 72
  descriptor: 72
  sequenceNumber: 70
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 73
  descriptor: 73
  sequenceNumber: 69
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 74
  descriptor: 74
  sequenceNumber: 68
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 75
  descriptor: 75
  sequenceNumber: 67
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 76
  descriptor: 76
  sequenceNumber: 66
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 77
  descriptor: 77
  sequenceNumber: 65
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 78
  descriptor: 78
  sequenceNumber: 64
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 79
  descriptor: 79
  sequenceNumber: 63
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 80
  descriptor: 80
  sequenceNumber: 62
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 81
  descriptor: 81
  sequenceNumber: 61
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 82
  descriptor: 82
  sequenceNumber: 60
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 83
  descriptor: 83
  sequenceNumber: 59
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 84
  descriptor: 84
  sequenceNumber: 58
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 85
  descriptor: 85
  sequenceNumber: 57
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 86
  descriptor: 86
  sequenceNumber: 56
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 87
  descriptor: 87
  sequenceNumber: 55
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 88
  descriptor: 88
  sequenceNumber: 54
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 89
  descriptor: 89
  sequenceNumber: 53
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 90
  descriptor: 90
  sequenceNumber: 52
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 91
  descriptor: 91
  sequenceNumber: 51
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 92
  descriptor: 92
  sequenceNumber: 50
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 93
  descriptor: 93
  sequenceNumber: 49
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 94
  descriptor: 94
  sequenceNumber: 48
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 95
  descriptor: 95
  sequenceNumber: 47
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 96
  descriptor: 96
  sequenceNumber: 46
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 97
  descriptor: 97
  sequenceNumber: 45
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 98
  descriptor: 98
  sequenceNumber: 44
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 99
  descriptor: 99
  sequenceNumber: 43
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 100
  descriptor: 100
  sequenceNumber: 42
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 101
  descriptor: 101
  sequenceNumber: 41
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 102
  descriptor: 102
  sequenceNumber: 40
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 103
  descriptor: 103
  sequenceNumber: 39
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 104
  descriptor: 104
  sequenceNumber: 38
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 105
  descriptor: 105
  sequenceNumber: 37
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 106
  descriptor: 106
  sequenceNumber: 36
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 107
  descriptor: 107
  sequenceNumber: 35
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 108
  descriptor: 108
  sequenceNumber: 34
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 109
  descriptor: 109
  sequenceNumber: 33
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 110
  descriptor: 110
  sequenceNumber: 32
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 111
  descriptor: 111
  sequenceNumber: 31
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 112
  descriptor: 112
  sequenceNumber: 30
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 113
  descriptor: 113
  sequenceNumber: 29
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 114
  descriptor: 114
  sequenceNumber: 28
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 115
  descriptor: 115
  sequenceNumber: 27
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 116
  descriptor: 116
  sequenceNumber: 26
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 117
  descriptor: 117
  sequenceNumber: 25
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 118
  descriptor: 118
  sequenceNumber: 24
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 119
  descriptor: 119
  sequenceNumber: 23
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 120
  descriptor: 120
  sequenceNumber: 22
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 121
  descriptor: 121
  sequenceNumber: 21
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 122
  descriptor: 122
  sequenceNumber: 20
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 123
  descriptor: 123
  sequenceNumber: 19
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 124
  descriptor: 124
  sequenceNumber: 18
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 125
  descriptor: 125
  sequenceNumber: 17
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 126
  descriptor: 126
  sequenceNumber: 16
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 127
  descriptor: 127
  sequenceNumber: 15
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 128
  descriptor: 128
  sequenceNumber: 14
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 129
  descriptor: 129
  sequenceNumber: 13
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 130
  descriptor: 130
  sequenceNumber: 12
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 131
  descriptor: 131
  sequenceNumber: 11
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 132
  descriptor: 132
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 133
  descriptor: 133
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 134
  descriptor: 134
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 135
  descriptor: 135
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 136
  descriptor: 136
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 137
  descriptor: 137
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 138
  descriptor: 138
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 139
  descriptor: 139
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 140
  descriptor: 140
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: "8A"
  descriptor: "8A"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "8B"
  descriptor: "8B"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "8C"
  descriptor: "8C"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "7A"
  descriptor: "7A"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "7B"
  descriptor: "7B"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "7C"
  descriptor: "7C"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "6A"
  descriptor: "6A"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "6B"
  descriptor: "6B"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "6C"
  descriptor: "6C"
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "5A"
  descriptor: "5A"
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "5B"
  descriptor: "5B"
  sequenceNumber: 11
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "5C"
  descriptor: "5C"
  sequenceNumber: 12
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "4A"
  descriptor: "4A"
  sequenceNumber: 13
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "4B"
  descriptor: "4B"
  sequenceNumber: 14
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "4C"
  descriptor: "4C"
  sequenceNumber: 15
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "B3"
  descriptor: "B3"
  sequenceNumber: 16
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3'}
-
  value: "A"
  descriptor: "A"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "A/B"
  descriptor: "A/B"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "B"
  descriptor: "B"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "B/C"
  descriptor: "B/C"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "C"
  descriptor: "C"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "C/D"
  descriptor: "C/D"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "D"
  descriptor: "D"
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "D/E"
  descriptor: "D/E"
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "E"
  descriptor: "E"
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "E/F"
  descriptor: "E/F"
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "F"
  descriptor: "F"
  sequenceNumber: 11
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "G"
  descriptor: "G"
  sequenceNumber: 12
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: "U"
  descriptor: "Unclassified"
  sequenceNumber: 13
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'GPrd'}
-
  value: 141
  descriptor: 141
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'CAT'}
-
  value: 7
  descriptor: 7
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 6
  descriptor: 6
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 5
  descriptor: 5
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 4
  descriptor: 4
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 3
  descriptor: 3
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 2
  descriptor: 2
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 1
  descriptor: 1
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDS'}
-
  value: 45
  descriptor: 45
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 44
  descriptor: 44
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 43
  descriptor: 43
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 42
  descriptor: 42
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 41
  descriptor: 41
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 40
  descriptor: 40
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 39
  descriptor: 39
  sequenceNumber: 7
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 38
  descriptor: 38
  sequenceNumber: 8
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 37
  descriptor: 37
  sequenceNumber: 9
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 36
  descriptor: 36
  sequenceNumber: 10
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 35
  descriptor: 35
  sequenceNumber: 11
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 34
  descriptor: 34
  sequenceNumber: 12
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 33
  descriptor: 33
  sequenceNumber: 13
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 32
  descriptor: 32
  sequenceNumber: 14
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 31
  descriptor: 31
  sequenceNumber: 15
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 30
  descriptor: 30
  sequenceNumber: 16
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 29
  descriptor: 29
  sequenceNumber: 17
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 28
  descriptor: 28
  sequenceNumber: 18
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 27
  descriptor: 27
  sequenceNumber: 19
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 26
  descriptor: 26
  sequenceNumber: 20
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 25
  descriptor: 25
  sequenceNumber: 21
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 24
  descriptor: 24
  sequenceNumber: 22
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 23
  descriptor: 23
  sequenceNumber: 23
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 22
  descriptor: 22
  sequenceNumber: 24
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 21
  descriptor: 21
  sequenceNumber: 25
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 20
  descriptor: 20
  sequenceNumber: 26
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 19
  descriptor: 19
  sequenceNumber: 27
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 18
  descriptor: 18
  sequenceNumber: 28
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 17
  descriptor: 17
  sequenceNumber: 29
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 16
  descriptor: 16
  sequenceNumber: 30
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 15
  descriptor: 15
  sequenceNumber: 31
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 14
  descriptor: 14
  sequenceNumber: 32
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 13
  descriptor: 13
  sequenceNumber: 33
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 12
  descriptor: 12
  sequenceNumber: 34
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 11
  descriptor: 11
  sequenceNumber: 35
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 10
  descriptor: 10
  sequenceNumber: 36
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 9
  descriptor: 9
  sequenceNumber: 37
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 8
  descriptor: 8
  sequenceNumber: 38
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 7
  descriptor: 7
  sequenceNumber: 39
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 6
  descriptor: 6
  sequenceNumber: 40
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 5
  descriptor: 5
  sequenceNumber: 41
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 4
  descriptor: 4
  sequenceNumber: 42
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 3
  descriptor: 3
  sequenceNumber: 43
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 2
  descriptor: 2
  sequenceNumber: 44
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 1
  descriptor: 1
  sequenceNumber: 45
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'IBDT'}
-
  value: 8
  descriptor: "Level 8"
  sequenceNumber: 1
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3S'}
-
  value: 7
  descriptor: "Level 7"
  sequenceNumber: 2
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3S'}
-
  value: 6
  descriptor: "Level 6"
  sequenceNumber: 3
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3S'}
-
  value: 5
  descriptor: "Level 5"
  sequenceNumber: 4
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3S'}
-
  value: 4
  descriptor: "Level 4"
  sequenceNumber: 5
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3S'}
-
  value: 3
  descriptor: "Level 3"
  sequenceNumber: 6
  defaultGrade: "N"
  scale: {table: 'App\Modules\School\Entity\Scale', reference: 'abbreviation', value: 'KS3S'}
JJJ
    );
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Curriculum\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\School\Entity\ScaleGrade;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RubricColumn
 * @package App\Modules\Curriculum
 * @ORM\Entity(repositoryClass="App\Modules\Curriculum\Repository\RubricColumnRepository")
 * @ORM\Table(name="RubricColumn",
 *     indexes={@ORM\Index(name="rubric", columns={"rubric"})})
 * @ORM\HasLifecycleCallbacks()
 */
class RubricColumn extends AbstractEntity
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
     * @var Rubric|null
     * @ORM\ManyToOne(targetEntity="Rubric")
     * @ORM\JoinColumn(name="rubric", referencedColumnName="id")
     */
    private $rubric;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\Length(max=20)
     */
    private $title;

    /**
     * @var integer
     * @ORM\Column(type="smallint",name="sequenceNumber")
     * @Assert\Range(min=1,max=99)
     */
    private $sequenceNumber;

    /**
     * @var ScaleGrade|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\ScaleGrade")
     * @ORM\JoinColumn(name="scale_grade", referencedColumnName="id")
     */
    private $scaleGrade;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $visualise = 'Y';

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return RubricColumn
     */
    public function setId(?string $id): RubricColumn
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Rubric|null
     */
    public function getRubric(): ?Rubric
    {
        return $this->rubric;
    }

    /**
     * @param Rubric|null $rubric
     * @return RubricColumn
     */
    public function setRubric(?Rubric $rubric): RubricColumn
    {
        $this->rubric = $rubric;
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
     * @return RubricColumn
     */
    public function setTitle(?string $title): RubricColumn
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    /**
     * @param int $sequenceNumber
     * @return RubricColumn
     */
    public function setSequenceNumber(int $sequenceNumber): RubricColumn
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * @return ScaleGrade|null
     */
    public function getScaleGrade(): ?ScaleGrade
    {
        return $this->scaleGrade;
    }

    /**
     * @param ScaleGrade|null $scaleGrade
     * @return RubricColumn
     */
    public function setScaleGrade(?ScaleGrade $scaleGrade): RubricColumn
    {
        $this->scaleGrade = $scaleGrade;
        return $this;
    }

    /**
     * isVisualise
     * @return bool
     * 1/06/2020 12:21
     */
    public function isVisualise(): bool
    {
        return $this->getVisualise() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getVisualise(): ?string
    {
        return self::checkBoolean($this->visualise);
    }

    /**
     * @param string|null $visualise
     * @return RubricColumn
     */
    public function setVisualise(?string $visualise): RubricColumn
    {
        $this->visualise = self::checkBoolean($visualise);
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 1/06/2020 12:54
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array
     * 1/06/2020 12:55
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__RubricColumn` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `rubric` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `scale_grade` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `title` char(20) NOT NULL,
                    `sequenceNumber` smallint NOT NULL,
                    `visualise` char(1) NOT NULL DEFAULT 'Y',
                    PRIMARY KEY (`id`),
                    KEY `rubric` (`rubric`),
                    KEY `scale_grade` (`scale_grade`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 1/06/2020 12:56
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__RubricColumn`
                    ADD CONSTRAINT FOREIGN KEY (`scale_grade`) REFERENCES `__prefix__ScaleGrade` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`rubric`) REFERENCES `__prefix__Rubric` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 1/06/2020 12:56
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
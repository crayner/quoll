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
namespace App\Modules\School\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ScaleGrade
 * @package App\Modules\SchoolAdmin\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\ScaleGradeRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="ScaleGrade",
 *     indexes={@ORM\Index(name="scale",columns={"scale"})},
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="scaleValue", columns={"scale","value"}),
*           @ORM\UniqueConstraint(name="scaleSequence", columns={"scale","sequenceNumber"})})
 * @UniqueEntity({"value","scale"})
 * @UniqueEntity({"sequenceNumber","scale"})
 */
class ScaleGrade implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer", columnDefinition="INT(7) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @ORM\Column(type="integer", name="sequenceNumber", columnDefinition="INT(5)")
     * @Assert\Range(min=0, max=99999)
     */
    private $sequenceNumber;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="isDefault", options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $isDefault = 'N';

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return ScaleGrade
     */
    public function setId(?int $id): ScaleGrade
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
    public function isIsDefault(): ?string
    {
        return $this->getIsDefault() === 'Y' ? true : false;
    }

    /**
     * isDefault
     * @return string|null
     */
    public function isDefault(): ?string
    {
        return $this->getIsDefault() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getIsDefault(): ?string
    {
        return $this->isDefault = self::checkBoolean($this->isDefault, 'N');
    }

    /**
     * @param string|null $isDefault
     * @return ScaleGrade
     */
    public function setIsDefault(?string $isDefault): ScaleGrade
    {
        $this->isDefault = self::checkBoolean($isDefault, 'N');
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
            'default' => $this->isDefault() ? TranslationsHelper::translate('Yes', [], 'messages') : TranslationsHelper::translate('No', [], 'messages'),
            'canDelete' => ProviderFactory::create(ScaleGrade::class)->canDelete($this),
        ];
    }

    public function create(): string
    {
        return 'CREATE TABLE `gibbonscalegrade` (
                    `id` int(7) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `value` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `descriptor` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `sequenceNumber` int(5) DEFAULT NULL,
                    `isDefault` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'N\',
                    `scale` int(5) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `id` (`id`,`value`),
                    UNIQUE KEY `scaleValue` (`scale`,`value`) USING BTREE,
                    UNIQUE KEY `scaleSequence` (`sequenceNumber`,`scale`),
                    KEY `scale` (`scale`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=330 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `gibbonscalegrade`
  ADD CONSTRAINT `gibbonscalegrade_ibfk_1` FOREIGN KEY (`scale`) REFERENCES `gibbonscale` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return 'INSERT INTO `gibbonscalegrade` (`value`, `descriptor`, `sequenceNumber`, `isDefault`, `scale`) VALUES
(\'7\', \'7\', 1, \'N\', 1),
(\'6\', \'6\', 2, \'N\', 1),
(\'5\', \'5\', 3, \'N\', 1),
(\'4\', \'4\', 4, \'N\', 1),
(\'3\', \'3\', 5, \'N\', 1),
(\'2\', \'2\', 6, \'N\', 1),
(\'1\', \'1\', 7, \'N\', 1),
(\'A\', \'49–60\', 1, \'N\', 2),
(\'B\', \'40–48\', 2, \'N\', 2),
(\'C\', \'32–39\', 3, \'N\', 2),
(\'D\', \'22–31\', 4, \'N\', 2),
(\'E\', \'–21\', 5, \'N\', 2),
(\'A*\', \'A*\', 1, \'N\', 3),
(\'A\', \'A\', 2, \'N\', 3),
(\'B\', \'B\', 3, \'N\', 3),
(\'C\', \'C\', 4, \'N\', 3),
(\'D\', \'D\', 5, \'N\', 3),
(\'E\', \'E\', 6, \'N\', 3),
(\'F\', \'F\', 7, \'N\', 3),
(\'G\', \'G\', 8, \'N\', 3),
(\'U\', \'Unclassified\', 9, \'N\', 3),
(\'100%\', \'100%\', 1, \'N\', 4),
(\'99%\', \'99%\', 2, \'N\', 4),
(\'98%\', \'98%\', 3, \'N\', 4),
(\'97%\', \'97%\', 4, \'N\', 4),
(\'96%\', \'96%\', 5, \'N\', 4),
(\'95%\', \'95%\', 6, \'N\', 4),
(\'94%\', \'94%\', 7, \'N\', 4),
(\'93%\', \'93%\', 8, \'N\', 4),
(\'92%\', \'92%\', 9, \'N\', 4),
(\'91%\', \'91%\', 10, \'N\', 4),
(\'90%\', \'90%\', 11, \'N\', 4),
(\'89%\', \'89%\', 12, \'N\', 4),
(\'88%\', \'88%\', 13, \'N\', 4),
(\'87%\', \'87%\', 14, \'N\', 4),
(\'86%\', \'86%\', 15, \'N\', 4),
(\'85%\', \'85%\', 16, \'N\', 4),
(\'84%\', \'84%\', 17, \'N\', 4),
(\'83%\', \'83%\', 18, \'N\', 4),
(\'82%\', \'82%\', 19, \'N\', 4),
(\'81%\', \'81%\', 20, \'N\', 4),
(\'80%\', \'80%\', 21, \'N\', 4),
(\'79%\', \'79%\', 22, \'N\', 4),
(\'78%\', \'78%\', 23, \'N\', 4),
(\'77%\', \'77%\', 24, \'N\', 4),
(\'76%\', \'76%\', 25, \'N\', 4),
(\'75%\', \'75%\', 26, \'N\', 4),
(\'74%\', \'74%\', 27, \'N\', 4),
(\'73%\', \'73%\', 28, \'N\', 4),
(\'72%\', \'72%\', 29, \'N\', 4),
(\'71%\', \'71%\', 30, \'N\', 4),
(\'70%\', \'70%\', 31, \'N\', 4),
(\'69%\', \'69%\', 32, \'N\', 4),
(\'68%\', \'68%\', 33, \'N\', 4),
(\'67%\', \'67%\', 34, \'N\', 4),
(\'66%\', \'66%\', 35, \'N\', 4),
(\'65%\', \'65%\', 36, \'N\', 4),
(\'64%\', \'64%\', 37, \'N\', 4),
(\'63%\', \'63%\', 38, \'N\', 4),
(\'62%\', \'62%\', 39, \'N\', 4),
(\'61%\', \'61%\', 40, \'N\', 4),
(\'60%\', \'60%\', 41, \'N\', 4),
(\'59%\', \'59%\', 42, \'N\', 4),
(\'58%\', \'58%\', 43, \'N\', 4),
(\'57%\', \'57%\', 44, \'N\', 4),
(\'56%\', \'56%\', 45, \'N\', 4),
(\'55%\', \'55%\', 46, \'N\', 4),
(\'54%\', \'54%\', 47, \'N\', 4),
(\'53%\', \'53%\', 48, \'N\', 4),
(\'52%\', \'52%\', 49, \'N\', 4),
(\'51%\', \'51%\', 50, \'N\', 4),
(\'50%\', \'50%\', 51, \'N\', 4),
(\'49%\', \'49%\', 52, \'N\', 4),
(\'48%\', \'48%\', 53, \'N\', 4),
(\'47%\', \'47%\', 54, \'N\', 4),
(\'46%\', \'46%\', 55, \'N\', 4),
(\'45%\', \'45%\', 56, \'N\', 4),
(\'44%\', \'44%\', 57, \'N\', 4),
(\'43%\', \'43%\', 58, \'N\', 4),
(\'42%\', \'42%\', 59, \'N\', 4),
(\'41%\', \'41%\', 60, \'N\', 4),
(\'40%\', \'40%\', 61, \'N\', 4),
(\'39%\', \'39%\', 62, \'N\', 4),
(\'38%\', \'38%\', 63, \'N\', 4),
(\'37%\', \'37%\', 64, \'N\', 4),
(\'36%\', \'36%\', 65, \'N\', 4),
(\'35%\', \'35%\', 66, \'N\', 4),
(\'34%\', \'34%\', 67, \'N\', 4),
(\'33%\', \'33%\', 68, \'N\', 4),
(\'32%\', \'32%\', 69, \'N\', 4),
(\'31%\', \'31%\', 70, \'N\', 4),
(\'30%\', \'30%\', 71, \'N\', 4),
(\'29%\', \'29%\', 72, \'N\', 4),
(\'28%\', \'28%\', 73, \'N\', 4),
(\'27%\', \'27%\', 74, \'N\', 4),
(\'26%\', \'26%\', 75, \'N\', 4),
(\'25%\', \'25%\', 76, \'N\', 4),
(\'24%\', \'24%\', 77, \'N\', 4),
(\'23%\', \'23%\', 78, \'N\', 4),
(\'22%\', \'22%\', 79, \'N\', 4),
(\'21%\', \'21%\', 80, \'N\', 4),
(\'20%\', \'20%\', 81, \'N\', 4),
(\'19%\', \'19%\', 82, \'N\', 4),
(\'18%\', \'18%\', 83, \'N\', 4),
(\'17%\', \'17%\', 84, \'N\', 4),
(\'16%\', \'16%\', 85, \'N\', 4),
(\'15%\', \'15%\', 86, \'N\', 4),
(\'14%\', \'14%\', 87, \'N\', 4),
(\'13%\', \'13%\', 88, \'N\', 4),
(\'12%\', \'12%\', 89, \'N\', 4),
(\'11%\', \'11%\', 90, \'N\', 4),
(\'10%\', \'10%\', 91, \'N\', 4),
(\'9%\', \'9%\', 92, \'N\', 4),
(\'8%\', \'8%\', 93, \'N\', 4),
(\'7%\', \'7%\', 94, \'N\', 4),
(\'6%\', \'6%\', 95, \'N\', 4),
(\'5%\', \'5%\', 96, \'N\', 4),
(\'4%\', \'4%\', 97, \'N\', 4),
(\'3%\', \'3%\', 98, \'N\', 4),
(\'2%\', \'2%\', 99, \'N\', 4),
(\'1%\', \'2%\', 100, \'N\', 4),
(\'%\', \'%\', 101, \'N\', 4),
(\'A+\', \'A+\', 1, \'N\', 5),
(\'A\', \'A\', 2, \'N\', 5),
(\'A-\', \'A-\', 3, \'N\', 5),
(\'B+\', \'B+\', 4, \'N\', 5),
(\'B\', \'B\', 5, \'N\', 5),
(\'B-\', \'B-\', 6, \'N\', 5),
(\'C+\', \'C+\', 7, \'N\', 5),
(\'C\', \'C\', 8, \'N\', 5),
(\'C-\', \'C-\', 9, \'N\', 5),
(\'D+\', \'D+\', 10, \'N\', 5),
(\'D\', \'D\', 11, \'N\', 5),
(\'D-\', \'D-\', 12, \'N\', 5),
(\'E+\', \'E+\', 13, \'N\', 5),
(\'E\', \'E\', 14, \'N\', 5),
(\'E-\', \'E-\', 15, \'N\', 5),
(\'F\', \'F\', 16, \'N\', 5),
(\'A\', \'A\', 1, \'N\', 6),
(\'B\', \'B\', 2, \'N\', 6),
(\'C\', \'C\', 3, \'N\', 6),
(\'D\', \'D\', 4, \'N\', 6),
(\'E\', \'E\', 5, \'N\', 6),
(\'F\', \'F\', 6, \'N\', 6),
(\'7\', \'Exceptional  Performance\', 1, \'N\', 7),
(\'6\', \'Well Above Expected Level\', 2, \'N\', 7),
(\'5\', \'Above Expected Level\', 3, \'N\', 7),
(\'4\', \'At Expected Level\', 4, \'N\', 7),
(\'3\', \'Below Expected Level\', 5, \'N\', 7),
(\'2\', \'Well Below Expected Level\', 6, \'N\', 7),
(\'1\', \'Cause For Concern\', 7, \'N\', 7),
(\'Complete\', \'Work complete\', 1, \'N\', 8),
(\'Incomplete\', \'Work incomplete\', 3, \'N\', 8),
(\'Late\', \'Work submitted late\', 2, \'N\', 8),
(\'Incomplete\', \'Work incomplete\', 8, \'N\', 7),
(\'Incomplete\', \'Work incomplete\', 8, \'N\', 1),
(\'Incomplete\', \'Work incomplete\', 10, \'N\', 3),
(\'Incomplete\', \'Work incomplete\', 102, \'N\', 4),
(\'Incomplete\', \'Work incomplete\', 17, \'N\', 5),
(\'Incomplete\', \'Work incomplete\', 7, \'N\', 6),
(\'60\', \'60\', 82, \'N\', 9),
(\'61\', \'61\', 81, \'N\', 9),
(\'62\', \'62\', 80, \'N\', 9),
(\'63\', \'63\', 79, \'N\', 9),
(\'64\', \'64\', 78, \'N\', 9),
(\'65\', \'65\', 77, \'N\', 9),
(\'66\', \'66\', 76, \'N\', 9),
(\'67\', \'67\', 75, \'N\', 9),
(\'68\', \'68\', 74, \'N\', 9),
(\'69\', \'69\', 73, \'N\', 9),
(\'70\', \'70\', 72, \'N\', 9),
(\'71\', \'71\', 71, \'N\', 9),
(\'72\', \'72\', 70, \'N\', 9),
(\'73\', \'73\', 69, \'N\', 9),
(\'74\', \'74\', 68, \'N\', 9),
(\'75\', \'75\', 67, \'N\', 9),
(\'76\', \'76\', 66, \'N\', 9),
(\'77\', \'77\', 65, \'N\', 9),
(\'78\', \'78\', 64, \'N\', 9),
(\'79\', \'79\', 63, \'N\', 9),
(\'80\', \'80\', 62, \'N\', 9),
(\'81\', \'81\', 61, \'N\', 9),
(\'82\', \'82\', 60, \'N\', 9),
(\'83\', \'83\', 59, \'N\', 9),
(\'84\', \'84\', 58, \'N\', 9),
(\'85\', \'85\', 57, \'N\', 9),
(\'86\', \'86\', 56, \'N\', 9),
(\'87\', \'87\', 55, \'N\', 9),
(\'88\', \'88\', 54, \'N\', 9),
(\'89\', \'89\', 53, \'N\', 9),
(\'90\', \'90\', 52, \'N\', 9),
(\'91\', \'91\', 51, \'N\', 9),
(\'92\', \'92\', 50, \'N\', 9),
(\'93\', \'93\', 49, \'N\', 9),
(\'94\', \'94\', 48, \'N\', 9),
(\'95\', \'95\', 47, \'N\', 9),
(\'96\', \'96\', 46, \'N\', 9),
(\'97\', \'97\', 45, \'N\', 9),
(\'98\', \'98\', 44, \'N\', 9),
(\'99\', \'99\', 43, \'N\', 9),
(\'100\', \'100\', 42, \'N\', 9),
(\'101\', \'101\', 41, \'N\', 9),
(\'102\', \'102\', 40, \'N\', 9),
(\'103\', \'103\', 39, \'N\', 9),
(\'104\', \'104\', 38, \'N\', 9),
(\'105\', \'105\', 37, \'N\', 9),
(\'106\', \'106\', 36, \'N\', 9),
(\'107\', \'107\', 35, \'N\', 9),
(\'108\', \'108\', 34, \'N\', 9),
(\'109\', \'109\', 33, \'N\', 9),
(\'110\', \'110\', 32, \'N\', 9),
(\'111\', \'111\', 31, \'N\', 9),
(\'112\', \'112\', 30, \'N\', 9),
(\'113\', \'113\', 29, \'N\', 9),
(\'114\', \'114\', 28, \'N\', 9),
(\'115\', \'115\', 27, \'N\', 9),
(\'116\', \'116\', 26, \'N\', 9),
(\'117\', \'117\', 25, \'N\', 9),
(\'118\', \'118\', 24, \'N\', 9),
(\'119\', \'119\', 23, \'N\', 9),
(\'120\', \'120\', 22, \'N\', 9),
(\'121\', \'121\', 21, \'N\', 9),
(\'122\', \'122\', 20, \'N\', 9),
(\'123\', \'123\', 19, \'N\', 9),
(\'124\', \'124\', 18, \'N\', 9),
(\'125\', \'125\', 17, \'N\', 9),
(\'126\', \'126\', 16, \'N\', 9),
(\'127\', \'127\', 15, \'N\', 9),
(\'128\', \'128\', 14, \'N\', 9),
(\'129\', \'129\', 13, \'N\', 9),
(\'130\', \'130\', 12, \'N\', 9),
(\'131\', \'131\', 11, \'N\', 9),
(\'132\', \'132\', 10, \'N\', 9),
(\'133\', \'133\', 9, \'N\', 9),
(\'134\', \'134\', 8, \'N\', 9),
(\'135\', \'135\', 7, \'N\', 9),
(\'136\', \'136\', 6, \'N\', 9),
(\'137\', \'137\', 5, \'N\', 9),
(\'138\', \'138\', 4, \'N\', 9),
(\'139\', \'139\', 3, \'N\', 9),
(\'140\', \'140\', 2, \'N\', 9),
(\'8A\', \'8A\', 1, \'N\', 10),
(\'8B\', \'8B\', 2, \'N\', 10),
(\'8C\', \'8C\', 3, \'N\', 10),
(\'7A\', \'7A\', 4, \'N\', 10),
(\'7B\', \'7B\', 5, \'N\', 10),
(\'7C\', \'7C\', 6, \'N\', 10),
(\'6A\', \'6A\', 7, \'N\', 10),
(\'6B\', \'6B\', 8, \'N\', 10),
(\'6C\', \'6C\', 9, \'N\', 10),
(\'5A\', \'5A\', 10, \'N\', 10),
(\'5B\', \'5B\', 11, \'N\', 10),
(\'5C\', \'5C\', 12, \'N\', 10),
(\'4A\', \'4A\', 13, \'N\', 10),
(\'4B\', \'4B\', 14, \'N\', 10),
(\'4C\', \'4C\', 15, \'N\', 10),
(\'B3\', \'B3\', 16, \'N\', 10),
(\'A\', \'A\', 1, \'N\', 11),
(\'A/B\', \'A/B\', 2, \'N\', 11),
(\'B\', \'B\', 3, \'N\', 11),
(\'B/C\', \'B/C\', 4, \'N\', 11),
(\'C\', \'C\', 5, \'N\', 11),
(\'C/D\', \'C/D\', 6, \'N\', 11),
(\'D\', \'D\', 7, \'N\', 11),
(\'D/E\', \'D/E\', 8, \'N\', 11),
(\'E\', \'E\', 9, \'N\', 11),
(\'E/F\', \'E/F\', 10, \'N\', 11),
(\'F\', \'F\', 11, \'N\', 11),
(\'G\', \'G\', 12, \'N\', 11),
(\'U\', \'Unclassified\', 13, \'N\', 11),
(\'141\', \'141\', 1, \'N\', 9),
(\'7\', \'7\', 1, \'N\', 12),
(\'6\', \'6\', 2, \'N\', 12),
(\'5\', \'5\', 3, \'N\', 12),
(\'4\', \'4\', 4, \'N\', 12),
(\'3\', \'3\', 5, \'N\', 12),
(\'2\', \'2\', 6, \'N\', 12),
(\'1\', \'1\', 7, \'N\', 12),
(\'45\', \'45\', 1, \'N\', 13),
(\'44\', \'44\', 2, \'N\', 13),
(\'43\', \'43\', 3, \'N\', 13),
(\'42\', \'42\', 4, \'N\', 13),
(\'41\', \'41\', 5, \'N\', 13),
(\'40\', \'40\', 6, \'N\', 13),
(\'39\', \'39\', 7, \'N\', 13),
(\'38\', \'38\', 8, \'N\', 13),
(\'37\', \'37\', 9, \'N\', 13),
(\'36\', \'36\', 10, \'N\', 13),
(\'35\', \'35\', 11, \'N\', 13),
(\'34\', \'34\', 12, \'N\', 13),
(\'33\', \'33\', 13, \'N\', 13),
(\'32\', \'32\', 14, \'N\', 13),
(\'31\', \'31\', 15, \'N\', 13),
(\'30\', \'30\', 16, \'N\', 13),
(\'29\', \'29\', 17, \'N\', 13),
(\'28\', \'28\', 18, \'N\', 13),
(\'27\', \'27\', 19, \'N\', 13),
(\'26\', \'26\', 20, \'N\', 13),
(\'25\', \'25\', 21, \'N\', 13),
(\'24\', \'24\', 22, \'N\', 13),
(\'23\', \'23\', 23, \'N\', 13),
(\'22\', \'22\', 24, \'N\', 13),
(\'21\', \'21\', 25, \'N\', 13),
(\'20\', \'20\', 26, \'N\', 13),
(\'19\', \'19\', 27, \'N\', 13),
(\'18\', \'18\', 28, \'N\', 13),
(\'17\', \'17\', 29, \'N\', 13),
(\'16\', \'16\', 30, \'N\', 13),
(\'15\', \'15\', 31, \'N\', 13),
(\'14\', \'14\', 32, \'N\', 13),
(\'13\', \'13\', 33, \'N\', 13),
(\'12\', \'12\', 34, \'N\', 13),
(\'11\', \'11\', 35, \'N\', 13),
(\'10\', \'10\', 36, \'N\', 13),
(\'9\', \'9\', 37, \'N\', 13),
(\'8\', \'8\', 38, \'N\', 13),
(\'7\', \'7\', 39, \'N\', 13),
(\'6\', \'6\', 40, \'N\', 13),
(\'5\', \'5\', 41, \'N\', 13),
(\'4\', \'4\', 42, \'N\', 13),
(\'3\', \'3\', 43, \'N\', 13),
(\'2\', \'2\', 44, \'N\', 13),
(\'1\', \'1\', 45, \'N\', 13),
(\'8\', \'Level 8\', 1, \'N\', 14),
(\'7\', \'Level 7\', 2, \'N\', 14),
(\'6\', \'Level 6\', 3, \'N\', 14),
(\'5\', \'Level 5\', 4, \'N\', 14),
(\'4\', \'Level 4\', 5, \'N\', 14),
(\'3\', \'Level 3\', 6, \'N\', 14);';
    }
}
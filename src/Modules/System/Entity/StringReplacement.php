<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 24/11/2018
 * Time: 16:16
 */
namespace App\Modules\System\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StringReplacement
 * @package App\Modules\System\Entity
 * @ORM\Entity(repositoryClass="App\Modules\System\Repository\StringReplacementRepository")
 * @ORM\Table(name="String")
 */
class StringReplacement extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @return array
     */
    public static function getModeList(): array
    {
        return self::$modeList;
    }

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

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
     * @return StringReplacement
     */
    public function setId(?string $id): StringReplacement
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=100)
     * @Assert\Length(max=100)
     * @Assert\NotBlank()
     */
    private $original;

    /**
     * @return string|null
     */
    public function getOriginal(): ?string
    {
        return $this->original;
    }

    /**
     * setOriginal
     * @param string|null $original
     * @return $this
     * 3/06/2020 14:19
     */
    public function setOriginal(?string $original): StringReplacement
    {
        $this->original = mb_substr($original, 0, 100);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=100)
     * @Assert\Length(max=100)
     * @Assert\NotBlank()
     */
    private $replacement;

    /**
     * @return string|null
     */
    public function getReplacement(): ?string
    {
        return $this->replacement;
    }

    /**
     * setReplacement
     *
     * @param string|null $replacement
     * @return StringReplacement
     */
    public function setReplacement(?string $replacement): StringReplacement
    {
        $this->replacement = mb_substr($replacement, 0, 100);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=8)
     * @Assert\Choice(callback="getModeList")
     */
    private $mode = 'Whole';

    /**
     * @var array
     */
    private static $modeList = [
        'Whole',
        'Partial',
    ];

    /**
     * @return string|null
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * setMode
     *
     * @param string|null $mode
     * @return StringReplacement
     */
    public function setMode(?string $mode): StringReplacement
    {
        $this->mode = in_array($mode, self::getModeList()) ? $mode : null ;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1, name="case_sensitive")
     * @Assert\Choice(callback="getBooleanList")
     */
    private $caseSensitive = 'N';

    /**
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->getCaseSensitive() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getCaseSensitive(): string
    {
        return $this->caseSensitive = self::checkBoolean($this->caseSensitive, 'N');
}

    /**
     * setCaseSensitive
     *
     * @param string|null $caseSensitive
     * @return StringReplacement
     */
    public function setCaseSensitive(?string $caseSensitive): StringReplacement
    {
        $this->caseSensitive = self::checkBoolean($caseSensitive, 'N');
        return $this;
    }

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", options={"default": "0"})
     * @Assert\Range(max="99", min="1")
     */
    private $priority;

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * Priority.
     *
     * @param int|null $priority
     * @return StringReplacement
     */
    public function setPriority(?int $priority): StringReplacement
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        $result =  (array) $this;
        $x = [];
        foreach($result as $q=>$w){
            $x[str_replace("\x00App\Modules\System\Entity\StringReplacement\x00", '', $q)] = $w;
        }
        return $x;
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__String` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `original` CHAR(100) NOT NULL,
                    `replacement` CHAR(100) NOT NULL,
                    `mode` CHAR(8) NOT NULL,
                    `case_sensitive` CHAR(1) NOT NULL,
                    `priority` smallint DEFAULT 0 NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

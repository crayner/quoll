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
namespace App\Modules\IndividualNeed\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AlertLevel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class INPersonDescriptor
 * @package App\Modules\IndividualNeed\Entity
 * @ORM\Entity(repositoryClass="App\Modules\IndividualNeed\Repository\INPersonDescriptorRepository")
 * @ORM\Table(name="INPersonDescriptor",
 *     indexes={@ORM\Index(name="person",columns={"person"}),
 *     @ORM\Index(name="in_descriptor",columns={"in_descriptor"}),
 *     @ORM\Index(name="alert_level",columns={"alert_level"})})
 */
class INPersonDescriptor extends AbstractEntity
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     */
    private $person;

    /**
     * @var INDescriptor|null
     * @ORM\ManyToOne(targetEntity="INDescriptor")
     * @ORM\JoinColumn(name="in_descriptor", referencedColumnName="id")
     */
    private $inDescriptor;

    /**
     * @var AlertLevel|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AlertLevel")
     * @ORM\JoinColumn(name="alert_level",referencedColumnName="id")
     */
    private $alertLevel;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return INPersonDescriptor
     */
    public function setId(?string $id): INPersonDescriptor
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
     * @return INPersonDescriptor
     */
    public function setPerson(?Person $person): INPersonDescriptor
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return INDescriptor|null
     */
    public function getInDescriptor(): ?INDescriptor
    {
        return $this->inDescriptor;
    }

    /**
     * @param INDescriptor|null $inDescriptor
     * @return INPersonDescriptor
     */
    public function setInDescriptor(?INDescriptor $inDescriptor): INPersonDescriptor
    {
        $this->inDescriptor = $inDescriptor;
        return $this;
    }

    /**
     * @return AlertLevel|null
     */
    public function getAlertLevel(): ?AlertLevel
    {
        return $this->alertLevel;
    }

    /**
     * @param AlertLevel|null $alertLevel
     * @return INPersonDescriptor
     */
    public function setAlertLevel(?AlertLevel $alertLevel): INPersonDescriptor
    {
        $this->alertLevel = $alertLevel;
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

    /**
     * create
     * @return array|string[]
     * 9/06/2020 15:00
     */
    public function create(): array
    {
        RETURN ["CREATE TABLE `__prefix__INPersonDescriptor` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `person` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                    `in_descriptor` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                    `alert_level` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                    INDEX `person` (`person`), 
                    INDEX `in_descriptor` (`in_descriptor`), 
                    INDEX `alert_level` (`alert_level`), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 9/06/2020 15:00
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__INPersonDescriptor` 
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`in_descriptor`) REFERENCES `__prefix__INDescriptor` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`alert_level`) REFERENCES `__prefix__AlertLevel` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 9/06/2020 14:52
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}

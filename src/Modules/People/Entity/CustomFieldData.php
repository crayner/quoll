<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
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
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class CustomFieldData
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\CustomFieldDataRepository")
 * @ORM\Table(name="CustomFieldData",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="person_field",columns={"person","custom_field"})},
 *     indexes={@ORM\Index(name="person",columns={"person"}), @ORM\Index(name="field",columns={"custom_field"})})
 * @UniqueEntity(fields={"customField","person"})
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
     */
    private $customField;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     */
    private $peron;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $value;

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
     * @return Person|null
     */
    public function getPeron(): ?Person
    {
        return $this->peron;
    }

    /**
     * Peron.
     *
     * @param Person|null $peron
     * @return CustomFieldData
     */
    public function setPeron(?Person $peron): CustomFieldData
    {
        $this->peron = $peron;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Value.
     *
     * @param string $value
     * @return CustomFieldData
     */
    public function setValue(string $value): CustomFieldData
    {
        $this->value = $value;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }

    public function create(): array
    {
        return ["CREATE TABLE  `__prefix__CustomFieldData` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `custom_field` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person` CHAR(36) DEFAULT NULL,
                    `value` longtext COLLATE utf8mb4_general_ci,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `person_field` (`person`,`custom_field`),
                    KEY `person` (`person`),
                    KEY `field` (`custom_field`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__CustomFieldData`
                    ADD CONSTRAINT FOREIGN KEY (`custom_field`) REFERENCES `__prefix__CustomField` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}

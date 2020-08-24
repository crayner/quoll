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
 * Time: 12:19
 */
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Security\Entity\SecurityRole;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomFields
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\CustomFieldRepository")
 * @ORM\Table(name="CustomField",
 *     indexes={@ORM\Index(name="display_order",columns={"display_order"})})
 * @App\Modules\People\Validator\CustomFieldOptions()
 */
class CustomField extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=32)
     * @Assert\Length(max=32)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=191)
     * @Assert\Length(max=191)
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private $active = true;

    /**
     * @var string|null
     * @ORM\Column(length=32)
     * @Assert\Choice(callback="getFieldTypeList")
     */
    private $fieldType;

    /**
     * @var string[]
     */
    private static $fieldTypeList = [
        'short_string',
        'text',
        'integer',
        'boolean',
        'choice',
        'date',
        'time',
        'date_time',
    ];

    /**
     * @var array|null
     * @ORM\Column(type="json",nullable=true)
     */
    private $options;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array")
     * @Assert\Choice(callback="getCategoriesList",multiple=true))
     */
    private $categories;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private $required = true;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private $dataUpdater = true;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 0})
     */
    private $applicationForm = false;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 0})
     */
    private $publicRegistrationForm = false;

    /**
     * @var int
     * @ORM\Column(type="smallint",options={"default": 0})
     */
    private $displayOrder = 0;

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
     * @return CustomField
     */
    public function setId(?string $id): CustomField
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
     * @return CustomField
     */
    public function setName(?string $name): CustomField
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Description.
     *
     * @param string|null $description
     * @return CustomField
     */
    public function setDescription(?string $description): CustomField
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool|null $active
     * @return CustomField
     */
    public function setActive(?bool $active): CustomField
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    /**
     * FieldType.
     *
     * @param string|null $fieldType
     * @return CustomField
     */
    public function setFieldType(?string $fieldType): CustomField
    {
        $this->fieldType = $fieldType;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getFieldTypeList(): array
    {
        return self::$fieldTypeList;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Options.
     *
     * @param array|null $options
     * @return CustomField
     */
    public function setOptions(?array $options): CustomField
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories ?: [];
    }

    /**
     * Categories.
     *
     * @param array|null $categories
     * @return CustomField
     */
    public function setCategories(?array $categories): CustomField
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getCategoriesList(): array
    {
        return ['Student','Staff','Care Giver'];
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return (bool)$this->required;
    }

    /**
     * @param bool|null $required
     * @return CustomField
     */
    public function setRequired(?bool $required): CustomField
    {
        $this->required = (bool)$required;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDataUpdater(): bool
    {
        return (bool)$this->dataUpdater;
    }

    /**
     * @param bool|null $dataUpdater
     * @return CustomField
     */
    public function setDataUpdater(?bool $dataUpdater): CustomField
    {
        $this->dataUpdater = (bool)$dataUpdater;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isApplicationForm(): bool
    {
        return (bool)$this->applicationForm;
    }

    /**
     * @param bool|null $applicationForm
     * @return CustomField
     */
    public function setApplicationForm(?bool $applicationForm): CustomField
    {
        $this->applicationForm = (bool)$applicationForm;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublicRegistrationForm(): bool
    {
        return (bool)$this->publicRegistrationForm;
    }

    /**
     * @param bool|null $publicRegistrationForm
     * @return CustomField
     */
    public function setPublicRegistrationForm(?bool $publicRegistrationForm): CustomField
    {
        $this->publicRegistrationForm = (bool) $publicRegistrationForm;
        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     * @return CustomField
     */
    public function setDisplayOrder(int $displayOrder): CustomField
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 29/07/2020 10:30
     */
    public function toArray(?string $name = null): array
    {
        $isRoles = [];
        foreach(self::getCategoriesList() as $role) {
            $isRoles['isCategory' . ucfirst($role)] = $this->isCategory($role);
        }
        return array_merge([
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'fieldType' => TranslationHelper::translate('customfield.fieldtype.' . strtolower($this->getFieldType()), [], 'People'),
            'active' => TranslationHelper::translate($this->isActive() ? 'Yes' : 'No', [], 'messages'),
            'isActive' => $this->isActive(),
            'categories' => $this->getCategoryNames(),
            'isCategoryStaff' => $this->isCategory('Staff'),
            'isCategoryStudent' => $this->isCategory('Student'),
            'isCategoryCareGiver' => $this->isCategory('Care Giver'),
            'isCategorySystem' => $this->isCategory('System'),
            'isCategoryContact' => $this->isCategory('Contact'),
            'canDelete' => $this->canDelete(),
        ], $isRoles);
    }

    /**
     * getCategoryNames
     * @return string
     * 29/07/2020 10:37
     */
    protected function getCategoryNames()
    {
        $roles = '';
        foreach($this->getCategories() as $role) {
            $roles .= TranslationHelper::translate('customfield.categories.' . strtolower($role), [], 'People') . ', ';
        }
        return trim($roles, ', ');
    }

    /**
     * isCategory
     * @param string $category
     * @return bool
     */
    protected function isCategory(string $category): bool
    {
        return in_array($category, $this->getCategories());
    }

    /**
     * isEqualTo
     * @param CustomField $field
     * @return bool
     * 30/07/2020 12:40
     */
    public function isEqualTo(CustomField $field): bool
    {
        return $field->getId() === $this->getId();
    }

    /**
     * canDelete
     * @return bool
     * 1/08/2020 08:33
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(CustomField::class)->canDelete($this);
    }
}

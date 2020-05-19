<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomFields
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\CustomFieldRepository")
 * @ORM\Table(name="CustomField",options={"auto_increment": 1})
 * @App\Modules\People\Validator\CustomFieldOptions()
 */
class CustomField implements EntityInterface
{
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
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $active = 'Y';

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
     * @var string[]
     */
    private static $categoriesList = [
        'staff',
        'student',
        'parent',
        'other',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $required = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $dataUpdater = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $applicationForm = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $publicRegistrationForm = 'N';

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
     * @return CustomField
     */
    public function setActive(?string $active): CustomField
    {
        $this->active = self::checkBoolean($active);
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
        return self::$categoriesList;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->getRequired() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getRequired(): ?string
    {
        return self::checkBoolean($this->required);
    }

    /**
     * Required.
     *
     * @param string|null $required
     * @return CustomField
     */
    public function setRequired(?string $required): CustomField
    {
        $this->required = self::checkBoolean($required);
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDataUpdater(): bool
    {
        return $this->getDataUpdater() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getDataUpdater(): ?string
    {
        return self::checkBoolean($this->dataUpdater);
    }

    /**
     * DataUpdater.
     *
     * @param string|null $dataUpdater
     * @return CustomField
     */
    public function setDataUpdater(?string $dataUpdater): CustomField
    {
        $this->dataUpdater = self::checkBoolean($dataUpdater);
        return $this;
    }

    /**
     * @return boolean
     */
    public function isApplicationForm(): bool
    {
        return $this->getApplicationForm() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getApplicationForm(): ?string
    {
        return self::checkBoolean($this->applicationForm, 'N');
    }

    /**
     * ApplicationForm.
     *
     * @param string|null $applicationForm
     * @return CustomField
     */
    public function setApplicationForm(?string $applicationForm): CustomField
    {
        $this->applicationForm = self::checkBoolean($applicationForm, 'N');
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublicRegistrationForm(): bool
    {
        return $this->getPublicRegistrationForm() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getPublicRegistrationForm(): ?string
    {
        return self::checkBoolean($this->publicRegistrationForm, 'N');
    }

    /**
     * PublicRegistrationForm.
     *
     * @param string|null $publicRegistrationForm
     * @return CustomField
     */
    public function setPublicRegistrationForm(?string $publicRegistrationForm): CustomField
    {
        $this->publicRegistrationForm = self::checkBoolean($publicRegistrationForm, 'N');
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        $isRoles = [];
        foreach(self::getCategoriesList() as $role) {
            $isRoles['isCategory' . ucfirst($role)] = $this->isCategory($role);
        }
        return array_merge([
            'id' => $this->getId(),
            'name' => $this->getName(),
            'fieldType' => TranslationHelper::translate('customfield.fieldtype.' . $this->getFieldType(), [], 'People'),
            'active' => TranslationHelper::translate($this->isActive() ? 'Yes' : 'No', [], 'messages'),
            'isActive' => $this->isActive(),
            'categories' => $this->getCategoryNames(),
        ], $isRoles);
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__CustomField` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `active` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `field_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `options` json DEFAULT NULL,
                    `categories` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
                    `required` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `data_updater` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `application_form` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `public_registration_form` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public function coreData(): string
    {
        return '';
    }
    
    protected function getCategoryNames()
    {
        $roles = '';
        foreach($this->getCategories() as $role) {
            $roles .= TranslationHelper::translate('customfield.categories.' . $role, [], 'People') . ', ';
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
}
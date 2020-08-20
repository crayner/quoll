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
 * Time: 12:52
 */
namespace App\Modules\People\Provider;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\CustomFieldData;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\Collection;

/**
 * Class CustomFieldProvider
 * @package App\Modules\People\Provider
 */
class CustomFieldProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = CustomField::class;

    /**
     * validateCustomFields
     * @param string $category
     * @param Collection $customFields
     * @param EntityInterface $member
     * @param string $usage
     * @return Collection
     * 29/07/2020 14:14
     */
    public function validateCustomFields(string $category, Collection $customFields, EntityInterface $member, string $usage = ''): Collection
    {
        $fields = $this->getRepository()->findByCategoryUsage($category, $usage);

        return $customFields;
    }

    /**
     * canDelete
     * @param CustomField $field
     * @return bool
     * 1/08/2020 08:35
     */
    public function canDelete(CustomField $field): bool
    {
        if ($field->isActive()) return false;
        return ProviderFactory::getRepository(CustomFieldData::class)->countCustomField($field) === 0;
    }

    /**
     * hasCustomFields
     *
     * 20/08/2020 08:56
     * @param string $category
     * @return bool
     */
    public function hasCustomFields(string $category): bool
    {
        return $this->getRepository()->countByCategory($category) > 0;
    }
}

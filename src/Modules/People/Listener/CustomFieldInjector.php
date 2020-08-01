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
 * Date: 30/07/2020
 * Time: 09:12
 */
namespace App\Modules\People\Listener;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\CustomFieldData;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Class CustomFieldInjector
 * @package App\Modules\People\Listener
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CustomFieldInjector
{
    /**
     * onPostLoad
     * @param LifecycleEventArgs $args
     * 30/07/2020 09:19
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Staff)
            $this->injectCustomFields($entity, 'Staff');
        if ($entity instanceof CareGiver)
            $this->injectCustomFields($entity, 'Care Giver');
        if ($entity instanceof Student)
            $this->injectCustomFields($entity, 'Student');
    }

    /**
     * injectCustomFields
     * @param EntityInterface $entity
     * @param string $category
     */
    private function injectCustomFields(EntityInterface $entity, string $category)
    {
        $fields = ProviderFactory::getRepository(CustomField::class)->findByCategoryUsage($category);

        foreach($entity->getCustomData() as $customData) {
            foreach($fields as $q=>$field) {
                if ($field->isEqualto($customData->getCustomField())) {
                    unset($fields[$q]);
                    break;
                }
            }
        }

        foreach($fields as $field) {
            $entity->addCustomData(new CustomFieldData($entity, $field));
        }
    }
}

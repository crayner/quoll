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
 * Date: 30/08/2020
 * Time: 07:45
 */
namespace App\Modules\People\Listener;

use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Staff\Entity\Staff;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Class PersonListener
 * @package App\Modules\People\Listener
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonListener
{
    /**
     * preUpdate
     *
     * 30/08/2020 09:04
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->checkPersonValid($args);
        $this->checkSubEntity($args);
    }

    /**
     * prePersist
     *
     * 30/08/2020 07:59
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->checkPersonValid($args);
    }

    /**
     * checkPersonValid
     *
     * 30/08/2020 07:51
     * @param LifecycleEventArgs $args
     */
    private function checkPersonValid(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Person) return;

        if ($entity->getContact() === null) {
            $entity->setContact(new Contact($entity));
        }
        if ($entity->getSecurityUser() === null) {
            $entity->setSecurityUser(new SecurityUser($entity));
        }
        if ($entity->getSecurityUser()->getPerson() === null) {
            $entity->reflectSecurityUser($entity->getSecurityUser());
        }
        if ($entity->getPersonalDocumentation() === null) {
            $entity->setPersonalDocumentation(new PersonalDocumentation($entity));
        }
    }

    /**
     * checkSubEntity
     *
     * 30/08/2020 09:06
     * @param PreUpdateEventArgs $args
     */
    private function checkSubEntity(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof SecurityUser || $entity instanceof Contact || $entity instanceof PersonalDocumentation || $entity instanceof Student || $entity instanceof CareGiver || $entity instanceof Staff) {
            $changes = $args->getEntityChangeSet();
            if (key_exists('person',$changes) && $changes['person'][1] === null) {
                $uw = $args->getObjectManager()->getUnitOfWork();
                // Clear changes
                $uw->clearEntityChangeSet(spl_object_hash($entity));
            }
        }
    }
}

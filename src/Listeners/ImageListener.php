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
 * Date: 27/04/2020
 * Time: 11:41
 */

namespace App\Listeners;

use App\Modules\People\Entity\Person;
use App\Util\ImageHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Class ImageListener
 * @package App\Listeners
 */
class ImageListener implements EventSubscriber
{
    /**
     * @var bool 
     */
    private $remove = false;
    
    /**
     * getSubscribedEvents
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * postUpdate
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handlePersonalImages($args);
    }

    /**
     * postRemove
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->setRemove(true);
        $this->handlePersonalImages($args);
    }

    /**
     * handlePersonalImages
     * @param LifecycleEventArgs $args
     */
    private function handlePersonalImages(LifecycleEventArgs $args)
    {
        if (!($person = $args->getObject()) instanceof Person)
            return;

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $uow->computechangeSets(); // do not compute changes if inside a preUpdate listener
        $changeSet = $uow->getEntitychangeSet($person);

        if (key_exists('image_240', $changeSet)) {
            ImageHelper::deleteImage($changeSet['image_240'][0]);
        }

        if (key_exists('birthCertificateScan', $changeSet)) {
            ImageHelper::deleteImage($changeSet['birthCertificateScan'][0]);
        }

        if (key_exists('nationalIDCardScan', $changeSet)) {
            ImageHelper::deleteImage($changeSet['nationalIDCardScan'][0]);
        }

        if (key_exists('citizenship1PassportScan', $changeSet)) {
            ImageHelper::deleteImage($changeSet['citizenship1PassportScan'][0]);
        }
        
        if ($this->isRemove()) 
        {
            ImageHelper::deleteImage($person->getImage240(false));
            ImageHelper::deleteImage($person->getNationalIDCardScan());
            ImageHelper::deleteImage($person->getBirthCertificateScan());
            ImageHelper::deleteImage($person->getCitizenship1PassportScan());
        }
     }

    /**
     * @return bool
     */
    public function isRemove(): bool
    {
        return $this->remove;
    }

    /**
     * Remove.
     *
     * @param bool $remove
     * @return ImageListener
     */
    public function setRemove(bool $remove): ImageListener
    {
        $this->remove = $remove;
        return $this;
    }
}
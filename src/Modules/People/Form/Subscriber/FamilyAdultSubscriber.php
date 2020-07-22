<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 7/12/2019
 * Time: 14:25
 */

namespace App\Modules\People\Form\Subscriber;

use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Provider\ProviderFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class FamilyAdultSubscriber
 * @package App\Modules\People\Form\Subscriber
 */
class FamilyAdultSubscriber implements EventSubscriberInterface
{
    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    /**
     * onPreSubmit
     * @param PreSubmitEvent $event
     */
    public function onPreSubmit(PreSubmitEvent $event)
    {
        $data = $event->getData();
        $provider = ProviderFactory::create(FamilyMemberCareGiver::class);
        $adults = $provider->getRepository()->findByFamilyWithoutAdult($data['person'], $data['family']);
        if (!empty($adults)) {
            $priority = intval($data['contactPriority']);
            foreach ($adults as $adult)
                if ($adult->getContactPriority() === $priority)
                    $adult->setContactPriority(++$priority);

            $family = $adults[0]->getFamily();
            $provider->persistFlush($family);
        }
    }
}
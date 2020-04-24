<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 13/09/2019
 * Time: 08:52
 */

namespace App\Modules\System\Form\EventListener;

use Kookaburra\SystemAdmin\Entity\NotificationListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;

/**
 * Class NotificationListenerSubscriber
 * @package App\Modules\System\Form\EventListener
 */
class NotificationEventSubscriber implements EventSubscriberInterface
{
    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'addListeners',
        ];
    }

    /**
     * addListeners
     * @param PostSetDataEvent $event
     */
    public function addListeners(PostSetDataEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        foreach($data->getListeners() as $q => $listener)
        {
            $child = $form->get('listeners')->get($q);
            $child
                ->remove('scopeID');
            $child
                ->add('scopeID', ChoiceType::class,
                    [
                        'label' => 'Scope Choices',
                        'placeholder' => ' ',
                        'choices' => NotificationListener::getChainedValues([]),
                        'required' => false,
                        'data' => $listener->getScopeID(),
                    ]
                )
            ;
        }
    }
}
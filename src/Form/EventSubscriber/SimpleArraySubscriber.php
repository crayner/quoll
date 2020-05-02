<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/01/2020
 * Time: 09:42
 */

namespace App\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;

/**
 * Class SimpleArraySubscriber
 * @package App\Form\EventSubscriber
 */
class SimpleArraySubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    /**
     * preSetData
     * @param PreSetDataEvent $event
     */
    public function preSetData(PreSetDataEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        if (!is_array($data))
            $data= [];
        if (count($data) > 0) {
            $last = end($data);
            while (empty(trim($last)) && count($data) > 0) {
                array_pop($data);
                $last = end($data);
            }
        }
        $name = count($data);
        $data[] = '';
        $event->setData($data);
        foreach($form->all() as $child)
            $form->remove($child->getName());
        foreach($data as $q=>$w)
            $form->add($q, TextType::class);
    }

    /**
     * preSubmit
     * @param PreSubmitEvent $event
     */
    public function preSubmit(PreSubmitEvent $event)
    {
        $data = $event->getData();
        if (!is_array($data))
            $data = [];
        if (count($data) > 0) {
            $last = end($data);
            while (empty(trim($last)) && count($data) > 0) {
                array_pop($data);
                $last = end($data);
            }
        }
        $event->setData($data);
        $form = $event->getForm();
        foreach($form->all() as $child)
            $form->remove($child->getName());
        foreach($data as $q=>$w)
            $form->add($q, TextType::class);
    }
}
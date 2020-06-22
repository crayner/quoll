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
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SimpleArraySubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $options;

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
        foreach($form->all() as $child) {
            $form->remove($child->getName());
        }
        foreach($data as $q=>$w) {
            $form->add($q, TextType::class, ['visible_values' => $this->options['visible_values'], 'visible_labels' => $this->options['visible_labels'], 'visible_parent' => $this->options['visible_parent']]);
        }
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

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return SimpleArraySubscriber
     */
    public function setOptions(array $options): SimpleArraySubscriber
    {
        $this->options = $options;
        return $this;
    }
}
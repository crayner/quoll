<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/12/2019
 * Time: 18:39
 */
namespace App\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormEvents;

class DateSettingSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * DateSettingSubscriber constructor.
     * @param $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * getSubscribedEvents
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',

        ];
    }

    /**
     * preSubmit
     * @param PreSubmitEvent $event
     */
    public function preSubmit(PreSubmitEvent $event)
    {
        $data = $event->getData();

        if (empty($data))
            $event->setData(null);

    }
}
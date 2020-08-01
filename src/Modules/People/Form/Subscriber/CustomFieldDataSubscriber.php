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
 * Date: 29/07/2020
 * Time: 10:51
 */
namespace App\Modules\People\Form\Subscriber;

use App\Form\Type\ReactDateType;
use App\Form\Type\ToggleType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CustomFieldDataSubscriber
 * @package App\Modules\People\Form\Subscriber
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CustomFieldDataSubscriber implements EventSubscriberInterface
{
    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    /**
     * onPreSetData
     * @param PreSetDataEvent $event
     * 29/07/2020 14:02
     */
    public function onPreSetData(PreSetDataEvent $event)
    {
        $form = $event->getForm();
        $parent = $event->getData();

        foreach($parent as $key=>$child) {
            if ($key !== $child->getCustomField()->getId()) {
                $form->remove($key);
                unset($parent[$key]);
            }
        }

        foreach($form->all() as $key=>$child) {

            $data = $parent[$key];
            $options = [];
            $options['required'] = false;
            $options['row_style'] = 'standard';
            $options['translation_domain'] = false;
            $field = $data->getCustomField();
            $constraints = [];
            if ($field->isRequired()) {
                $options['required'] = true;
                $constraints[] = new NotBlank();
            }
            $formType = TextType::class;
            $options['label'] = $field->getName();
            $options['help'] = $field->getDescription();
            $name = 'value';
            switch ($field->getFieldType()) {
                case 'choice':
                    $formType = ChoiceType::class;
                    $choices = [];
                    foreach ($field->getOptions() as $value) $choices[$value] = $value;
                    $options['choices'] = $choices;
                    if (!$field->isRequired()) {
                        $options['placeholder'] = ' ';
                        $choices[' '] = ' ';
                    }
                    $constraints[] = new Choice(['choices' => $choices]);
                    break;
                case 'date_time':
                    $options['widget'] = 'string';
                    $options['with_seconds'] = false;
                    $formType = DateTimeType::class;
                    $name = 'dateTimeValue';
                    break;
                case 'date':
                    $formType = ReactDateType::class;
                    $options['input'] = 'datetime_immutable';
                    $name = 'dateTimeValue';
                    break;
                case 'short_string':
                    $constraints[] = new Length(['max' => $field->getOptions()['length']]);
                    break;
                case 'text':
                    $formType = TextareaType::class;
                    $options['attr'] = [
                        'rows' => $field->getOptions()['rows'],
                    ];
                    break;
                case 'boolean':
                    $formType = ToggleType::class;
                    $name = 'booleanValue';
                    break;
                case 'integer':
                    $formType = IntegerType::class;
//                    $options['data'] = $data->getValue();
                    $name = 'integerValue';
                    break;
                case 'time':
//                    $options['data'] = $data->getValue();
                    $name = 'dateTimeValue';
                    $formType = TimeType::class;
                    $options['with_seconds'] = false;
                    break;
                default:
                    throw new \InvalidArgumentException('Do what for ' . $field->getFieldType());
            }
            $options['constraints'] = $constraints;

            $child
                ->add($name, $formType, $options);
        }
    }
}

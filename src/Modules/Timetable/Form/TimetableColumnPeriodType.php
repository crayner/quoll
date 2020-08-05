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
 * Date: 4/08/2020
 * Time: 13:10
 */
namespace App\Modules\Timetable\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use App\Modules\Timetable\Entity\TimetableColumnPeriod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TimetableColumnPeriodType
 * @package App\Modules\Timetable\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableColumnPeriodType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 4/08/2020 13:12
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('columnName', DisplayType::class,
                [
                    'label' => 'Timetable Column',
                    'mapped' => false,
                    'data' => $options['data']->getTimetableColumn()->getName()
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('timeStart', TimeType::class,
                [
                    'label' => 'Start Time',
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                ]
            )
            ->add('timeEnd', TimeType::class,
                [
                    'label' => 'End Time',
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                ]
            )
            ->add('type', EnumType::class,
                [
                    'label' => 'Type',
                    'placeholder' => 'Please select...',
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 4/08/2020 13:11
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Timetable',
                'data_class' => TimetableColumnPeriod::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 4/08/2020 13:10
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

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
 * Time: 09:46
 */
namespace App\Modules\Timetable\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TimetableColumnType
 * @package App\Modules\Timetable\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 4/08/2020 10:03
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timetable', HiddenEntityType::class,
                [
                    'class' => Timetable::class,
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
            ->add('colour', ColorType::class,
                [
                    'label' => 'Header Background Colour',
                ]
            )
            ->add('fontColour', ColorType::class,
                [
                    'label' => 'Header Text Colour',
                ]
            )
            ->add('daysOfWeek', EntityType::class,
                [
                    'label' => 'Link to Calendar Days',
                    'help' => 'Select calendar days to limit this timetable day to those calendar days.  If no days of the week are selected, then the timetable day is free to select any calendar day.',
                    'class' => DaysOfWeek::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Rotate',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->orderBy('d.sortOrder')
                            ->where('d.schoolDay = :true')
                            ->setParameter('true', true)
                        ;
                    },
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 4/08/2020 09:48
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Timetable',
                'data_class' => TimetableDay::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 4/08/2020 09:48
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

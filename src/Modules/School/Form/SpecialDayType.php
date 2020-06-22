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
 * Date: 23/12/2019
 * Time: 12:44
 */
namespace App\Modules\School\Form;

use App\Form\Transform\DateStringTransform;
use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFormType;
use App\Modules\School\Entity\AcademicYearSpecialDay;
use App\Modules\School\Form\Transform\AcademicYearNameTransform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SpecialDayType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SpecialDayType extends AbstractType
{
    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'School',
            'data_class' => AcademicYearSpecialDay::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $day = $options['data'];
        $builder
            ->add('dayHeader', HeaderType::class,
                [
                    'label' => $day->getId() > 0 ? 'Edit Special Day' : 'Add Special Day'  ,
                ]
            )
        ;
        if ($day->getId() !== null) {
            $builder
                ->add('academicYear', DisplayType::class,
                    [
                        'label' => 'Academic Year',
                        'help' => 'This value is locked.'
                    ]
                )
                ->add('date', DisplayType::class,
                    [
                        'label' => 'Date',
                    ]
                )
            ;
            $builder->get('date')->addViewTransformer(new DateStringTransform(true));
        } else {
            $builder
                ->add('academicYear', DisplayType::class,
                    [
                        'label' => 'Academic Year',
                        'help' => 'This value is locked. Change it in the Academic Year screen.'
                    ]
                )
                ->add('date', ReactDateType::class,
                    [
                        'label' => 'Date',
                        'help' => 'Must be unique in the Academic Year.',
                        'input' => 'datetime_immutable',
                    ]
                )
            ;
        }
            $builder
                ->add('type', EnumType::class,
                    [
                        'label' => 'Type',
                        'placeholder' => 'Please select...',
                        'visible_by_choice' => true,
                        'values' => AcademicYearSpecialDay::getTypeList(),
                    ]
                )
                ->add('name', TextType::class,
                    [
                        'label' => 'Name',
                    ]
                )
                ->add('description', TextType::class,
                    [
                        'label' => 'Description',
                        'required' => false,
                    ]
                )
                ->add('schoolOpen', TimeType::class,
                    [
                        'label' => 'School Opens',
                        'required' => false,
                        'input' => 'datetime_immutable',
                        'widget' => 'single_text',
                        'visible_values' => ['Timing Change'],
                    ]
                )
                ->add('schoolStart', TimeType::class,
                    [
                        'label' => 'School Starts',
                        'required' => false,
                        'input' => 'datetime_immutable',
                        'widget' => 'single_text',
                        'visible_values' => ['Timing Change'],
                    ]
                )
                ->add('schoolEnd', TimeType::class,
                    [
                        'label' => 'School Ends',
                        'required' => false,
                        'input' => 'datetime_immutable',
                        'widget' => 'single_text',
                        'visible_values' => ['Timing Change'],
                    ]
                )
                ->add('schoolClose', TimeType::class,
                    [
                        'label' => 'School Closes',
                        'required' => false,
                        'input' => 'datetime_immutable',
                        'widget' => 'single_text',
                        'visible_values' => ['Timing Change'],
                    ]
                )
                ->add('submit', SubmitType::class)
            ;
        $builder->get('academicYear')->addViewTransformer(new AcademicYearNameTransform());
    }
}
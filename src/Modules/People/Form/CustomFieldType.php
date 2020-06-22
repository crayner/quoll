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
 * Date: 18/05/2020
 * Time: 11:01
 */
namespace App\Modules\People\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Form\Transform\CustomFieldOptionsTransform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldType
 * @package App\Modules\People\Form
 */
class CustomFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('header', HeaderType::class,
                [
                    'label' => $options['data']->getId() === null ? 'Add Custom Field' : 'Edit Custom Field',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Custom Field name',
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('Description', TextareaType::class,
                [
                    'label' => 'Description',
                    'attr' => [
                        'row' => 2,
                    ],
                ]
            )
            ->add('fieldType', EnumType::class,
                [
                    'label' => 'Field Type',
                    'placeholder' => 'Please Select...',
                ]
            );
        if ($options['data']->getFieldType() === 'text') {
            $builder
                ->add('options', TextType::class,
                    [
                        'help' => 'Enter the number of rows to display for field Data',
                        'label' => 'Options: Rows',
                    ]
                );
        } else if ($options['data']->getFieldType() === 'short_string') {
            $builder
                ->add('options', TextType::class,
                    [
                        'help' => 'Maximum characters in the short string (max: 191)',
                        'label' => 'Options: Length',
                    ]
                );
        } else if ($options['data']->getFieldType() === 'choice') {
            $builder
                ->add('options', SimpleArrayType::class,
                    [
                        'help' => 'A list of choices for your field.',
                        'label' => 'Options: Choices',
                    ]
                );
        } else {
            $builder
                ->add('options', HiddenType::class);
        }
        $builder
            ->add('required', ToggleType::class,
                [
                    'label' => 'Required',
                    'help' => 'Is this field compulsory?',
                ]
            )
            ->add('categories', EnumType::class,
                [
                    'label' => 'Role Categories',
                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->add('dataUpdater', ToggleType::class,
                [
                    'label' => 'Include in Data Updater?',
                ]
            )
            ->add('applicationForm', ToggleType::class,
                [
                    'label' => 'Include in Application Form?',
                ]
            )
            ->add('publicRegistrationForm', ToggleType::class,
                [
                    'label' => 'Include in Public Registration Form?',
                ]
            )
            ->add('submit', SubmitType::class);

        $builder->get('options')->addModelTransformer(new CustomFieldOptionsTransform($options['data']));
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
       $resolver->setDefaults(
           [
                'data_class' => CustomField::class,
               'translation_domain' => 'People',
           ]
       );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
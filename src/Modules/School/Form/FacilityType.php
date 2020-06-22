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
 * Date: 4/01/2020
 * Time: 17:12
 */
namespace App\Modules\School\Form;

use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\School\Entity\Facility;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FacilityType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FacilityType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 3/06/2020 16:02
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('type', EnumType::class,
                [
                    'label' => 'Facility Type',
                    'placeholder' => TranslationHelper::translate('Please select...', [], 'messages'),
                    'choice_translation_domain' => false,
                    'help' => '{anchor}Manage Facility Types Settings{closeAnchor}',
                    'help_translation_parameters' => ['{anchor}' => '<a href="'.$options['facility_setting_uri'].'">', '{closeAnchor}' => '</a>']
                ]
            )
            ->add('capacity', NumberType::class,
                [
                    'label' => 'Capacity',
                    'required' => false,
                ]
            )
            ->add('computer', ToggleType::class,
                [
                    'label' => 'Teacher Computer',
                ]
            )
            ->add('studentComputers', NumberType::class,
                [
                    'label' => 'Student Computers',
                    'help' => 'How many are there',
                    'required' => false,
                ]
            )
            ->add('projector', ToggleType::class,
                [
                    'label' => 'Projector',
                ]
            )
            ->add('tv', ToggleType::class,
                [
                    'label' => 'TV',
                ]
            )
            ->add('dvd', ToggleType::class,
                [
                    'label' => 'DVD Player',
                ]
            )
            ->add('hifi', ToggleType::class,
                [
                    'label' => 'Hifi',
                ]
            )
            ->add('speakers', ToggleType::class,
                [
                    'label' => 'Speakers',
                ]
            )
            ->add('iwb', ToggleType::class,
                [
                    'label' => 'Interactive White Board',
                ]
            )
            ->add('phoneInt', TextType::class,
                [
                    'label' => 'Extension',
                    'required' => false,
                    'help' => 'Room\'s internal phone number.',
                ]
            )
            ->add('phoneExt', TextType::class,
                [
                    'label' => 'Phone Number',
                    'help' => 'Room\'s external phone number.',
                    'required' => false,
                ]
            )
            ->add('comment', TextareaType::class,
                [
                    'label' => 'Comment',
                    'required' => false,
                    'attr' => [
                        'rows' => 6,
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'translation_domain' => 'messages',
                ]
            )
        ;
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'facility_setting_uri',
            ]
        );
        $resolver->setDefaults(
            [
                'translation_domain' => 'School',
                'data_class' => Facility::class,
            ]
        );
    }
}
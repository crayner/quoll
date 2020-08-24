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
 * Date: 6/12/2019
 * Time: 14:54
 */

namespace App\Modules\People\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FamilyAdultType
 * @package App\Modules\People\Form
 */
class FamilyCareGiverType extends AbstractType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => FamilyMemberCareGiver::class,
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

    /**
     * buildForm
     *
     * 22/08/2020 10:13
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']->getId() !== null) {
            $builder
                ->add('adultEditHeader', HeaderType::class,
                    [
                        'label' => 'Edit Care Giver',
                        'help' => 'This person could be a parent, guardian or emergency contact for any student.',
                    ]
                )
                ->add('adultNote', ParagraphType::class,
                    [
                        'wrapper_class' => 'warning',
                        'help' => 'contact_priority_logic'
                    ]
                )
                ->add('familyName', DisplayType::class,
                    [
                        'label' => 'Family Name',
                        'help' => 'This value cannot be changed',
                        'data' => $options['data']->getFamily()->getName(),
                        'mapped' => false,
                    ]
                )
                ->add('personName', DisplayType::class,
                    [
                        'label' => "Care Giver's Name",
                        'help' => 'This value cannot be changed',
                        'data' => $options['data']->getCareGiver()->getPerson()->formatName('Formal'),
                        'mapped' => false,
                    ]
                )
                ->add('careGiver', HiddenEntityType::class,
                    [
                        'class' => CareGiver::class,
                    ]
                )
            ;
        } else {
            $builder
                ->add('adultEditHeader', HeaderType::class,
                    [
                        'label' => 'Add Care Giver',
                    ]
                )
                ->add('familyName', DisplayType::class,
                    [
                        'label' => 'Family Name',
                        'help' => 'This value cannot be changed',
                        'data' => $options['data']->getFamily()->getName(),
                        'visible_values' => ['showCareGiverAdd'],
                        'visible_parent' => 'family_care_giver_showHideForm',
                        'mapped' => false,
                    ]
                )
                ->add('adultNote', ParagraphType::class,
                    [
                        'visible_values' => ['showCareGiverAdd'],
                        'visible_parent' => 'family_care_giver_showHideForm',
                        'wrapper_class' => 'warning',
                        'help' => 'contact_priority_logic'
                    ]
                )
                ->add('careGiver', AutoSuggestEntityType::class,
                    [
                        'visible_values' => ['showCareGiverAdd'],
                        'visible_parent' => 'family_care_giver_showHideForm',
                        'choice_label' => 'getFullNameReversed',
                        'placeholder' => "Any part of a care giver's name...",
                        'label' => "Care Giver's Name",
                        'class' => CareGiver::class,
                        'query_builder' => ProviderFactory::getRepository(CareGiver::class)->getAllCareGiversQuery()->where('p.status = :full')->setParameter('full', 'Full'),
                    ]
                )
            ;
        }
        $builder
            ->add('family', HiddenEntityType::class,
                [
                    'class' => Family::class,
                ]
            )
            ->add('contactPriority', HiddenType::class)
            ->add('comment', TextareaType::class,
                [
                    'label' => 'Comment'   ,
                    'required' => false,
                    'visible_values' => ['showCareGiverAdd'],
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'attr' => [
                        'rows' => 5,
                        'class' => 'w-full',
                    ],
                ]
            )
            ->add('childDataAccess', ToggleType::class,
                [
                    'label' => 'Data Access?',
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'visible_values' => ['showCareGiverAdd'],
                    'help' => 'Access data on family members?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactCall', ToggleType::class,
                [
                    'label' => 'Contact by phone call?',
                    'visible_values' => ['showCareGiverAdd'],
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'help' => 'Receive non-emergency phone calls from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactSMS', ToggleType::class,
                [
                    'label' => 'Contact by SMS?',
                    'visible_values' => ['showCareGiverAdd'],
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'help' => 'Receive non-emergency SMS messages from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactEmail', ToggleType::class,
                [
                    'label' => 'Contact by Email?',
                    'visible_values' => ['showCareGiverAdd'],
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'help' => 'Receive non-emergency emails from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactMail', ToggleType::class,
                [
                    'label' => 'Contact by Mail?',
                    'visible_values' => ['showCareGiverAdd'],
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'help' => 'Receive postage mail from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'visible_values' => ['showCareGiverAdd'],
                    'visible_parent' => 'family_care_giver_showHideForm',
                    'label' => 'Submit',
                ]
            )
        ;
        if ($options['data']->getId() === null) {
            $builder
                ->add('showHideForm', ToggleType::class,
                    [
                        'label' => 'Add Care Giver to Family',
                        'visible_by_choice' => 'showCareGiverAdd',
                        'mapped' => false,
                        'data' => 'N',
                    ]
                )
            ;
        }
    }
}
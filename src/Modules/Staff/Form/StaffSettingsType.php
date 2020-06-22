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
 * Date: 3/12/2019
 * Time: 12:04
 */

namespace App\Modules\Staff\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Modules\System\Form\SettingsType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StaffSettingsType
 * @package Kookaburra\UserAdmin\Form
 */
class StaffSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('staffAbsenceHeader', HeaderType::class,
                [
                    'label' => 'Staff Absence',
                ]
            )
            ->add('staffAbsenceSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Staff',
                            'name' => 'absenceApprovers',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'Staff',
                            'name' => 'absenceFullDayThreshold',
                            'entry_type' => NumberType::class,
                            'entry_options' => [
                                'attr' => [
                                    'step' => '0.1',
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Staff',
                            'name' => 'absenceHalfDayThreshold',
                            'entry_type' => NumberType::class,
                            'entry_options' => [
                                'attr' => [
                                    'step' => '0.1',
                                ],
                            ],
                        ],
                    ]
                ]
            )
            ->add('staffCoverageHeader', HeaderType::class,
                [
                    'label' => 'Staff Coverage',
                ]
            )
            ->add('staffCoverageSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Staff',
                            'name' => 'substituteTypes',
                            'entry_type' => SimpleArrayType::class,
                        ],
                    ]
                ]
            )
            ->add('notificationHeader', HeaderType::class,
                [
                    'label' => 'Notifications',
                ]
            )
            ->add('notificationSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Staff',
                            'name' => 'absenceNotificationGroups',
                            'entry_type' => TextType::class,
                        ],
                    ]
                ]
            )
            ->add('fieldValuesHeader', HeaderType::class,
                [
                    'label' => 'Field Values',
                ]
            )
            ->add('fieldValueSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Staff',
                            'name' => 'salaryScalePositions',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'Staff',
                            'name' => 'responsibilityPosts',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'Staff',
                            'name' => 'jobOpeningDescriptionTemplate',
                            'entry_type' => CKEditorType::class,
                        ],
                    ]
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            )
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Staff',
                'data_class' => null,
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
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
 * Date: 30/11/2019
 * Time: 15:02
 */
namespace App\Modules\People\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\System\Form\SettingsType;
use App\Validator\SimpleArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PeopleSettingsType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PeopleSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 22/06/2020 08:42
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fieldValueHeader', HeaderType::class,
                [
                    'label' => 'Field Values',
                    'panel' => 'Field Values',
                ]
            )
            ->add('fieldValueSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'People',
                            'name' => 'nationality',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Field Values',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'ethnicity',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Field Values',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'religions',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Field Values',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'residencyStatus',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Field Values',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'departureReasons',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Field Values',
                            ],
                        ],
                    ],
                    'panel' => 'Field Values',
                ]
            )
            ->add('submit2', SubmitType::class,
                [
                    'panel' => 'Field Values',
                ]
            )
            ->add('privacyHeader', HeaderType::class,
                [
                    'label' => 'Privacy Options',
                    'panel' => 'Privacy / Data Options',
                ]
            )
            ->add('privacySettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'People',
                            'name' => 'privacy',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'panel' => 'Privacy / Data Options',
                                'visible_by_choice' => 'privacy_row',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'privacyBlurb',
                            'entry_type' => TextareaType::class,
                            'entry_options' => [
                                'panel' => 'Privacy / Data Options',
                                'attr' => [
                                    'rows' => 6,
                                ],
                                'visible_values' => ['privacy_row'],
                                'visible_parent' => 'people_settings_privacySettings_People__privacy',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'privacyOptions',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Privacy / Data Options',
                                'visible_values' => ['privacy_row'],
                                'visible_parent' => 'people_settings_privacySettings_People__privacy',
                            ],
                        ],
                    ],
                    'panel' => 'Privacy / Data Options',
                ]
            )
            ->add('peopleDataHeader', HeaderType::class,
                [
                    'label' => 'People Data Options',
                    'panel' => 'Privacy / Data Options',
                ]
            )
            ->add('peopleDataSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'People',
                            'name' => 'uniqueEmailAddress',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'panel' => 'Privacy / Data Options',
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'personalBackground',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'panel' => 'Privacy / Data Options',
                            ],
                        ],
                    ],
                    'panel' => 'Privacy / Data Options',
                ]
            )
            ->add('submit1', SubmitType::class,
                [
                    'panel' => 'Privacy / Data Options',
                ]
            )
            ->add('dayTypeHeader', HeaderType::class,
                [
                    'label' => 'Day-Type Options',
                    'panel' => 'Day Type Options',
                ]
            )
            ->add('dayTypeSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'People',
                            'name' => 'dayTypeOptions',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Day Type Options',
                                'attr' => [
                                    'rows' => 6,
                                ],
                                'constraints' => [
                                    new SimpleArray(),
                                ]
                            ],
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'dayTypeText',
                            'entry_type' => TextareaType::class,
                            'entry_options' => [
                                'panel' => 'Day Type Options',
                                'attr' => [
                                    'rows' => 6,
                                ],
                            ],
                        ],
                    ],
                    'panel' => 'Day Type Options',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'panel' => 'Day Type Options',
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
                'translation_domain' => 'People',
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
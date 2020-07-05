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
 * Date: 15/01/2020
 * Time: 16:04
 */
namespace App\Modules\Behaviour\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\System\Form\SettingsType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Class BehaviourSettingsType
 * @package App\Modules\Behaviour\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class BehaviourSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('descriptorsHeader', HeaderType::class,
                [
                    'label' => 'Descriptors',
                    'panel' => 'Descriptors',
                ]
            )
            ->add('descriptorSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Behaviour',
                            'name' => 'enableDescriptors',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'visible_by_choice' => 'enableDescriptors',
                            ],
                        ],
                        [
                            'scope' => 'Behaviour',
                            'name' => 'positiveDescriptors',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'visible_values' => ['enableDescriptors'],
                                'visible_parent' => 'behaviour_settings_descriptorSettings_Behaviour__enableDescriptors'
                            ],
                        ],
                        [
                            'scope' => 'Behaviour',
                            'name' => 'negativeDescriptors',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'visible_values' => ['enableDescriptors'],
                                'visible_parent' => 'behaviour_settings_descriptorSettings_Behaviour__enableDescriptors'
                            ],
                        ],
                    ],
                    'panel' => 'Descriptors',
                ]
            )
            ->add('submit1', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Descriptors',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('levelsHeader', HeaderType::class,
                [
                    'label' => 'Levels',
                    'panel' => 'Levels',
                ]
            )
            ->add('levelSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Behaviour',
                            'name' => 'enableLevels',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'visible_by_choice' => 'enableLevels',
                            ],
                        ],
                        [
                            'scope' => 'Behaviour',
                            'name' => 'levels',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'visible_values' => ['enableLevels'],
                                'visible_parent' => 'behaviour_settings_levelSettings_Behaviour__enableLevels'
                            ],
                        ],
                    ],
                    'panel' => 'Levels',
                ]
            )
            ->add('submit2', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Levels',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('lettersHeader', HeaderType::class,
                [
                    'label' => 'Behaviour Letters',
                    'help' => 'behaviour_letters_help',
                    'help_translation_parameters' => [
                        '{link}' => '<a target="_blank" href="https://www.craigrayner.com/support/administrators/command-line-tools/">',
                        '{closeLink}' => '</a>',
                    ],
                    'help_attr' => [
                        'className' => 'info',
                    ],
                    'panel' => 'Letters',
                ]
            )
            ->add('letterSettings', SettingsType::class,
                [
                    'settings' => $this->buildBehaviourLetters(),
                    'panel' => 'Letters',
                ]
            )
            ->add('submit3', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Letters',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('miscellaneousHeader', HeaderType::class,
                [
                    'label' => 'Miscellaneous',
                    'panel' => 'Miscellaneous',
                ]
            )
            ->add('miscellaneousSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Behaviour',
                            'name' => 'policyLink',
                            'entry_type' => UrlType::class,
                            'entry_options' => [
                                'constraints' => [
                                    new Url(),
                                ],
                            ],
                        ],
                    ],
                    'panel' => 'Miscellaneous',
                ]
            )
            ->add('submit4', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Miscellaneous',
                    'translation_domain' => 'messages',
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
                'data_class' => null,
                'translation_domain' => 'Behaviour',
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
     * buildBehaviourLetters
     * @return array
     */
    private function buildBehaviourLetters(): array
    {
        $result = [];
        $result[] =                         [
            'scope' => 'Behaviour',
            'name' => 'enableBehaviourLetters',
            'entry_type' => ToggleType::class,
            'entry_options' => [
                'visible_by_choice' => 'enableBehaviourLetters',
            ],
        ];

        $colours = [
            1 => 'bg-blue-200',
            2 => 'bg-orange-200',
            3 => 'bg-red-200',
        ];

        for ($i = 1; $i <= 3; $i++) {
            $setting = [
                'scope' => 'Behaviour',
                'name' => 'behaviourLettersLetter'.$i.'Count',
                'entry_type' => EnumType::class,
                'entry_options' => [
                    'visible_values' => ['enableBehaviourLetters'],
                    'visible_parent' => 'behaviour_settings_letterSettings_Behaviour__enableBehaviourLetters',
                    'choice_list_class' => BehaviourSettingsType::class,
                    'choice_list_method' => 'getCountList',
                    'choice_translation_domain' => false,
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0 hover:'. $colours[$i],
                ],
            ];
            $result[] = $setting;
            $setting = [
                'scope' => 'Behaviour',
                'name' => 'behaviourLettersLetter'.$i.'Text',
                'entry_type' => CKEditorType::class,
                'entry_options' => [
                    'visible_values' => ['enableBehaviourLetters'],
                    'visible_parent' => 'behaviour_settings_letterSettings_Behaviour__enableBehaviourLetters',
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0 hover:'. $colours[$i],
                ],
            ];
            $result[] = $setting;
        }
        return $result;
    }

    /**
     * getCountList
     * @return array
     */
    public static function getCountList(): array
    {
        $result = [];
        for($i=1;$i<=20;$i++)
            $result[$i] = $i;
        return $result;
    }
}

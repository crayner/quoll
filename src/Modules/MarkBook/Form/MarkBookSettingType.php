<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 14/01/2020
 * Time: 17:09
 */

namespace App\Modules\MarkBook\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Form\SettingsType;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MarkbookSettingType
 * @package App\Modules\MarkBook\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookSettingType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('featureHeader', HeaderType::class,
                [
                    'label' => 'Features',
                    'panel' => 'Features',
                ]
            )
            ->add('featureSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Mark Book',
                            'name' => 'enableEffort',
                            'entry_type' => ToggleType::class,

                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'enableRubrics',
                            'entry_type' => ToggleType::class,

                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'enableColumnWeighting',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'visible_by_choice' => 'enableColumnWeighting',
                            ],
                        ],
                    ],
                    'panel' => 'Features',
                ]
            )
        ;
        if (ProviderFactory::create(Setting::class)->getSettingByScopeAsInteger('System', 'defaultAssessmentScale')) {
            $builder
                ->add('enableColumnWeighting', ParagraphType::class,
                    [
                        'help' => 'Calculation of cumulative marks and weightings is currently only available when using Percentage as the Default Assessment Scale. This value can be changed in System Settings.',
                        'panel' => 'Features',
                        'wrapper_class' => 'warning flex relative',
                        'visible_values' => ['enableColumnWeighting'],
                    ]
                )
                ->add('featureSettings2', SettingsType::class,
                    [
                        'settings' => [
                            [
                                'scope' => 'Mark Book',
                                'name' => 'enableDisplayCumulativeMarks',
                                'entry_type' => ToggleType::class,
                                'entry_options' => [
                                    'visible_values' => ['enableColumnWeighting'],
                                ],
                            ],
                            [
                                'scope' => 'Mark Book',
                                'name' => 'enableRawAttainment',
                                'entry_type' => ToggleType::class,
                            ],
                            [
                                'scope' => 'Mark Book',
                                'name' => 'enableModifiedAssessment',
                                'entry_type' => ToggleType::class,
                            ],
                        ],
                        'panel' => 'Features',
                    ]
                )
            ;
        }
        $builder
            ->add('submit1', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Features',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('interfaceHeader', HeaderType::class,
                [
                    'label' => 'Interface',
                    'panel' => 'Interface',
                ]
            )
            ->add('interfaceSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Mark Book',
                            'name' => 'markBookType',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'enableGroupByTerm',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'attainmentAlternativeName',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'attainmentAlternativeNameAbrev',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'effortAlternativeName',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'effortAlternativeNameAbrev',
                            'entry_type' => TextType::class,
                        ],
                    ],
                    'panel' => 'Interface',
                ]
            )
            ->add('submit2', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Interface',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('warningsHeader', HeaderType::class,
                [
                    'label' => 'Warnings',
                    'panel' => 'Warnings',
                ]
            )
            ->add('warningsSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showStudentAttainmentWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showStudentEffortWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showParentAttainmentWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showParentEffortWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'personalisedWarnings',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                    'panel' => 'Warnings',
                ]
            )
            ->add('submit3', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Warnings',
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
                'translation_domain' => 'MarkBook',
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
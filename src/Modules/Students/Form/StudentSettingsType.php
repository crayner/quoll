<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/12/2019
 * Time: 16:17
 */

namespace App\Modules\Students\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\Students\Util\StudentHelper;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StudentSettingsType
 * @package App\Modules\Students\Form
 */
class StudentSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('studentNotesHeader', HeaderType::class,
                [
                    'label' => 'Student Notes',
                    'panel' => 'Notes',
                ]
            )
            ->add('studentNotesSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Students',
                            'name' => 'enableStudentNotes',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'panel' => 'Notes',
                            ]
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'noteCreationNotification',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'choice_list_class' => StudentHelper::class,
                                'choice_list_method' => 'getNoteNotificationList',
                                'choice_list_prefix' => 'student.note_notification',
                                'panel' => 'Notes',
                            ],
                        ],
                    ],
                    'panel' => 'Notes',
                ]
            )
            ->add('alertsHeader', HeaderType::class,
                [
                    'label' => 'Alerts',
                    'panel' => 'Alerts',
                ]
            )
            ->add('alertsSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Students',
                            'name' => 'academicAlertLowThreshold',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'panel' => 'Alerts',
                            ],
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'academicAlertMediumThreshold',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'panel' => 'Alerts',
                            ],
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'academicAlertHighThreshold',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'panel' => 'Alerts',
                            ],
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'behaviourAlertLowThreshold',
                            'entry_options' => [
                                'panel' => 'Alerts',
                            ],
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'behaviourAlertMediumThreshold',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'panel' => 'Alerts',
                            ],
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'behaviourAlertHighThreshold',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'panel' => 'Alerts',
                            ],
                        ],
                    ],
                    'panel' => 'Alerts'
                ]
            )
            ->add('miscHeader', HeaderType::class,
                [
                    'label' => 'Miscellaneous',
                    'panel' => 'Miscellaneous',
                ]
            )
            ->add('miscSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Students',
                            'name' => 'extendedBriefProfile',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'panel' => 'Miscellaneous',
                            ],
                        ],
                        [
                            'scope' => 'School Admin',
                            'name' => 'studentAgreementOptions',
                            'entry_type' => SimpleArrayType::class,
                            'entry_options' => [
                                'panel' => 'Miscellaneous',
                                'attr' => [
                                    'rows' => 6,
                                ],
                            ],
                        ],
                    ],
                    'panel' => 'Miscellaneous',
                ]
            )
            ->add('submitMisc', SubmitType::class,
                [
                    'panel' => 'Miscellaneous',
                    'label' => 'Submit'
                ]
            )
            ->add('submitNotes', SubmitType::class,
                [
                    'panel' => 'Notes',
                    'label' => 'Submit'
                ]
            )
            ->add('submitAlerts', SubmitType::class,
                [
                    'panel' => 'Alerts',
                    'label' => 'Submit'
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
                'translation_domain' => 'Students',
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
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
 * Time: 13:43
 */
namespace App\Modules\People\Form;

use App\Form\Type\DateSettingType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UpdaterSettingsType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class UpdaterSettingsType extends AbstractType
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

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 27/06/2020 09:19
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'Staff' => 'Staff',
            'Student' => 'Student',
            'Parent' => 'Parent',
        ];
        $builder
            ->add('updaterSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Data Updater',
                            'name' => 'requiredUpdates',
                            'entry_type' => ToggleType::class,
                            'entry_options' => [
                                'visible_by_choice' => 'require_updates',
                            ],
                        ],
                        [
                            'scope' => 'Data Updater',
                            'name' => 'requiredUpdatesByType',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'visible_values' => ['require_updates'],
                                'visible_parent' => 'updater_settings_updaterSettings_Data_Updater__requiredUpdates',
                                'multiple' => true,
                                'choices' => [
                                    'updater.bytype.family' => 'Family',
                                    'updater.bytype.personal' => 'Personal',
                                    'updater.bytype.medical' => 'Medical',
                                    'updater.bytype.finance' => 'Finance',
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Data Updater',
                            'name' => 'cutoffDate',
                            'entry_type' => DateSettingType::class,
                            'entry_options' => [
                                'visible_values' => ['require_updates'],
                                'visible_parent' => 'updater_settings_updaterSettings_Data_Updater__requiredUpdates',
                            ],
                        ],
                        [
                            'scope' => 'Data Updater',
                            'name' => 'redirectByRoleCategory',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'visible_values' => ['require_updates'],
                                'visible_parent' => 'updater_settings_updaterSettings_Data_Updater__requiredUpdates',
                                'multiple' => true,
                                'choices' => $choices,
                            ],
                        ],
                    ],
                    'panel' => 'Settings'
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'panel' => 'Settings',
                ]
            )
        ;
    }
}
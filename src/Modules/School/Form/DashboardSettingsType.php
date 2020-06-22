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
 * Time: 15:40
 */
namespace App\Modules\School\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DashboardSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facilitySettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'School Admin',
                            'name' => 'staffDashboardDefaultTab',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                                'choice_list_class' => DashboardSettingsType::class,
                                'choice_list_method' => 'getStaffTabList',
                            ],
                        ],
                        [
                            'scope' => 'School Admin',
                            'name' => 'studentDashboardDefaultTab',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                                'choice_list_class' => DashboardSettingsType::class,
                                'choice_list_method' => 'getStudentTabList',
                            ],
                        ],
                        [
                            'scope' => 'School Admin',
                            'name' => 'parentDashboardDefaultTab',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                                'choice_list_class' => DashboardSettingsType::class,
                                'choice_list_method' => 'getParentTabList',
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class)
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
                'translation_domain' => 'School',
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
     * getStaffTabList
     * @return array
     */
    public static function getStaffTabList(): array
    {
        return [
            'Planner'
        ];
    }

    /**
     * getStudentTabList
     * @return array
     */
    public static function getStudentTabList(): array
    {
        return [
            'Planner'
        ];
    }

    /**
     * getParentTabList
     * @return array
     */
    public static function getParentTabList(): array
    {
        return [
            'Learning Overview',
            'Timetable',
            'Activities',
        ];
    }
}


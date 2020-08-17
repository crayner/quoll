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
 * Time: 14:45
 */
namespace App\Modules\Planner\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PlannerSettingType
 * @package App\Modules\Planner\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PlannerAccessSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 12/06/2020 10:33
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accessHeader', HeaderType::class,
                [
                    'label' => 'Access Settings',
                    'panel' => 'Access',
                ]
            )
            ->add('accessSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Planner',
                            'name' => 'makeUnitsPublic',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Planner',
                            'name' => 'shareUnitOutline',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Planner',
                            'name' => 'allowOutcomeEditing',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Planner',
                            'name' => 'sharingDefaultParents',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Planner',
                            'name' => 'sharingDefaultStudents',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                    'panel' => 'Access',
                ]
            )
            ->add('submit2', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Access',
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
                'translation_domain' => 'Planner',
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

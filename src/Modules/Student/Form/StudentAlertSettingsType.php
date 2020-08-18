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
 * Date: 2/12/2019
 * Time: 16:17
 */
namespace App\Modules\Student\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StudentAlertSettingsType
 * @package App\Modules\Student\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentAlertSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('alertsHeader', HeaderType::class,
                [
                    'label' => 'Alerts',
                ]
            )
            ->add('alertsSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Students',
                            'name' => 'academicAlertLowThreshold',
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'academicAlertMediumThreshold',
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'academicAlertHighThreshold',
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'behaviourAlertLowThreshold',
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'behaviourAlertMediumThreshold',
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'behaviourAlertHighThreshold',
                            'entry_type' => IntegerType::class,
                        ],
                    ],
                ]
            )
            ->add('submitAlerts', SubmitType::class)
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
                'translation_domain' => 'Student',
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
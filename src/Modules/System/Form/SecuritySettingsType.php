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
 * Date: 3/09/2019
 * Time: 14:33
 */

namespace App\Modules\System\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class SecuritySettingsType
 * @package App\Modules\System\Form
 */
class SecuritySettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('securitySettingsHeader', HeaderType::class,
                [
                    'label' => 'Security Settings'
                ]
            )
            ->add('passwordPolicyHeader', HeaderType::class,
                [
                    'label' => 'Password Policy',
                    'header_type' => 'h4',
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0',
                ]
            )
            ->add('policySettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyMinLength',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'constraints' => [
                                    new Range(['min' => 8, 'max' => 12]),
                                ],
                                'attr' => [
                                    'min' => 8,
                                    'max' => 12,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyAlpha',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyNumeric',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyNonAlphaNumeric',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                ]
            )
            ->add('miscellaneousHeader', HeaderType::class,
                [
                    'label' => 'Miscellaneous',
                    'header_type' => 'h4',
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0',
                ]
            )
            ->add('miscellaneousSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'sessionDuration',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'constraints' => [
                                    new Range(['min' => 900, 'max' => ini_get("session.gc_maxlifetime") < 43200000 ? ini_get("session.gc_maxlifetime") : 43200000]),
                                ],
                                'attr' => [
                                    'min' => 900,
                                    'max' => ini_get("session.gc_maxlifetime") < 43200000 ? ini_get("session.gc_maxlifetime") : 43200000,
                                ],
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'System',
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
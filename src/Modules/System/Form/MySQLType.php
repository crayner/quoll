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
 * Date: 22/07/2019
 * Time: 15:45
 */

namespace App\Modules\System\Form;

use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\System\Form\Entity\MySQLSettings;
use App\Modules\System\Validator\MySQLConnection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class MySQLType
 * @package App\Form\Installation
 */
class MySQLType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['proceed'] === 'proceed') {
            $builder
                ->add('proceedHelp', ParagraphType::class,
                    [
                        'help' => 'database_build_ready',
                        'wrapper_class' => 'relative w-full info',
                    ]
                )
                ->add('proceed', SubmitType::class,
                    [
                        'label' => 'Proceed',
                        'label_class' => '',
                    ]
                )
                ->add('proceedFlag', HiddenType::class,
                    [
                        'data' => 'Ready to Go',
                        'mapped' => false,
                    ]
                )
            ;
        }

        $builder
            ->add('host', TextType::class,
                [
                    'label' => 'Database Server',
                    'help' => 'Localhost, IP address or domain.',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 255,
                    ],
                    'widget_class' => 'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0',
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('dbname', TextType::class,
                [
                    'label' => 'Database Name',
                    'help' => 'This database will be created if it does not already exist. Collation should be utf8mb4_general_ci.',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 50,
                    ],
                    'widget_class' => 'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0',
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('user', TextType::class,
                [
                    'label' => 'Database Username',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 50,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('password', TextType::class,
                [
                    'label' => 'Database Password',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 50,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('port', TextType::class,
                [
                    'label' => 'Database Port',
                    'help' => 'The standard port for MySQL is 3306. Only change this if the MySQL Server is listening on a different port.',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 5,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('prefix', TextType::class,
                [
                    'label' => 'Database Table Prefix',
                    'help' => 'A prefix added to all table names.  Up to 6 characters in length',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 6,
                    ],
                    'constraints' => [
                        new Length(['max' => 6]),
                    ],
                ]
            )
        ;
        $builder
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
                'data_class' => MySQLSettings::class,
                'translation_domain' => 'System',
                'constraints' => [
                    new MySQLConnection(),
                ],
                'proceed' => '0',
                'allow_extra_fields' => true,
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
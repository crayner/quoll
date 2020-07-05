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
use App\Modules\School\Entity\Scale;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class MiscellaneousSettingsType
 * @package App\Modules\System\Form
 */
class MiscellaneousSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('miscellaneousSettingsHeader', HeaderType::class,
                [
                    'label' => 'Miscellaneous'
                ]
            )
            ->add('miscellaneousSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'emailLink',
                            'entry_type' => UrlType::class,
                            'entry_options' => [
                                'required' => false,
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'webLink',
                            'entry_type' => UrlType::class,
                            'entry_options' => [
                                'required' => false,
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'pagination',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'attr' => [
                                    'min' => 5,
                                    'max' => 50,
                                ],
                                'constraints' => [
                                    new Range(['min' => 5, 'max' => 50])
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'analytics',
                            'entry_type' => TextareaType::class,
                            'entry_options' => [
                                'attr' => [
                                    'rows' => 8,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'defaultAssessmentScale',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Scale::class,
                                'choice_label' => 'name',
                                'placeholder' => 'Please Select...',
                                'query_builder' => function(EntityRepository $er){
                                    return $er->createQueryBuilder('s')
                                        ->where('s.active = :yes')
                                        ->setParameter('yes', 'Y')
                                        ->orderBy('s.name')
                                    ;
                                },
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
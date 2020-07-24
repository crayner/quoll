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
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Person;
use App\Modules\System\Form\Entity\OrganisationSettings;
use App\Provider\ProviderFactory;
use App\Validator\ReactImage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganisationSettingsType
 * @package App\Modules\System\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class OrganisationSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $orgSettings = new OrganisationSettings();
        $builder
            ->add('organisationSettingsHeader', HeaderType::class,
                [
                    'label' => 'Organisation Settings'
                ]
            )
            ->add('organisationSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'organisationName',
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationAbbreviation',
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 10,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationEmail',
                            'entry_type' => EmailType::class,
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 75,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationLogo',
                            'entry_type' => ReactFileType::class,
                            'entry_options' => [
                                'file_prefix' => 'org_logo',
                                'image_method' => 'getOrganisationLogo',
                                'entity' => $orgSettings,
                                'delete_route' => $options['remove_organisation_logo'],
                                'constraints' => [
                                    new ReactImage(['minWidth' => 400, 'maxWidth' => 800, 'minHeight' => 100, 'maxHeight' => 200, 'maxRatio' => 0.25, 'minRatio' => 0.25, 'maxSize' => '750k']),
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationBackground',
                            'entry_type' => ReactFileType::class,
                            'entry_options' => [
                                'file_prefix' => 'org_bg',
                                'image_method' => 'getOrganisationBackground',
                                'entity' => $orgSettings,
                                'delete_route' => $options['remove_organisation_background'],
                                'constraints' => [
                                    new ReactImage(['maxSize' => '750k', 'minWidth' => '1500', 'minHeight' => '1200']),
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationAdministrator',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'choice_label' => 'fullNameReversed',
                                'placeholder' => ' ',
                                'choice_translation_domain' => false,
                                'query_builder' => ProviderFactory::getRepository(Person::class)->getStaffQueryBuilder(),
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationDBA',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'choice_label' => 'fullNameReversed',
                                'placeholder' => ' ',
                                'choice_translation_domain' => false,
                                'query_builder' => ProviderFactory::getRepository(Person::class)->getStaffQueryBuilder(),
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationAdmissions',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'placeholder' => ' ',
                                'choice_label' => 'fullNameReversed',
                                'choice_translation_domain' => false,
                                'query_builder' => ProviderFactory::getRepository(Person::class)->getStaffQueryBuilder(),
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationHR',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'choice_label' => 'fullNameReversed',
                                'placeholder' => ' ',
                                'choice_translation_domain' => false,
                                'query_builder' => ProviderFactory::getRepository(Person::class)->getStaffQueryBuilder(),
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
        $resolver->setRequired(
            [
                'remove_organisation_logo',
                'remove_organisation_background',
            ]
        );
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
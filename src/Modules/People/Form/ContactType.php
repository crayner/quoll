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
 * Date: 21/07/2020
 * Time: 10:15
 */
namespace App\Modules\People\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ContactType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 21/07/2020 10:20
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
/*            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                ]
            ) */
            ->add('contactHeader', HeaderType::class,
                [
                    'label' => 'Contact Details',
                ]
            )
            ->add('email', EmailType::class,
                [
                    'label' => 'Email',
                    'required' => false,
                    'help' => SecurityHelper::isEmailUnique() ? 'This email must be unique.{google}': null,
                    'help_translation_parameters' => ['{google}' => ParameterBagHelper::get('google_oauth') ? TranslationHelper::translate('<br/>Google oAuth uses this email address to validate a google user.', [], 'People') : ''],
                ]
            )
            ->add('emailAlternate', EmailType::class,
                [
                    'label' => 'Additional Email',
                    'required' => false,
                ]
            )
        ;
        if (!$options['data']->getPerson()->isStudent() && !$options['data']->getPerson()->isCareGiver()) {
            $builder
                ->add('physicalAddress', AutoSuggestEntityType::class,
                    [
                        'label' => 'Physical Address',
                        'placeholder' => 'Enter any part of an address',
                        'class' => Address::class,
                        'choice_label' => 'toString',
                        'required' => false,
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('a')
                                ->select(['a','l'])
                                ->leftJoin('a.locality','l')
                                ->orderBy('a.streetNumber', 'ASC')
                                ->addOrderBy('a.streetName', 'ASC')
                                ->addOrderBy('l.name', 'ASC')
                                ;
                        },
                        'panel' => 'Contact',
                        'buttons' => [
                            'add' => [
                                'class' => 'fa-fw fas fa-plus-circle',
                                'route' => '/address/add/popup/',
                                'target' => 'Address_Details',
                                'specs' => 'width=800,height=600',
                                'title' => TranslationHelper::translate('Add Address', [], 'People'),
                            ],
                            'edit' => [
                                'class' => 'fa-fw fas fa-edit',
                                'route' => '/address/__value__/edit/popup/',
                                'target' => 'Address_Details',
                                'specs' => 'width=800,height=600',
                                'title' => TranslationHelper::translate('Edit Address', [], 'People'),
                            ],
                            'refresh' => [
                                'class' => 'fa-fw fas fa-sync',
                                'route' => '/address/list/refresh/',
                                'title' => TranslationHelper::translate('Refresh Address List', [], 'People'),
                            ],
                        ],
                    ]
                )
                ->add('postalAddress', AutoSuggestEntityType::class,
                    [
                        'label' => 'Postal Address',
                        'help' => 'Should only be used if the physical address is not the postal address.',
                        'placeholder' => 'Enter any part of an address',
                        'required' => false,
                        'class' => Address::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('a')
                                ->select(['a', 'l'])
                                ->leftJoin('a.locality', 'l')
                                ->orderBy('a.streetNumber', 'ASC')
                                ->addOrderBy('a.streetName', 'ASC')
                                ->addOrderBy('l.name', 'ASC');
                        },
                        'choice_label' => 'toString',
                        'panel' => 'Contact',
                        'buttons' => [
                            'add' => [
                                'class' => 'fa-fw fas fa-plus-circle',
                                'route' => '/address/add/popup/',
                                'target' => 'Address_Details',
                                'specs' => 'width=800,height=600',
                                'title' => TranslationHelper::translate('Add Address', [], 'People'),
                            ],
                            'edit' => [
                                'class' => 'fa-fw fas fa-edit',
                                'route' => '/address/__value__/edit/popup/',
                                'target' => 'Address_Details',
                                'specs' => 'width=800,height=600',
                                'title' => TranslationHelper::translate('Edit Address', [], 'People'),
                            ],
                            'refresh' => [
                                'class' => 'fa-fw fas fa-sync',
                                'route' => '/address/list/refresh/',
                                'title' => TranslationHelper::translate('Refresh Address List', [], 'People'),
                            ],
                        ],
                    ]
                );
        }
        $builder
            ->add('personalPhone', AutoSuggestEntityType::class,
                [
                    'label' => 'Personal Phone',
                    'help' => 'Usually a mobile phone.',
                    'class' => Phone::class,
                    'placeholder' => 'Enter any part of a phone number',
                    'required' => false,
                    'panel' => 'Contact',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->orderBy('p.phoneNumber', 'ASC');
                    },
                    'buttons' => [
                        'add' => [
                            'class' => 'fa-fw fas fa-plus-circle',
                            'route' => '/phone/add/popup/',
                            'target' => 'Phone_Details',
                            'specs' => 'width=700,height=350',
                            'title' => TranslationHelper::translate('Add Phone', [], 'People'),
                        ],
                        'edit' => [
                            'class' => 'fa-fw fas fa-edit',
                            'route' => '/phone/__value__/edit/popup/',
                            'target' => 'Phone_Details',
                            'specs' => 'width=700,height=350',
                            'title' => TranslationHelper::translate('Edit Phone', [], 'People'),
                        ],
                        'refresh' => [
                            'class' => 'fa-fw fas fa-sync',
                            'route' => '/phone/list/refresh/',
                            'title' => TranslationHelper::translate('Refresh Phone List', [], 'People'),
                        ],
                    ],
                ]
            )
            ->add('website', UrlType::class,
                [
                    'required' => false,
                    'label' => 'Personal Website',
                ]
            )
        ;
        if (!$options['data']->getPerson()->isStaff() && !$options['data']->getPerson()->isStudent()) {
            $builder
                ->add('profession', TextType::class,
                    [
                        'required' => false,
                        'label' => 'Profession',
                    ]
                )
                ->add('employer', TextType::class,
                    [
                        'label' => 'Employer',
                        'required' => false,
                    ]
                )
                ->add('jobTitle', TextType::class,
                    [
                        'label' => 'Job Title',
                        'required' => false,
                    ]
                )
            ;
        }
        $builder
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 21/07/2020 10:17
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Contact::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 21/07/2020 10:16
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

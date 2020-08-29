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
 * Date: 25/07/2019
 * Time: 09:39
 */
namespace App\Modules\System\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Validator\Password;
use App\Modules\System\Manager\InstallationManager;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Form\Entity\SystemSettings;
use App\Provider\ProviderFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SystemType
 * @package App\Modules\System\Form
 */
class SystemType extends AbstractType
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * getAbsoluteURL
     *
     * 29/08/2020 08:11
     * @return string
     */
    public function getAbsoluteURL(): string
    {
        return $this->getParameterBag()->get('absoluteURL');
    }

    /**
     * getTimezone
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->getParameterBag()->get('timezone');
    }

    /**
     * @return ParameterBagInterface
     */
    public function getParameterBag(): ParameterBagInterface
    {
        return $this->parameterBag;
    }

    /**
     * ParameterBag.
     *
     * @param ParameterBagInterface $parameterBag
     * @return SystemType
     */
    public function setParameterBag(ParameterBagInterface $parameterBag): SystemType
    {
        $this->parameterBag = $parameterBag;
        return $this;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $provider = SettingFactory::getSettingManager();
        $systemName = $provider->get('System', 'systemName');
        $installType = $provider->get('System', 'installType');
        $orgName = $provider->get('System', 'organisationName');
        $orgNameShort = $provider->get('System', 'organisationAbbreviation');
        $country = $provider->get('System', 'country');
        $currency = $provider->get('System', 'currency');

        $builder
            ->add('userAccountHeader', HeaderType::class,
                [
                    'label' => 'System User Account',
                    'panel' => 'System User',
                ]
            )
            ->add('title', EnumType::class,
                [
                    'label' => 'Title',
                    'attr' => [
                       'class' => 'w-full',
                    ],
                    'placeholder' => 'Please select...',
                    'panel' => 'System User',
                    'required' => false,
                    'choice_translation_domain' => 'People',
                    'choice_list_prefix' => 'person.title',
                    'choice_list_class' => Person::class,
                    'choice_list_method' => 'getTitleList',
                    'constraints' => [
                       new Choice(['callback' => Person::class . '::getTitleList']),
                    ],
                ]
            )
            ->add('surname', TextType::class,
                [
                    'label' => 'Surname',
                    'help' => 'Family name as shown in ID documents.',
                    'panel' => 'System User',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 60,
                    ],
                    'constraints' => [
                       new NotBlank(),
                    ],
                ]
            )
            ->add('firstName', TextType::class,
                [
                    'label' => 'First Name',
                    'help' => 'First name as shown in ID documents.',
                    'panel' => 'System User',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 60,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('gender', EnumType::class,
                [
                    'label' => 'Gender',
                    'panel' => 'System User',
                    'translation_domain' => 'People',
                    'choice_list_class' => Person::class,
                    'choice_list_method' => 'getGenderList',
                    'choice_list_prefix' => 'person.gender',
                    'attr' => [
                        'class' => 'w-full',
                    ],
                    'constraints' => [
                        new Choice(['choices' => Person::getGenderList()]),
                    ],
                ]
            )
            ->add('email', EmailType::class,
               [
                   'label' => 'Email',
                   'panel' => 'System User',
                   'attr' => [
                       'class' => 'w-full',
                       'maxLength' => 75,
                   ],
                   'constraints' => [
                       new NotBlank(),
                   ],
               ]
            )
            ->add('username', TextType::class,
               [
                   'label' => 'Username',
                   'panel' => 'System User',
                   'help' => 'Must be unique. System login name. Cannot be changed.',
                   'attr' => [
                       'class' => 'w-full',
                       'maxLength' => 20,
                   ],
                   'constraints' => [
                       new NotBlank(),
                   ],
               ]
            )
            ->add('password', RepeatedType::class,
               [
                   'first_options' => [
                       'label' => 'Password',
                       'attr' => [
                           'class' => 'w-full',
                           'maxLength' => 30,
                        ],
                   ],
                   'second_options' => [
                       'label' => 'Confirm Password',
                       'attr' => [
                           'class' => 'w-full',
                           'maxLength' => 30,
                       ],
                   ],
                   'type' => PasswordType::class,
                   'panel' => 'System User',
                   'constraints' => [
                       new NotBlank(),
                       new Password(),
                   ],
               ]
            )
            ->add('submit1', SubmitType::class,
                [
                    'panel' => 'System User',
                ]
            )
            ->add('systemSettingsHeader', HeaderType::class,
                [
                    'label' => 'System Settings',
                    'panel' => 'Settings',
                ]
            )
            ->add('baseUrl', DisplayType::class,
                [
                    'label' => 'Base URL',
                    'panel' => 'Settings',
                    'help' =>  'The url at which the whole system resides.',
                    'data' => $this->getAbsoluteURL(),
                ]
            )
            ->add('basePath', DisplayType::class,
                [
                    'label' => 'Base Path',
                    'panel' => 'Settings',
                    'help' =>'The local file system path to the system',
                    'data' => realpath($this->getParameterBag()->get('kernel.public_dir')),
                ]
            )
            ->add('systemName', TextType::class,
                [
                    'label' => 'System.systemName.name',
                    'help' => 'System.systemName.description',
                    'panel' => 'Settings',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 50,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('installType', EnumType::class,
                [
                    'label' => 'System.installType.name',
                    'help' => 'System.installType.description',
                    'panel' => 'Settings',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 50,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'choice_list_prefix' => false,
                ]
            )
            ->add('country', CountryType::class,
                [
                    'label' => 'System.country.name',
                    'help' => 'System.country.description',
                    'panel' => 'Settings',
                    'alpha3' => true,
                    'placeholder' => ' ',
                    'attr' => [
                        'class' => 'w-full',
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('currency', CurrencyType::class,
                [
                    'label' => 'System.currency.name',
                    'help' => 'System.currency.description',
                    'placeholder' => ' ',
                    'panel' => 'Settings',
                    'attr' => [
                        'class' => 'w-full',
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('timezone', TimezoneType::class,
                [
                    'label' => 'System.timezone.name',
                    'help' => 'System.timezone.description',
                    'placeholder' => ' ',
                    'panel' => 'Settings',
                    'attr' => [
                        'class' => 'w-full',
                    ],
                    'data' => $this->getTimeZone(),
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('submit2', SubmitType::class,
                [
                    'panel' => 'Settings',
                ]
            )
            ->add('organisationHeader', HeaderType::class,
                [
                    'label' => 'Organisation Settings',
                    'panel' => 'Organisation',
                ]
            )
            ->add('organisationName', TextType::class,
                [
                    'label' => 'System.organisationName.name',
                    'help' => 'System.organisationName.description',
                    'panel' => 'Organisation',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 50,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('organisationAbbreviation', TextType::class,
                [
                    'label' => 'System.organisationAbbreviation.name',
                    'help' => 'System.organisationAbbreviation.description',
                    'panel' => 'Organisation',
                    'attr' => [
                        'class' => 'w-full',
                        'maxLength' => 10,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'panel' => 'Organisation',
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
                'translation_domain' => 'Setting',
                'data_class' => SystemSettings::class,
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

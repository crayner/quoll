<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 22/11/2019
 * Time: 15:00
 */

namespace App\Modules\People\Form;

use App\Form\Extension\ChoiceTranslations;
use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\EntityType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\FamilyRelationship;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
use App\Modules\People\Manager\AddressManager;
use App\Modules\People\Util\UserHelper;
use App\Modules\School\Entity\House;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Util\LocaleHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class PersonType
 * @package App\Modules\People\Form
 */
class PersonType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $hierarchy;

    /**
     * @var AddressManager
     */
    private $manager;

    /**
     * PersonType constructor.
     * @param RouterInterface $router
     * @param RoleHierarchyInterface $hierarchy
     * @param AddressManager $manager
     */
    public function __construct(RouterInterface $router, RoleHierarchyInterface $hierarchy, AddressManager $manager)
    {
        $this->router = $router;
        $this->hierarchy = $hierarchy;
        $this->manager = $manager;
    }


    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('basicHeader', HeaderType::class,
                [
                    'label' => 'Basic Information',
                    'panel' => 'Basic',
                ]
            )
            ->add('title', EnumType::class,
                [
                    'label' => 'Title',
                    'required' => true,
                    'panel' => 'Basic',
                    'placeholder' => ' ',
                ]
            )
            ->add('surname', TextType::class,
                [
                    'label' => 'Surname',
                    'help' => 'Family name as shown in ID documents.',
                    'panel' => 'Basic',
                ]
            )
            ->add('firstName', TextType::class,
                [
                    'label' => 'Given Names',
                    'help' => 'Given names as shown in ID documents.',
                    'panel' => 'Basic',
                ]
            )
            ->add('preferredName', TextType::class,
                [
                    'label' => 'Preferred Name',
                    'help' => 'Most common name, alias, nickname, etc.',
                    'panel' => 'Basic',
                ]
            )
            ->add('officialName', TextType::class,
                [
                    'label' => 'Official Name',
                    'help' => 'Full name as shown in ID documents.',
                    'panel' => 'Basic',
                ]
            )
            ->add('nameInCharacters', TextType::class,
                [
                    'label' => 'Name In Characters',
                    'help' => 'Chinese or other character-based name.',
                    'panel' => 'Basic',
                ]
            )
            ->add('gender', EnumType::class,
                [
                    'label' => 'Gender',
                    'panel' => 'Basic',
                    'placeholder' => 'person.gender.unspecified',
                ]
            )
            ->add('dob', ReactDateType::class,
                [
                    'label' => 'Date of Birth',
                    'panel' => 'Basic',
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'years' => range(intval(date('Y'))- 120, intval(date('Y')))
                ]
            )
        ;
        if ($options['data']->getId() > 0)
            $builder
                ->add('image240', ReactFileType::class,
                    [
                        'label' => 'Personal Photo',
                        'help' => 'person.image.help',
                        'panel' => 'Basic',
                        'file_prefix' => 'personal_',
                        'data' => $options['data']->getImage240(false),
                        'showThumbnail' => true,
                        'entity' => $options['data'],
                        'imageMethod' => 'getImage240',
                    ]
                )
            ;
        $builder
            ->add('submitBasic', SubmitType::class,
                [
                    'label' => 'Submit',
                    'translation_domain' => 'messages',
                    'panel' => 'Basic',
                ]
            )
        ;

        $this->buildSystem($builder, $options);
        if ($options['data']->getId() > 0) {
            $this->buildContact($builder, $options);
            $this->buildSchool($builder, $options);
            $this->buildBackground($builder, $options);
            if (UserHelper::isParent($options['data']))
                $this->buildEmployment($builder, $options);
            if (UserHelper::isStudent($options['data']) || UserHelper::isStaff($options['data']))
                $this->buildEmergency($builder, $options);
            $this->buildMiscellaneous($builder, $options);
        }
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $emailConstraint = [];
        if (ProviderFactory::create(Setting::class)->getSettingByScopeAsBoolean('People','uniqueEmailAddress'))
            $emailConstraint = [
                new UniqueEntity(['fields' => ['email'], 'ignoreNull' => true]),
            ];
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Person::class,
                'constraints' => $emailConstraint,
            ]
        );
        $resolver->setRequired(
            [
                'user_roles',
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
     * buildSystem
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildSystem(FormBuilderInterface $builder, array $options)
    {
        $hierarchy = [];
        foreach($this->hierarchy->getAssignableRoleNames($options['user_roles']) as $role)
        {
            $hierarchy[$role] = $role;
        }


        $builder
            ->add('systemHeader', HeaderType::class,
                [
                    'label' => 'System Access: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'panel' => 'System',
                ]
            )
            ->add('primaryRole', ChoiceType::class,
                [
                    'label' => 'Primary Role',
                    'choices' => $hierarchy,
                    'help' => 'Controls what a user can do and see.',
                    'panel' => 'System',
                    'placeholder' => 'Please select...',
                    'choice_translation_domain' => 'Security',
                ]
            )
            ->add('allRoles', ChoiceType::class,
                [
                    'label' => 'All Roles',
                    'help' => "Controls what a user can do and see. Use Control, Command and/or Shift to select multiple.",
                    'panel' => 'System',
                    'multiple' => true,
                    'required' => false,
                    'choices' => $hierarchy,
                    'choice_translation_domain' => 'Security',
                    'attr' => [
                        'size' => 4,
                    ],
                ]
            )
            ->add('username', TextType::class,
                [
                    'label' => 'Username',
                    'help' => "System login name.",
                    'panel' => 'System',
                    'required' => false,
                ]
            )
            ->add('status', EnumType::class,
                [
                    'label' => 'Status',
                    'help' => "This determines visibility within the system.",
                    'panel' => 'System',
                    'placeholder' => 'Please Select...',
                    'choice_list_prefix' => 'person.status'
                ]
            )
            ->add('canLogin', ToggleType::class,
                [
                    'label' => 'Can Login?',
                    'panel' => 'System',
                ]
            )
            ->add('passwordForceReset', ToggleType::class,
                [
                    'label' => 'Force Password Reset',
                    'help' => 'User will be prompted on next login.',
                    'panel' => 'System',
                ]
            )
            ->add('submitSystem', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'System',
                    'translation_domain' => 'People',
                ]
            )
        ;
    }

    /**
     * buildSystem
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildContact(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contactHeader', HeaderType::class,
                [
                    'label' => 'Contact Information: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'panel' => 'Contact',
                ]
            )
            ->add('email', EmailType::class,
                [
                    'label' => 'Email',
                    'panel' => 'Contact',
                    'required' => false,
                ]
            )
            ->add('emailAlternate', EmailType::class,
                [
                    'label' => 'Alternate Email',
                    'panel' => 'Contact',
                    'required' => false,
                ]
            )
            ->add('addressWarning', ParagraphType::class,
                [
                    'help' => 'person.address.warning',
                    'panel' => 'Contact',
                ]
            )
            ->add('enterPersonalAddress', ToggleType::class,
                [
                    'label' => 'Enter Personal Address',
                    'panel' => 'Contact',
                    'mapped' => false,
                    'visible_by_choice' => 'address_info',
                    'data' => $options['data']->getPhysicalAddress() || $options['data']->getPostalAddress() ? 'Y' : 'N',
                ]
            )
            ->add('physicalAddress', AutoSuggestEntityType::class,
                [
                    'label' => 'Physical Address',
                    'placeholder' => 'Enter any part of an address',
                    'class' => Address::class,
                    'choice_label' => 'toString',
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
                    'visible_values' => ['address_info'],
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
                    'class' => Address::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->select(['a','l'])
                            ->leftJoin('a.locality','l')
                            ->orderBy('a.streetNumber', 'ASC')
                            ->addOrderBy('a.streetName', 'ASC')
                            ->addOrderBy('l.name', 'ASC')
                        ;
                    },
                    'choice_label' => 'toString',
                    'panel' => 'Contact',
                    'visible_values' => ['address_info'],
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
            ->add('personalPhone', AutoSuggestEntityType::class,
                [
                    'label' => 'Personal Phone',
                    'help' => 'Usually a mobile phone.',
                    'class' => Phone::class,
                    'placeholder' => 'Enter any part of a phone number',
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
 /*           ->add('additionalPhones', ToggleType::class,
                [
                    'label' => 'Additional Personal Phone Details',
                    'panel' => 'Contact',
                    'mapped' => false,
                    'visible_by_choice' => 'phone_info',
                    'data' => $options['data']->getAddition() || $options['data']->getPostalAddress() ? 'Y' : 'N',
                ]
            ) */
            ->add('website', UrlType::class,
                [
                    'label' => 'Website',
                    'panel' => 'Contact',
                ]
            )
            ->add('submitContact', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Contact',
                    'translation_domain' => 'People',
                ]
            )
        ;
    }

    /**
     * buildSchool
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildSchool(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('schoolHeader', HeaderType::class,
                [
                    'label' => 'School Information: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'panel' => 'School',
                ]
            )
        ;
        if (UserHelper::isStudent($options['data']) || UserHelper::isStaff($options['data'])) {
            $builder
                ->add('lastSchool', TextType::class,
                    [
                        'label' => 'Last School',
                        'required' => false,
                        'panel' => 'School',
                    ]
                )
            ;
        }
        $builder
            ->add('dateStart', DateType::class,
                [
                    'label' => 'Start Date',
                    'help' => 'The first day at school for this person.',
                    'panel' => 'School',
                    'required' => false,
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'years' => range(intval(date('Y'))- 25, intval(date('Y')))
                ]
            )
            ->add('dateEnd', DateType::class,
                [
                    'label' => 'End Date',
                    'help' => 'The last day at school for this person.',
                    'panel' => 'School',
                    'required' => false,
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'years' => range(intval(date('Y'))- 25, intval(date('Y')))
                ]
            )
        ;
        if (UserHelper::isStudent($options['data'])) {
            $builder
                ->add('AcademicYearClassOf', EntityType::class,
                    [
                        'label' => 'Class of',
                        'class' => AcademicYear::class,
                        'choice_label' => 'name',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('sy')
                                ->orderBy('sy.firstDay', 'ASC');
                        },
                        'required' => false,
                        'help' => 'When is the student expected to graduate?',
                        'panel' => 'School',
                        'placeholder' => ' ',
                    ]
                )
            ;
        }
        if (UserHelper::isStudent($options['data']) || UserHelper::isStaff($options['data'])) {
            $builder
                ->add('nextSchool', TextType::class,
                    [
                        'label' => 'Next School',
                        'required' => false,
                        'panel' => 'School',
                    ]
                )
                ->add('departureReason', TextType::class,
                    [
                        'label' => 'Departure Reason',
                        'required' => false,
                        'panel' => 'School',
                    ]
                )
            ;
        }
        $builder
            ->add('submitSchool', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'School',
                    'translation_domain' => 'People',
                ]
            )
        ;
    }

    /**
     * buildSchool
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildBackground(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('backgroundHeader', HeaderType::class,
                [
                    'label' => 'Background Information: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'panel' => 'Background',
                ]
            )
            ->add('languageFirst', LanguageType::class,
                [
                    'label' => 'First Language',
                    'panel' => 'Background',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('languageSecond', LanguageType::class,
                [
                    'label' => 'Second Language',
                    'panel' => 'Background',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('languageThird', LanguageType::class,
                [
                    'label' => 'Third Language',
                    'panel' => 'Background',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('countryOfBirth', CountryType::class,
                [
                    'label' => 'Country of Birth',
                    'panel' => 'Background',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('birthCertificateScan', ReactFileType::class,
                [
                    'label' => 'Birth Certificate Scan',
                    'panel' => 'Background',
                    'help' => 'Less than 2M,  Accepts PDF and image files only.',
                    'required' => false,
                    'data' => $options['data']->getBirthCertificateScan(),
                    'file_prefix' => 'birth_cert_',
                ]
            )
            ->add('ethnicity', EnumType::class,
                [
                    'label' => 'Ethnicity',
                    'panel' => 'Background',
                    'choice_list_prefix' => false,
                    'required' => false,
                    'help' => 'Ethnicity selection can be altered at {anchor}People Settings{endAnchor}',
                    'help_translation_parameters' => ['{anchor}' => '<a href="'.$this->router->generate('people_settings').'">', '{endAnchor}' => '</a>'],
                    'choice_translation_domain' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('religion', EnumType::class,
                [
                    'label' => 'Religion',
                    'panel' => 'Background',
                    'choice_list_prefix' => false,
                    'required' => false,
                    'help' => 'Religion selection can be altered at {anchor}People Settings{endAnchor}',
                    'help_translation_parameters' => ['{anchor}' => '<a href="'.$this->router->generate('people_settings').'">', '{endAnchor}' => '</a>'],
                    'choice_translation_domain' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('citizenship1', CountryType::class,
                [
                    'label' => 'Citizenship 1',
                    'panel' => 'Background',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('citizenship1Passport', TextType::class,
                [
                    'label' => 'Citizenship 1 Passport Number',
                    'panel' => 'Background',
                    'required' => false,
                ]
            )
            ->add('citizenship1PassportScan', ReactFileType::class,
                [
                    'label' => 'Citizenship 1 Passport Scan',
                    'panel' => 'Background',
                    'help' => 'Less than 2M,  Accepts PDF and image files only.',
                    'required' => false,
                    'data' => $options['data']->getCitizenship1PassportScan(),
                    'file_prefix' => 'passport_',
                ]
            )
            ->add('citizenship2', CountryType::class,
                [
                    'label' => 'Citizenship 2',
                    'panel' => 'Background',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('citizenship2Passport', TextType::class,
                [
                    'label' => 'Citizenship 2 Passport Number',
                    'panel' => 'Background',
                    'required' => false,
                ]
            )
            ->add('nationalIDCardNumber', TextType::class,
                [
                    'label' => '{name} ID Card Number',
                    'label_translation_parameters' => ['{name}' => LocaleHelper::getCountryName($this->manager->getCountry(null))],
                    'panel' => 'Background',
                    'required' => false,
                ]
            )
            ->add('nationalIDCardScan', ReactFileType::class,
                [
                    'label' => '{name} ID Card Scan',
                    'label_translation_parameters' => ['{name}' => LocaleHelper::getCountryName($this->manager->getCountry(null))],
                    'panel' => 'Background',
                    'help' => 'Less than 2M,  Accepts PDF and image files only.',
                    'required' => false,
                    'file_prefix' => 'national_card_',
                    'data' => $options['data']->getNationalIDCardScan(),
                ]
            )
            ->add('residencyStatus', TextType::class,
                [
                    'label' => '{name} Residency/Visa Type',
                    'label_translation_parameters' => ['{name}' => LocaleHelper::getCountryName($this->manager->getCountry(null))],
                    'panel' => 'Background',
                    'required' => false,
                ]
            )
            ->add('visaExpiryDate', DateType::class,
                [
                    'label' => '{name} Visa Expiry Date',
                    'label_translation_parameters' => ['{name}' => LocaleHelper::getCountryName($this->manager->getCountry(null))],
                    'help' => 'If relevant',
                    'panel' => 'Background',
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'required' => false,
                ]
            )
            ->add('submitBackground', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Background',
                    'translation_domain' => 'People',
                ]
            )
        ;
    }

    /**
     * buildEmployment
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildEmployment(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('employmentHeader', HeaderType::class,
                [
                    'label' => 'Employment Details: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'panel' => 'Employment',
                ]
            )
            ->add('profession', TextType::class,
                [
                    'label' => 'Profession',
                    'panel' => 'Employment',
                ]
            )
            ->add('employer', TextType::class,
                [
                    'label' => 'Employer',
                    'panel' => 'Employment',
                ]
            )
            ->add('jobTitle', TextType::class,
                [
                    'label' => 'Job Title',
                    'panel' => 'Employment',
                ]
            )
            ->add('employmentBackground', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Employment',
                    'translation_domain' => 'People',
                ]
            )
        ;
    }

    /**
     * buildEmergency
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildEmergency(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emergencyHeader', HeaderType::class,
                [
                    'label' => 'Emergency Contacts: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'help' => 'These details are used when immediate family members (e.g. parent, spouse) cannot be reached first. Please try to avoid listing immediate family members.',
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergency1Name', TextType::class,
                [
                    'label' => 'Contact 1 Name',
                    'required' => false,
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergency1Relationship', EnumType::class,
                [
                    'label' => 'Contact 1 Relationship',
                    'choice_list_method' => 'getRelationshipList',
                    'choice_list_prefix' => 'family.relationship',
                    'choice_list_class' => FamilyRelationship::class,
                    'required' => false,
                    'panel' => 'Emergency',
                    'placeholder' => ' ',
                ]
            )
            ->add('emergency1Number1', TextType::class,
                [
                    'label' => 'Contact 1 Number 1',
                    'required' => false,
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergency1Number2', TextType::class,
                [
                    'label' => 'Contact 1 Number 2',
                    'required' => false,
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergency2Name', TextType::class,
                [
                    'label' => 'Contact 2 Name',
                    'required' => false,
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergency2Relationship', EnumType::class,
                [
                    'label' => 'Contact 2 Relationship',
                    'choice_list_method' => 'getRelationshipList',
                    'choice_list_class' => FamilyRelationship::class,
                    'required' => false,
                    'panel' => 'Emergency',
                    'choice_list_prefix' => 'family.relationship',
                    'placeholder' => ' ',
                ]
            )
            ->add('emergency2Number1', TextType::class,
                [
                    'label' => 'Contact 2 Number 1',
                    'required' => false,
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergency2Number2', TextType::class,
                [
                    'label' => 'Contact 2 Number 2',
                    'required' => false,
                    'panel' => 'Emergency',
                ]
            )
            ->add('emergencyBackground', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Emergency',
                ]
            )
        ;
    }

    /**
     * buildMiscellaneous
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildMiscellaneous(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('miscellaneousHeader', HeaderType::class,
                [
                    'label' => 'Miscellaneous: {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getId() > 0 ? $options['data']->formatName(['reverse' => true]) : ''],
                    'panel' => 'Miscellaneous',
                ]
            )
            ->add('house', EntityType::class,
                [
                    'label' => 'House',
                    'class' => House::class,
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('h')
                            ->orderBy('h.name')
                            ;
                    },
                    'required' => false,
                    'data' => $options['data']->getHouse() ? $options['data']->getHouse()->getId() : null,
                    'panel' => 'Miscellaneous',
                    'placeholder' => ' ',
                ]
            )
        ;
        if (UserHelper::isStudent($options['data'])) {
            $builder
                ->add('studentID', TextType::class,
                    [
                        'label' => 'Student Identifier',
                        'help' => 'Must be unique if set.',
                        'required' => false,
                        'panel' => 'Miscellaneous',
                    ]
                );
        }
        if (UserHelper::isStudent($options['data']) || UserHelper::isStaff($options['data'])) {
            $builder
                ->add('transport', TextType::class,
                    [
                        'label' => 'Transport',
                        'required' => false,
                        'panel' => 'Miscellaneous',
                    ]
                )
                ->add('transportNotes', TextareaType::class,
                    [
                        'label' => 'Transport Notes',
                        'required' => false,
                        'panel' => 'Miscellaneous',
                        'attr' => [
                            'rows' => 4,
                        ],
                    ]
                )
                ->add('lockerNumber', TextType::class,
                    [
                        'label' => 'Locker Number',
                        'required' => false,
                        'panel' => 'Miscellaneous',
                    ]
                )
            ;
        }
        $builder
            ->add('vehicleRegistration', TextType::class,
                [
                    'label' => 'Vehicle Registration',
                    'required' => false,
                    'panel' => 'Miscellaneous',
                ]
            )
            ->add('miscellaneousBackground', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Miscellaneous',
                ]
            )
        ;
    }
}
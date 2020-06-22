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
 * Date: 4/12/2019
 * Time: 22:00
 */

namespace App\Modules\People\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Family;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FamilyGeneralType
 * @package App\Modules\People\Form
 */
class FamilyGeneralType extends AbstractType
{
    /**
     * @var array
     */
    private $preferredLanguages;

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Family::class,
            ]
        );
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('general', HeaderType::class,
                [
                    'label' => 'General Information',
                    'panel' => 'General',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Family Name',
                    'panel' => 'General',
                ]
            )
            ->add('status', EnumType::class,
                [
                    'label' => 'Relationship Status',
                    'placeholder' => 'Please select...',
                    'panel' => 'General',
                ]
            )
            ->add('languageHomePrimary', LanguageType::class,
                [
                    'label' => 'Home Language - Primary',
                    'placeholder' => ' ',
                    'panel' => 'General',
                    'preferred_choices' => $this->getPreferredLanguages(),
                ]
            )
            ->add('languageHomeSecondary', LanguageType::class,
                [
                    'label' => 'Home Language - Secondary',
                    'placeholder' => ' ',
                    'panel' => 'General',
                    'required' => false,
                    'preferred_choices' => $this->getPreferredLanguages(),
                ]
            )
            ->add('formalName', TextType::class,
                [
                    'label' => 'Formal Family Name',
                    'panel' => 'General',
                    'help' => 'Used to address correspondence sent to the parents/guardians of this family.'
                ]
            )
            ->add('physicalAddress', AutoSuggestEntityType::class,
                [
                    'label' => 'Residential Address',
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
                    'panel' => 'General',
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
                    'placeholder' => 'Enter any part of an address',
                    'help' => 'Should only be used if the physical address is not the postal address.',
                    'class' => Address::class,
                    'required' => false,
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
                    'panel' => 'General',
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
            ->add('panelName', HiddenType::class,
                [
                    'data' => 'General',
                    'panel' => 'General',
                    'mapped' => false,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'panel' => 'General',
                    'label' => 'Submit',
                ]
            )
        ;
    }

    /**
     * @return array
     */
    public function getPreferredLanguages(): array
    {
        return $this->preferredLanguages;
    }

    /**
     * PreferredLanguages.
     *
     * @param array $preferredLanguages
     * @return FamilyGeneralType
     */
    public function setPreferredLanguages(array $preferredLanguages): FamilyGeneralType
    {
        $this->preferredLanguages = $preferredLanguages;
        return $this;
    }
}
<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/05/2020
 * Time: 14:20
 */
namespace App\Modules\People\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType
 * @package App\Modules\People\Form
 */
class AddressType extends AbstractType
{
    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addressHeader', HeaderType::class,
                [
                    'label' => $options['data']->getId() > 0 ? 'Edit Address' : 'Add Address',
                    'help' => 'Editing an existing address will change that address for every person or family that uses that address.'
                ]
            )
            ->add('flatUnitDetails', TextType::class,
                [
                    'label' => 'Flat / Unit Details',
                    'help' => 'Identifies an address within a building/sub-complex.',
                    'required' => false,
                ]
            )
            ->add('streetNumber', TextType::class,
                [
                    'label' => 'Street Number',
                    'help' => 'Identifies the number of the address in the street. ',
                    'required' => false,
                ]
            )
            ->add('streetName', TextType::class,
                [
                    'label' => 'Street Name and Type',
                    'help' => 'Identifies the name and type of the street to the address site.',
                    'parse_value' => 'changeAddress',
                ]
            )
            ->add('propertyName', TextType::class,
                [
                    'label' => 'Property / Building Details',
                    'help' => 'Details the official place name or common usage name for an address site, including the name of a building, Indigenous community, homestead, building complex, agricultural property, park or unbounded address site.',
                    'required' => false,
                ]
            )
            ->add('locality', AutoSuggestEntityType::class,
                [
                    'label' => 'Locality',
                    'help' => 'Suburb, Locality, District or Town.',
                    'class' => Locality::class,
                    'placeholder' => 'Enter any part of a Locality...',
                    'choice_label' => 'toString',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->orderBy('a.name', 'ASC');
                    },
                    'buttons' => [
                        'add' => [
                            'class' => 'fa-fw fas fa-plus-circle',
                            'route' => '/locality/add/popup/',
                            'target' => 'Locality_Details',
                            'specs' => 'width=800,height=450',
                            'title' => TranslationHelper::translate('Add Locality', [], 'People'),
                        ],
                        'refresh' => [
                            'class' => 'fa-fw fas fa-sync',
                            'route' => '/locality/list/refresh/',
                            'title' => TranslationHelper::translate('Refresh Locality List', [], 'People'),
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
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Address::class,
            ]
        );
    }
}
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

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Locality;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType
 * @package App\Modules\People\Form
 */
class LocalityType extends AbstractType
{
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    /**
     * LocalityType constructor.
     * @param ParameterBagInterface $bag
     */
    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
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
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->bag->has('country_list')) {
            $cList = $this->bag->get('country_list');
        } else {
            $cList = [];
        }
        $cList = [
            'AUS',
            'NZL'
        ];
        $list = [];
        foreach($cList as $c)
            $list[$c] = Countries::getAlpha3Name($c);
        $list = array_flip($list);
        $builder
            ->add('localityHeader', HeaderType::class,
                [
                    'label' => 'Locality',
                    'help' => 'Editing an existing address will change that address for every person or family that uses that address.'
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Locality Name',
                    'help' => '',
                    'on_change' => 'changeLocality',
                ]
            )
            ->add('territory', TextType::class,
                [
                    'label' => 'State / Provence ',
                    'on_change' => 'changeLocality',
                ]
            )
            ->add('postCode', TextType::class,
                [
                    'label' => 'Post Code',
                    'on_change' => 'changeLocality',
                ]
            )
            ->add('country', CountryType::class,
                [
                    'label' => 'Country',
                    'alpha3' => true,
                    'placeholder' => ' ',
                    'on_change' => 'changeLocality',
                    'preferred_choices' => $list,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'on_click' => 'submitLocality',
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
                'translation_domain' => 'People',
                'data_class' => Locality::class,
            ]
        );
    }
}
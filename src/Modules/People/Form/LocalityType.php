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
use App\Modules\People\Manager\AddressManager;
use App\Modules\System\Util\LocaleHelper;
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
     * @var AddressManager
     */
    private $manager;

    /**
     * AddressType constructor.
     * @param AddressManager $manager
     */
    public function __construct(AddressManager $manager)
    {
        $this->manager = $manager;
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
        $builder
            ->add('localityHeader', HeaderType::class,
                [
                    'label' => $options['data']->getId() > 0 ? 'Edit Locality' : 'Add Locality',
                    'help' => 'Editing an existing address will change that address for every person or family that uses that address.'
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Locality Name',
                    'help' => 'Suburb, Locality or Town',
                ]
            )
            ->add('territory', TextType::class,
                [
                    'label' => 'State / Provence ',
                ]
            );
        if ($this->manager->isPostCodeHere('locality', $options['data']->getCountry())) {
            $builder
                ->add('postCode', TextType::class,
                    [
                        'label' => 'Post Code',
                        'help' => 'This post code applies to the entire locality. This can be changed in country settings.',
                    ]
                );
        }
        $builder
            ->add('country', CountryType::class,
                [
                    'label' => 'Country',
                    'alpha3' => true,
                    'placeholder' => ' ',
                    'preferred_choices' => $this->manager->getPreferredCountries(),
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
                'data_class' => Locality::class,
            ]
        );
    }
}
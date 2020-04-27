<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 24/11/2019
 * Time: 17:08
 */

namespace App\Modules\People\Form;

use App\Form\Type\EnumType;
use App\Modules\People\Entity\Person;
use App\Modules\System\Entity\Country;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhoneType
 * @package App\Modules\People\Form
 */
class PhoneType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $method = 'getPhone'.$options['phone_position'].'CountryCode';
        $codeValue = $options['data']->$method() ? $options['data']->$method()->getId() : null;
        $builder
            ->add('phone'.$options['phone_position'].'Type', EnumType::class,
                [
                    'label' => false,
                    'choice_list_method' => 'getPhoneTypeList',
                    'choice_list_prefix' => 'person.phoneTypeList',
                ]
            )
            ->add('phone'.$options['phone_position'].'CountryCode', EntityType::class,
                [
                    'label' => false,
                    'class' => Country::class,
                    'choice_label' => 'nameWithCode',
                    'placeholder' => ' ',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->orderBy('c.printable_name', 'ASC')
                        ;
                    },
                ]
            )
            ->add('phone'.$options['phone_position'], TextType::class,
                [
                    'label' => false,
                    'wrapper_class' => 'flex-1 relative width40',
                ]
            )
        ;
    }

    public function getBlockPrefix()
    {
        return 'kook_phone';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Person::class,
                'row_style' => 'multiple_widget',
                'phone_position' => 1,
            ]
        );
    }
}
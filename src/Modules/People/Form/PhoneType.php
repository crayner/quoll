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
 * Date: 24/11/2019
 * Time: 17:08
 */
namespace App\Modules\People\Form;

use App\Form\Type\EnumType;
use App\Manager\PhoneCodes;
use App\Modules\People\Entity\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $builder
            ->add('type', EnumType::class,
                [
                    'label' => 'Phone Type',
                    'choice_list_method' => 'getPhoneTypeList',
                    'choice_list_prefix' => 'phone.type.',
                ]
            )
            ->add('phoneNumber', TextType::class,
                [
                    'label' => 'National Phone Number',
                ]
            )
            ->add('country', ChoiceType::class,
                [
                    'label' => 'International Direct Dial Code',
                    'choices' => PhoneCodes::getIddCodeChoices(true),
                ]
            )
        ;
    }

    /**
     * getBlockPrefix
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return 'kook_phone';
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
                'data_class' => Phone::class,
            ]
        );
    }
}
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
 * Date: 18/01/2020
 * Time: 08:43
 */
namespace App\Modules\IndividualNeed\Form;

use App\Form\Type\ReactFormType;
use App\Modules\IndividualNeed\Entity\INDescriptor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class INDescriptorType
 * @package App\Modules\IndividualNeed\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INDescriptorType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 9/06/2020 15:44
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'translation_domain' => 'messages',
                    'help' => 'Must be unique',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('sortOrder', HiddenType::class)
            ->add('description', TextareaType::class,
                [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => [
                        'rows' => 7,
                    ],
                ]
            )
            ->add('submit', SubmitType::class)
        ;
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
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'IndividualNeed',
                'data_class' => INDescriptor::class,
            ]
        );
    }
}
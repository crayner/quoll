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
 * Date: 31/12/2019
 * Time: 18:31
 */
namespace App\Modules\School\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Modules\School\Entity\House;
use App\Validator\ReactImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HouseType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HouseType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('headName', HeaderType::class,
                [
                    'label' => $options['data']->getId() === null ? 'Add House' : 'Edit House',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('logo', ReactFileType::class,
                [
                    'label' => 'Logo',
                    'help' => 'Image file size maximum 2MB',
                    'file_prefix' => 'house_logo_',
                    'data' => $options['data']->getLogo(),
                    'show_thumbnail' => true,
                    'entity' => $options['data'],
                    'image_method' => 'getLogo',
                    'constraints' => [
                         new ReactImage([
                             'maxSize' => "2M",
                             'minRatio' => 0.7,
                             'maxRatio' => 1,
                             'mimeTypes' => "image/*"
                         ]),
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
                'translation_domain' => 'School',
                'data_class' => House::class,
            ]
        );
    }
}
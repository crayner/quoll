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
 * Date: 5/11/2019
 * Time: 13:45
 */
namespace App\Modules\Library\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Department\Entity\Department;
use App\Modules\Library\Entity\Library;
use App\Modules\School\Entity\Facility;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LibraryType
 * @package App\Modules\Library\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LibraryType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 8/06/2020 08:39
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selectLibrary', HeaderType::class,
                [
                    'label' => 'Library {name}',
                    'label_translation_parameters' => ['{name}' => $options['data']->getName()],
                ]
            )
            ->add('workingOn', EntityType::class,
                [
                    'mapped' => false,
                    'label' => 'Library',
                    'class' => Library::class,
                    'data' => $options['data']->getId() === null ? null : $options['data'],
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'submit_on_change' => true,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->orderBy('l.name')
                        ;
                    },
                    'help' => 'This selection sets the the current Library with which you are working, and also allows you to change the settings for this library.',
                ]
            )
            ->add('librarySettings', HeaderType::class,
                [
                    'label' => 'Library Settings',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Library Name',
                    'help' => 'Must be unique',
                ]
            )
            ->add('abbr', TextType::class,
                [
                    'label' => 'Library Abbreviation',
                    'help' => 'Must be unique',
                ]
            )
            ->add('borrowLimit', IntegerType::class,
                [
                    'label' => 'Borrowing Limit',
                    'help' => 'The maximum number of items a borrower can have on loan.',
                    'attr' => [
                        'max' => 99,
                    ]
                ]
            )
            ->add('department', EntityType::class,
                [
                    'label' => 'Department',
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'data' => $options['data']->getDepartment(),
                    'class' => Department::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->orderBy('d.name');
                    },
                ]
            )
            ->add('facility', EntityType::class,
                [
                    'label' => 'Facility',
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'help' => 'The storage location when the item is not in use.',
                    'data' => $options['data']->getFacility(),
                    'class' => Facility::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('f')
                            ->orderBy('f.name');
                    },
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('main', ToggleType::class,
                [
                    'label' => 'Main Library',
                ]
            )
            ->add('lendingPeriod', IntegerType::class,
                [
                    'label' => 'Default Lending Period',
                    'help' => 'in days',
                ]
            )
            ->add('bgColour', ColorType::class,
                [
                    'label' => TranslationHelper::translate('Background Colour'),
                    'help' => '<a class="text-blue-800 underline hover:text-blue-500" href="https://www.w3schools.com/colors/default.asp" target="_blank">https://www.w3schools.com/colors/default.asp</a>',
                    'translation_domain' => false,
                ]
            )
            ->add('bgImage', ReactFileType::class,
                [
                    'label' => 'Background Image',
                    'file_prefix' => 'lib_bg_',
                    'show_thumbnail' => true,
                    'image_method' => 'getBGImage',
                    'entity' => $options['data'],
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
                'translation_domain' => 'Library',
                'data_class' => Library::class,
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
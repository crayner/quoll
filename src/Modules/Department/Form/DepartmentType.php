<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/01/2020
 * Time: 07:48
 */
namespace App\Modules\Department\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Modules\Department\Entity\Department;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DepartmentType
 * @package App\Modules\Department\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DepartmentType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 4/06/2020 15:02
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('generalTitle', HeaderType::class,
                [
                    'label' => 'General',
                    'panel' => 'General',
                ]
            )
        ;
        if ($options['data']->getId() === null) {
            $builder
                ->add('type', EnumType::class,
                    [
                        'label' => 'Type',
                        'panel' => 'General',
                        'visible_by_choice' => true,
                    ]
                )
            ;
        } else {
            $builder
                ->add('type', DisplayType::class,
                    [
                        'label' => 'Type',
                        'panel' => 'General',
                    ]
                )
            ;
        }
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'panel' => 'General',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'panel' => 'General',
                    'translation_domain' => 'messages',
                ]
            );
        if ($options['data']->getId() === null || ($options['data']->getId() !== null && $options['data']->getType() === 'Learning Area')) {
            $builder
                ->add('subjectListing', SimpleArrayType::class,
                    [
                        'label' => 'Subject Listing',
                        'panel' => 'General',
                        'visible_values' => ['Learning Area'],
                    ]
                )
            ;
        } else {
            $builder
                ->add('subjectListing', HiddenType::class,
                    [
                        'panel' => 'General',
                    ]
                )
            ;
        }
        $builder
            ->add('blurbName', HeaderType::class,
                [
                    'label' => 'Blurb',
                    'header_type' => 'h6',
                    'panel' => 'General',
                ]
            )
            ->add('blurb', CKEditorType::class,
                [
                    'row_style' => 'single',
                    'panel' => 'General',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'General',
                ]
            )
            ->add('staffTitle', HeaderType::class,
                [
                    'label' => 'New Staff',
                    'panel' => 'Staff',
                ]
            )
            ->add('formName', HiddenType::class,
                [
                    'data' => 'General Form',
                    'mapped' => false,
                ]
            )
                ->add('submit2', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Staff',
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
                'translation_domain' => 'Department',
                'data_class' => Department::class,
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
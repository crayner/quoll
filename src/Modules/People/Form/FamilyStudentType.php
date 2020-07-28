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
 * Date: 6/12/2019
 * Time: 10:59
 */
namespace App\Modules\People\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FamilyChildType
 * @package App\Modules\People\Form
 */
class FamilyStudentType extends AbstractType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => FamilyMemberStudent::class,
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

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']->getId() !== null) {
            $builder
                ->add('studentEditHeader', HeaderType::class,
                    [
                        'label' => 'Edit Student',
                    ]
                )
                ->add('personName', DisplayType::class,
                    [
                        'label' => "Student's Name",
                        'help' => 'This value cannot be changed',
                        'data' => $options['data']->getStudent()->getFullName('Standard'),
                        'mapped' => false,
                    ]
                )
                ->add('student', HiddenEntityType::class,
                    [
                        'class' => Student::class,
                    ]
                )
            ;
        } else {
            $studentRoles = SecurityHelper::getHierarchy()->getReachableRoleNames(['ROLE_STUDENT']);
            $builder
                ->add('studentEditHeader', HeaderType::class,
                    [
                        'label' => 'Edit Student',
                        'help' => 'Family name: {name}',
                        'help_translation_parameters' => [
                            '{name}' => $options['data']->getFamily()->getName(),
                        ],
                    ]
                )
                ->add('showHideForm', ToggleType::class,
                    [
                        'label' => 'Add student to family',
                        'visible_by_choice' => 'showStudentAdd',
                        'data' => 'N',
                        'mapped' => false,
                    ]
                )
                ->add('student', AutoSuggestEntityType::class,
                    [
                        'label' => "Student's Name",
                        'class' => Student::class,
                        'choice_label' => 'fullNameReversed',
                        'placeholder' => 'Please select...',
                        'query_builder' => ProviderFactory::getRepository(Student::class)->getAllStudentsQuery(),
                        'visible_values' => ['showStudentAdd'],
                        'visible_parent' => 'family_student_showHideForm',
                    ]
                )
            ;
        }

        $builder
            ->add('comment', TextareaType::class,
                [
                    'label' => 'Comment'   ,
                    'required' => false,
                    'visible_values' => ['showStudentAdd'],
                    'visible_parent' => 'family_student_showHideForm',
                    'attr' => [
                        'rows' => 5,
                        'class' => 'w-full',
                    ],
                ]
            )
            ->add('panelName', HiddenType::class,
                [
                    'data' => 'Students',
                    'mapped' => false,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'visible_values' => ['showStudentAdd'],
                    'visible_parent' => 'family_student_showHideForm',
                    'label' => 'Submit',
                ]
            )
        ;
    }
}
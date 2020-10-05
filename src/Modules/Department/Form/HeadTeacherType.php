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
 * Date: 4/10/2020
 * Time: 09:34
 */
namespace App\Modules\Department\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\Department\Entity\Department;
use App\Modules\Department\Entity\HeadTeacher;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Staff\Entity\Staff;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HeadTeacherType
 * @package App\Modules\Department\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HeadTeacherType extends AbstractType
{
    /**
     * buildForm
     *
     * 4/10/2020 09:37
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']->getId()) {
            $builder
                ->add('teacher', HiddenEntityType::class,
                    [
                        'class' => Staff::class,
                    ]
                )
                ->add('teacherName', DisplayType::class,
                    [
                        'label' => 'Head Teacher',
                        'data' => $options['data']->getTeacher()->getFullName(),
                        'mapped' => false,
                    ]
                )
            ;
        } else {
            $builder
                ->add('teacher', AutoSuggestEntityType::class,
                    [
                        'label' => 'Head Teacher',
                        'class' => Staff::class,
                        'placeholder' => 'Please select...',
                        'choice_label' => 'getFullNameReversed',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('s')
                                ->select(['s','p'])
                                ->where('s.type = :teaching')
                                ->setParameter('teaching', 'Teaching')
                                ->leftJoin('s.person', 'p')
                                ->orderBy('p.surname','ASC')
                                ->addOrderBy('p.firstName')
                            ;
                        },
                    ]
                )
            ;
        }
        $builder
            ->add('title', TextType::class,
                [
                    'label' => 'Title',
                    'attr' => [
                        'max' => 64,
                    ],
                ]
            )
            ->add('classes', EntityType::class,
                [
                    'label' => 'Access to Course Classes',
                    'help' => 'Use Control, Command and/or Shift to select multiple.',
                    'class' => CourseClass::class,
                    'choice_label' => 'getFullName',
                    'multiple' => true,
                    'attr' => [
                        'size' => 8,
                    ],
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('cc')
                            ->select(['cc','c'])
                            ->leftJoin('cc.course', 'c')
                            ->where('c.academicYear = :current')
                            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                            ->orderBy('c.abbreviation')
                            ->addOrderBy('cc.name')
                       ;
                    },
                ]
            )
            ->add('department', EntityType::class,
                [
                    'label' => 'Department',
                    'help' => 'Add all classes within this department to this person',
                    'placeholder' => 'Please select...',
                    'submit_on_change' => true,
                    'class' => Department::class,
                    'choice_label' => 'name',
                    'mapped' => false,
                    'disabled' => $options['data']->getId() === null,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->orderBy('d.name')
                            ->where('d.type = :la')
                            ->setParameter('la', 'Learning Area')
                            ;
                    },
                ]
            )
            ->add('yearGroup', EntityType::class,
                [
                    'label' => 'Year Group',
                    'help' => 'Add all classes within this year group to this person',
                    'placeholder' => 'Please select...',
                    'submit_on_change' => true,
                    'class' => YearGroup::class,
                    'choice_label' => 'name',
                    'mapped' => false,
                    'disabled' => $options['data']->getId() === null,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('yg')
                            ->orderBy('yg.sortOrder')
                        ;
                    },
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 4/10/2020 09:37
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(
            [
                'translation_domain' => 'Department',
                'data_class' => HeadTeacher::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 4/10/2020 09:35
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

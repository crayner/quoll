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
 * Date: 4/09/2020
 * Time: 09:35
 */
namespace App\Modules\Enrolment\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\People\Entity\Person;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CourseClassPersonType
 * @package App\Modules\Enrolment\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassStudentType extends AbstractType
{
    /**
     * buildForm
     *
     * 4/09/2020 09:39
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('academicYear', DisplayType::class,
                [
                    'label' => 'Academic Year',
                    'help' => 'This value cannot be changed.',
                    'translation_domain' => 'School',
                    'data' => $options['data']->getCourseClass()->getCourse()->getAcademicYear()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('course', DisplayType::class,
                [
                    'label' => 'Course',
                    'help' => 'This value cannot be changed.',
                    'translation_domain' => 'Curriculum',
                    'data' => $options['data']->getCourseClass()->getCourse()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('courseClassName', DisplayType::class,
                [
                    'label' => 'Course Class',
                    'help' => 'This value cannot be changed.',
                    'data' => $options['data']->getCourseClass()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('courseClass', HiddenEntityType::class,
                [
                    'class' => CourseClass::class,
                ]
            )
        ;
        if ($options['data']->getid() === null) {
            $builder
                ->add('student', AutoSuggestEntityType::class,
                    [
                        'class' => Person::class,
                        'label' => 'Participant',
                        'choice_label' => 'getFullNameReversedWithRollGroup',
                        'placeholder' => 'Search for...',
                        'choices' => ProviderFactory::create(Person::class)->getEnrolmentListByClass($options['data']->getCourseClass()),
                    ]
                )
            ;
        } else {
            $builder
                ->add('student', HiddenEntityType::class,
                    [
                        'class' => Student::class,
                    ]
                )
                ->add('personName', DisplayType::class,
                    [
                        'label' => 'Participant',
                        'help' => 'This value cannot be changed.',
                        'data' => $options['data']->getStudent()->getFullNameReversed(),
                        'mapped' => false,
                    ]
                )
                ->add('role', DisplayType::class,
                    [
                        'label' => 'Role',
                        'data' => 'Student',
                        'mapped' => false,
                    ]
                )
            ;

        }

        $builder
            ->add('reportable', ToggleType::class,
                [
                    'label' => 'Reportable',
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 4/09/2020 09:39
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Enrolment',
                'data_class' => CourseClassStudent::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 4/09/2020 09:37
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

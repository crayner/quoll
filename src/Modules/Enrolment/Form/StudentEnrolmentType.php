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
 * Date: 9/09/2020
 * Time: 11:41
 */
namespace App\Modules\Enrolment\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StudentEnrolmentType
 * @package App\Modules\Enrolment\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentEnrolmentType extends AbstractType
{
    /**
     * buildForm
     *
     * 9/09/2020 11:43
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('academicYearName', DisplayType::class,
                [
                    'mapped' => false,
                    'label' => 'Academic Year',
                    'translation_domain' => 'School',
                    'data' => $options['data']->getAcademicYear()->getName(),
                ]
            )
            ->add('academicYear', HiddenEntityType::class,
                [
                    'class' => AcademicYear::class,
                ]
            )
            ->add('studentName', DisplayType::class,
                [
                    'mapped' => false,
                    'label' => 'Student',
                    'translation_domain' => 'Student',
                    'data' => $options['data']->getStudent()->getFullName('Preferred'),
                ]
            )
            ->add('student', HiddenEntityType::class,
                [
                    'class' => Student::class,
                ]
            )
            ->add('yearGroup', EntityType::class,
                [
                    'label' => 'Year Group',
                    'translation_domain' => 'School',
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'class' => YearGroup::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('yg')
                            ->orderBy('yg.sortOrder', 'ASC')
                        ;
                    },
                ]
            )
            ->add('rollGroup', EntityType::class,
                [
                    'label' => 'Roll Groups',
                    'translation_domain' => 'School',
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'class' => RollGroup::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('rg')
                            ->orderBy('rg.name', 'ASC')
                            ->where('rg.academicYear = :current')
                            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                            ;
                    },
                ]
            )
            ->add('rollOrder', IntegerType::class,
                [
                    'label' => 'Roll Order',
                    'required' => false,
                ]
            )
            ->add('studentHistory', DisplayType::class,
                [
                    'label' => 'Student History',
                    'mapped' => false,
                    'data' => $options['data']->getStudent()->getStudentHistory(),
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 9/09/2020 11:43
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'translation_domain' => 'Enrolment',
                    'data_class' => StudentEnrolment::class,
                ]
            )
        ;
    }

    /**
     * getParent
     *
     * 9/09/2020 11:43
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

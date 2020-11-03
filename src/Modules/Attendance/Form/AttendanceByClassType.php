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
 * Date: 2/10/2020
 * Time: 16:03
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFormType;
use App\Modules\Attendance\Manager\Hidden\AttendanceByClass;
use App\Modules\Attendance\Manager\TeacherManager;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceByClassType
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByClassType extends AbstractType
{
    /**
     * AttendanceByClassType constructor.
     *
     * @param TeacherManager $manager
     */
    public function __construct(TeacherManager $manager)
    {
    }

    /**
     * buildForm
     *
     * 3/11/2020 13:46
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('class', AutoSuggestEntityType::class,
                [
                    'class' => CourseClass::class,
                    'label' => 'Course Class',
                    'choice_label' => 'getFullName',
                    'query_builder' => TeacherManager::getClassListQuery(),
                ]
            )
            ->add('date', ReactDateType::class,
                [
                    'label' => 'Date',
                    'input' => 'datetime_immutable',
                    'attr' => [
                        'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                        'max' => AcademicYearHelper::getCurrentAcademicYear()->getLastDay()->format('Y-m-d'),
                    ],
                ]
            )
            ->add('submit',SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 2/10/2020 16:11
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Attendance',
                'data_class' => AttendanceByClass::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 2/10/2020 16:10
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

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
 * Date: 17/10/2020
 * Time: 08:18
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactCollectionType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Manager\AttendanceByRollGroupManager;
use App\Modules\Attendance\Validator\AttendanceLogTime;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class AttendanceByRollGroupType
 *
 * 17/10/2020 08:19
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByRollGroupType extends AbstractType
{
    /**
     * buildForm
     *
     * 17/10/2020 08:30
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']->isValid()) {
            $defaultAttendanceCode = ProviderFactory::getRepository(AttendanceCode::class)->findOneByName(SettingFactory::getSettingManager()->get('Attendance', 'defaultRollGroupAttendanceType', 'Present'));
            $builder
                ->add('students', ReactCollectionType::class,
                    [
                        'entry_type' => AttendanceForStudentType::class,
                        'element_delete_route' => false,
                        'special' => 'display_student_attendance',
                        'special_data' => [
                            'default_code' => $defaultAttendanceCode->getId(),
                            'inOrOut' => ProviderFactory::getRepository(AttendanceCode::class)->findInOrOut(),
                        ],
                        'row_style' => 'single',
                    ]
                )
                ->add('changeAll', AttendanceRollGroupChangeAllType::class)
            ;
        }

        $builder
            ->add('header', HeaderType::class,
                [
                    'label' => 'Choose Roll Group',
                ]
            )
            ->add('rollGroup', AutoSuggestEntityType::class,
                [
                    'class' => RollGroup::class,
                    'choice_label' => 'name',
                    'label' => 'Roll Group',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('rg')
                            ->where('rg.academicYear = :current')
                            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                            ->orderBy('rg.name')
                        ;
                    },
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('date', ReactDateType::class,
                [
                    'label' => 'Date',
                    'attr' => [
                        'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                        'max' => date('Y-m-d'),
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Range(
                            [
                                'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                                'max' => date('Y-m-d')
                            ]
                        ),
                    ],

                ]
            )
        ;
        $times = AttendanceByRollGroupManager::getDailyTimeList();
        if (count($times) > 1) {
            $builder
                ->add('dailyTime', EnumType::class,
                    [
                        'choice_list_class' => AttendanceByRollGroupManager::class,
                        'label' => 'Attendance (Daily) Record Time',
                        'choice_list_prefix' => false,
                        'placeholder' => 'Please select...',
                        'constraints' => [
                            new NotBlank(),
                            new Choice(['choices' => AttendanceByRollGroupManager::getDailyTimeList()]),
                        ],
                    ]
                )
                ->add('autoFill', ToggleType::class,
                    [
                        'label' => 'Attendance.autoFillDailyTimes.name',
                        'help' => 'Attendance.autoFillDailyTimes.description',
                        'translation_domain' => 'Setting',
                        'mapped' => false,
                        'data' => SettingFactory::getSettingManager()->get('Attendance', 'autoFillDailyTimes', false),
                    ]
                )
            ;
        }

        $builder
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     *
     * 17/10/2020 08:21
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Attendance',
                'data_class' => AttendanceByRollGroupManager::class,
                'constraints' => [
                    new AttendanceLogTime(),
                ],
            ]
        );
    }

    /**
     * getParent
     *
     * 17/10/2020 08:20
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

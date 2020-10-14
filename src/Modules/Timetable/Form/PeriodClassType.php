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
 * Date: 13/10/2020
 * Time: 16:24
 */
namespace App\Modules\Timetable\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\ReactFormType;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Entity\Facility;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PeriodClassType
 *
 * 13/10/2020 16:25
 * @package App\Modules\Timetable\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PeriodClassType extends AbstractType
{
    /**
     * buildForm
     *
     * 13/10/2020 16:26
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timetableDisplay', DisplayType::class,
                [
                    'mapped' => false,
                    'data' => $options['data']->getPeriod()->getTimetableDay()->getTimetable()->getName(),
                    'label' => 'Timetable',
                ]
            )
            ->add('timetableDayDisplay', DisplayType::class,
                [
                    'mapped' => false,
                    'data' => $options['data']->getPeriod()->getTimetableDay()->getName(),
                    'label' => 'Timetable Day',
                ]
            )
            ->add('periodDisplay', DisplayType::class,
                [
                    'mapped' => false,
                    'data' => $options['data']->getPeriod()->getName(),
                    'label' => 'Period',
                ]
            )
        ;
        if ($options['data']->getId()) {
            $builder
                ->add('classDisplay', DisplayType::class,
                    [
                        'mapped' => false,
                        'data' => $options['data']->getCourseClass()->getFullName(),
                        'label' => 'Class',
                    ]
                )
            ;
        } else {
            $classes = ProviderFactory::create(TimetablePeriodClass::class)->findByPeriod($options['data']->getPeriod(), 'cc.id');
            $builder
                ->add('courseClass', AutoSuggestEntityType::class,
                    [
                        'label' => 'Class',
                        'class' => CourseClass::class,
                        'help' => 'Enter any part of the name to select...',
                        'choice_label' => 'getFullName',
                        'placeholder' => 'Enter any part of the name to select...',
                        'query_builder' => function(EntityRepository $er) use ($classes) {
                            return $er->createQueryBuilder('cc')
                                ->leftJoin('cc.course', 'c')
                                ->orderBy('c.abbreviation','ASC')
                                ->addOrderBy('cc.name','ASC')
                                ->where('cc.id NOT IN (:classes)')
                                ->setParameter('classes', $classes, Connection::PARAM_STR_ARRAY)
                            ;
                        },
                    ]
                )
            ;

        }
        $inUse = ProviderFactory::create(TimetablePeriodClass::class)->findByPeriod($options['data']->getPeriod(), 'f.id', $options['data']->getFacility() ? $options['data']->getFacility()->getId() : null);
        $builder
            ->add('facility', AutoSuggestEntityType::class,
                [
                    'label' => 'Location',
                    'class' => Facility::class,
                    'choice_label' => 'name',
                    'help' => 'Enter any part of the name to select...',
                    'placeholder' => 'Enter any part of the name to select...',
                    'query_builder' => function(EntityRepository $er) use ($inUse) {
                        return $er->createQueryBuilder('f')
                            ->orderBy('f.name','ASC')
                            ->where('f.id NOT IN (:facilities)')
                            ->setParameter('facilities', $inUse, Connection::PARAM_STR_ARRAY)
                            ;
                    },
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     *
     * 13/10/2020 16:25
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Timetable',
                'data_class' => TimetablePeriodClass::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 13/10/2020 16:25
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

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
 * Date: 3/01/2020
 * Time: 16:05
 */
namespace App\Modules\RollGroup\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\Facility;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Staff\Entity\Staff;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RollGroupType
 * @package App\Modules\RollGroup\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RollGroupType extends AbstractType
{
    /**
     * buildForm
     *
     * 5/11/2020 08:21
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('academicYear', DisplayType::class,
                [
                    'label' => 'Academic Year',
                    'mapped' => false,
                    'data' => $options['data']->getAcademicYear()->getName(),
                ]
            )
            ;
        if ($options['data']->getYearGroup()) {
            $builder
                ->add('yearGroup', HiddenEntityType::class,
                    [
                        'class' => YearGroup::class,
                    ]
                )
                ->add('yearGroupDisplay', DisplayType::class,
                    [
                        'label' => 'Year Group',
                        'help' => 'Once set, the year group cannot be changed.',
                        'data' => $options['data']->getYearGroup()->getName(),
                        'mapped' => false,
                    ]
               )
            ;
        } else {
            $builder
                ->add('yearGroup', EntityType::class,
                    [
                        'label' => 'Year Group',
                        'help' => 'Once set, the year group cannot be changed.',
                        'class' => YearGroup::class,
                        'choice_label' => 'name',
                        'placeholder' => 'Please select...',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('yg')
                                ->orderBy('yg.sortOrder');
                        },
                    ]
                )
            ;
        }
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Needs to be unique in the academic year.',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Needs to be unique in the academic year.',
                ]
            )
            ->add('tutor', AutoSuggestEntityType::class,
                [
                    'label' => 'Main Tutor',
                    'class' => Staff::class,
                    'placeholder' => 'Type any part of the name...',
                    'choice_label' => 'getFullNameReversed',
                    'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQuery(),
                ]
            )
            ->add('tutor2', AutoSuggestEntityType::class,
                [
                    'label' => '2nd Tutor',
                    'class' => Staff::class,
                    'placeholder' => 'Type any part of the name...',
                    'choice_label' => 'getFullNameReversed',
                    'required' => false,
                    'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQuery(),
                ]
            )
            ->add('tutor3', AutoSuggestEntityType::class,
                [
                    'label' => '3rd Tutor',
                    'class' => Staff::class,
                    'required' => false,
                    'placeholder' => 'Type any part of the name...',
                    'choice_label' => 'getFullNameReversed',
                    'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQuery(),
                ]
            )
            ->add('assistant', AutoSuggestEntityType::class,
                [
                    'label' => 'Educational Assistant',
                    'class' => Staff::class,
                    'placeholder' => 'Type any part of the name...',
                    'choice_label' => 'getFullNameReversed',
                    'required' => false,
                    'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQuery(),
                ]
            )
            ->add('assistant2', AutoSuggestEntityType::class,
                [
                    'label' => '2nd Educational Assistant',
                    'class' => Staff::class,
                    'placeholder' => 'Type any part of the name...',
                    'required' => false,
                    'choice_label' => 'getFullNameReversed',
                    'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQuery(),
                ]
            )
            ->add('assistant3', AutoSuggestEntityType::class,
                [
                    'label' => '3rd Educational Assistant',
                    'class' => Staff::class,
                    'placeholder' => 'Type any part of the name...',
                    'required' => false,
                    'choice_label' => 'getFullNameReversed',
                    'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQuery(),
                ]
            )
            ->add('facility', EntityType::class,
                [
                    'label' => 'Room',
                    'class' => Facility::class,
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.name')
                            ;
                    },
                ]
            )
            ->add('nextRollGroup', EntityType::class,
                [
                    'label' => 'Next Roll Group',
                    'help' => 'Sets student progression on rollover.',
                    'class' => RollGroup::class,
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'required' => false,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name')
                            ->where('r.academicYear = :year')
                            ->setParameter('year', AcademicYearHelper::getCurrentAcademicYear())
                        ;
                    },
                ]
            )
            ->add('attendance', ToggleType::class,
                [
                    'label' => 'Track Attendance',
                    'help' => 'Should this class allow attendance to be taken?',
                ]
            )
            ->add('website', UrlType::class,
                [
                    'label' => 'Website',
                    'required' => false,
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
                'translation_domain' => 'RollGroup',
                'data_class' => RollGroup::class,
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
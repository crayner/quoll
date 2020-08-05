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
 * Date: 3/08/2020
 * Time: 14:38
 */
namespace App\Modules\Timetable\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\YearGroup;
use App\Modules\Timetable\Entity\Timetable;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TimetableType
 * @package App\Modules\Timetable\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('academicYearDisplay',DisplayType::class,
                [
                    'label' => 'Academic Year',
                    'help' => 'This value is locked.',
                    'data' => $options['data']->getAcademicYear()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('academicYear', HiddenEntityType::class,
                [
                    'class' => AcademicYear::class,
                ]
            )
            ->add('name',TextType::class,
                [
                    'label' => 'Name',
                ]
            )
            ->add('abbreviation',TextType::class,
                [
                    'label' => 'Abbreviation',
                ]
            )
            ->add('displayMode',EnumType::class,
                [
                    'label' => 'Display Mode',
                    'placeholder' => ' ',
                ]
            )
            ->add('active',ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('yearGroups',EntityType::class,
                [
                    'label' => 'Year Groups',
                    'class' => YearGroup::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('yg')
                            ->orderBy('yg.sortOrder', 'ASC')
                        ;
                    },
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 3/08/2020 14:40
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Timetable',
                'data_class' => Timetable::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 3/08/2020 14:39
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

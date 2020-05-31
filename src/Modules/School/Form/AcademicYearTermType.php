<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 21/12/2019
 * Time: 20:07
 */
namespace App\Modules\School\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFormType;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearTerm;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AcademicYearTermType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearTermType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('termHeader', HeaderType::class,
                [
                    'label' => intval($options['data']->getId()) > 0 ? 'Edit Academic Year Term' : 'Add Academic Year Term',
                ]
            )
            ->add('academicYear', EntityType::class,
                [
                    'label' => 'Academic Year',
                    'placeholder' => 'Please select...',
                    'class' => AcademicYear::class,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('y')
                            ->orderBy('y.firstDay', 'ASC')
                            ->addOrderBy('y.name', 'ASC');
                    },
                    'choice_label' => 'name',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique in the Academic Year',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique in the Academic Year',
                ]
            )
            ->add('firstDay', ReactDateType::class,
                [
                    'label' => 'First Day',
                    'input' => 'datetime_immutable'
                ]
            )
            ->add('lastDay', ReactDateType::class,
                [
                    'label' => 'Last Day',
                    'input' => 'datetime_immutable'
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
                'translation_domain' => 'School',
                'data_class' => AcademicYearTerm::class,
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
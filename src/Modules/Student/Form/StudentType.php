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
 * Date: 20/07/2020
 * Time: 08:53
 */
namespace App\Modules\Student\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Modules\System\Entity\Locale;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * Class StudentType
 * @package App\Modules\Student\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 20/07/2020 09:04
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $options['data']->getPerson();
        $academicYear = AcademicYearHelper::getCurrentAcademicYear();
        $builder
            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                ]
            )
            ->add('studentHeader', HeaderType::class,
                [
                    'label' => 'Student Details',
                ]
            )
            ->add('studentIdentifier', TextType::class,
                [
                    'label' => 'Student Identifier',
                    'help' => 'Unique if present.'
                ]
            )
        ;
        if (count(SettingFactory::getSettingManager()->get('School Admin','studentAgreementOptions')) > 0) {
            $builder
                ->add('studentAgreements', Choice::class,
                    [
                        'label' => 'Signed Student Agreements',
                        'multiple' => true,
                        'choices' => SettingFactory::getSettingManager()->get('School Admin', 'studentAgreementOptions'),
                    ]
                )
            ;
        }
        $builder
            ->add('lastSchool', TextType::class,
                [
                    'label' => 'Previous School',
                    'help' => 'This student transferred from this school.',
                ]
            )
            ->add('nextSchool', TextType::class,
                [
                    'label' => 'Next School',
                    'help' => 'This student transferred to this school.',
                ]
            )
            ->add('departureReason', TextType::class,
                [
                    'label' => 'Reason for Departure',
                    'help' => 'Why did this student leave this school?',
                ]
            )
            ->add('transport', TextType::class,
                [
                    'label' => 'Transport',
                ]
            )
            ->add('transportNotes', TextareaType::class,
                [
                    'label' => 'Transport Notes',
                    'attr' => [
                        'rows' => 4,
                    ],
                ]
            )
        ;
        if (count(SettingFactory::getSettingManager()->get('People','dayTypeOptions')) > 0) {
            $builder
                ->add('dayType', Choice::class,
                    [
                        'label' => 'Day Type',
                        'choices' => SettingFactory::getSettingManager()->get('People', 'dayTypeOptions'),
                        'placeholder' => 'Please select...',
                    ]
                )
            ;
        }
        $builder
            ->add('graduationYear', EntityType::class,
                [
                    'label' => 'Graduation Year',
                    'class' => AcademicYear::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                ]
            )
            ->add('submit', SubmitType::class)
        ;

    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 20/07/2020 08:55
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Student',
                'data_class' => Student::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 20/07/2020 08:54
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

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
use App\Form\Type\ReactCollectionType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\CustomFieldDataType;
use App\Modules\People\Form\Subscriber\CustomFieldDataSubscriber;
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
                    'help' => 'Unique if present.',
                    'required' => false,
                ]
            )
        ;
        if (count(SettingFactory::getSettingManager()->get('School Admin','studentAgreementOptions')) > 0) {
            $builder
                ->add('studentAgreements', Choice::class,
                    [
                        'label' => 'Signed Student Agreements',
                        'multiple' => true,
                        'required' => false,
                        'choices' => SettingFactory::getSettingManager()->get('School Admin', 'studentAgreementOptions'),
                    ]
                )
            ;
        }
        $builder
            ->add('lastSchool', TextType::class,
                [
                    'label' => 'Previous School',
                    'required' => false,
                    'help' => 'This student transferred from this school.',
                ]
            )
            ->add('nextSchool', TextType::class,
                [
                    'label' => 'Next School',
                    'required' => false,
                    'help' => 'This student transferred to this school.',
                ]
            )
            ->add('departureReason', TextType::class,
                [
                    'label' => 'Reason for Departure',
                    'required' => false,
                    'help' => 'Why did this student leave this school?',
                ]
            )
            ->add('transport', TextType::class,
                [
                    'required' => false,
                    'label' => 'Transport',
                ]
            )
            ->add('transportNotes', TextareaType::class,
                [
                    'label' => 'Transport Notes',
                    'required' => false,
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
                        'required' => false,
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
                    'required' => false,
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                ]
            )
        ;
        if ($this->hasCustomFields()) {
            $builder
                ->add('customHeader', HeaderType::class,
                    [
                        'label' => 'Custom Data',
                        'translation_domain' => 'People',
                    ]
                )
                ->add('customData', ReactCollectionType::class,
                    [
                        'entry_type' => CustomFieldDataType::class,
                        'entry_options' => [
                            'category' => 'Staff',
                        ],
                        'allow_add' => false,
                        'allow_delete' => false,
                        'element_delete_route' => false,
                        'column_count' => 2,
                        'row_style' => 'transparent',
                    ]
                )
            ;
            $builder
                ->get('customData')
                ->addEventSubscriber(new CustomFieldDataSubscriber());
        }
        $builder
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

    /**
     * hasCustomFields
     * @return bool
     * 29/07/2020 13:49
     */
    private function hasCustomFields(): bool
    {
        return ProviderFactory::getRepository(CustomField::class)->countByCategory('Student') > 0;
    }
}

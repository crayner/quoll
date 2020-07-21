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
 * Date: 18/07/2020
 * Time: 11:44
 */
namespace App\Modules\Staff\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Person;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Entity\I18n;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StaffType
 * @package App\Modules\Staff\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 18/07/2020 11:47
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $options['data']->getPerson();
        $locale = ProviderFactory::getRepository(I18n::class)->findOneByCode(ParameterBagHelper::get('locale'));
        $builder
            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                ]
            )
            ->add('staffHeader', HeaderType::class,
                [
                    'label' => 'Staff Details',
                ]
            )
            ->add('type', EnumType::class,
                [
                    'label' => 'Staff Type',
                    'placeholder' => 'Please select...',
                ]
            )
            ->add('jobTitle', TextType::class,
                [
                    'label' => 'Job Title',
                ]
            )
            ->add('smartWorkflowHelp', ToggleType::class,
                [
                    'label' => 'Enable Smart Workflow Help?',
                ]
            )
            ->add('firstAidQualified', ToggleType::class,
                [
                    'label' => 'First Aid Qualified?',
                    'visible_by_choice' => 'first_aid',
                ]
            )
            ->add('firstAidExpiry', DateType::class,
                [
                    'label' => 'First Aid Qualification Expiry',
                    'visible_parent' => 'staff_firstAidQualified',
                    'visible_values' => ['first_aid'],
                    'input' => 'datetime_immutable',
                ]
            )
            ->add('biographyHeader', HeaderType::class,
                [
                    'label' => 'Biography Details',
                ]
            )
            ->add('qualifications', TextType::class,
                [
                    'label' => 'Qualification',
                ]
            )
            ->add('biographicalGrouping', TextareaType::class,
                [
                    'label' => 'Grouping',
                    'help' => 'Used to group staff when creating a staff directory.',
                ]
            )
            ->add('biographicalGroupingPriority', TextareaType::class,
                [
                    'label' => 'Grouping Priority',
                    'help' => 'Higher numbers move teachers up the order within their grouping.',
                ]
            )
            ->add('biography', TextareaType::class,
                [
                    'label' => 'Biography',
                    'attr' => [
                        'rows' => 8,
                    ],
                ]
            )
            ->add('emergencyHeader', HeaderType::class,
                [
                    'label' => 'Emergency Contact Details',
                    'help' => 'Emergency contacts must be added as people within the database. You can then attach them here as an emergency contact for this person.'
                ]
            )
            ->add('emergencyContact1', EntityType::class,
                [
                    'label' => 'Emergency Contact #1',
                    'class' => Person::class,
                    'choice_label' => 'fullNameReversed',
                    'placeholder' => ' ',
                    'choice_translation_domain' => false,
                    'query_builder' => function(EntityRepository $er) use ($person) {
                        return $er->createQueryBuilder('p')
                            ->where('p.id <> :self')
                            ->andWhere('p.student IS NULL')
                            ->setParameter('self', $person->getId())
                            ->orderBy('p.surname', 'ASC')
                            ->addOrderBy('p.firstName', 'ASC')
                        ;
                    },
                ]
            )
            ->add('emergencyContact2', EntityType::class,
                [
                    'label' => 'Emergency Contact #2',
                    'class' => Person::class,
                    'choice_label' => 'fullNameReversed',
                    'placeholder' => ' ',
                    'choice_translation_domain' => false,
                    'query_builder' => function(EntityRepository $er) use ($person) {
                        return $er->createQueryBuilder('p')
                            ->where('p.id <> :self')
                            ->andWhere('p.student IS NULL')
                            ->setParameter('self', $person->getId())
                            ->orderBy('p.surname', 'ASC')
                            ->addOrderBy('p.firstName', 'ASC')
                        ;
                    },
                ]
            )
            ->add('schoolHeader', HeaderType::class,
                [
                    'label' => 'Staff School Details',
                ]
            )
            ->add('viewCalendarSpaceBooking', ToggleType::class,
                [
                    'label' => 'View Facility Booking Details',
                    'visible_parent' => 'staff_viewCalendarPersonal',
                    'visible_values' => ['personal_calendar'],
                ]
            )
            ->add('vehicleRegistration', TextType::class,
                [
                    'label' => 'Vehicle Registration',
                ]
            )
            ->add('locale', EntityType::class,
                [
                    'label' => 'Personal Locale Override!',
                    'help' => 'The system default is "{name}" and is not available to select.',
                    'help_translation_parameters' => ['{name}' => $locale->getName()],
                    'class' => I18n::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                            ->orderBy('i.name','ASC')
                            ->where('i.active = :true')
                            ->andWhere('i.systemDefault = :false')
                            ->setParameters(['true' => true, 'false' => false])
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
     * 19/07/2020 10:49
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Staff',
                'data_class' => Staff::class
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 18/07/2020 11:45
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
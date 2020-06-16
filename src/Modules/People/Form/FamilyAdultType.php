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
 * Date: 6/12/2019
 * Time: 14:54
 */

namespace App\Modules\People\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\Subscriber\FamilyAdultSubscriber;
use App\Modules\Security\Util\SecurityHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FamilyAdultType
 * @package App\Modules\People\Form
 */
class FamilyAdultType extends AbstractType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => FamilyMemberAdult::class,
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

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']->getId() !== null) {
            $builder
                ->add('adultEditHeader', HeaderType::class,
                    [
                        'label' => 'Edit Adult',
                        'help' => 'This person could be a parent, guardian or emergency contact for any student.'
                    ]
                )
                ->add('adultNote', ParagraphType::class,
                    [
                        'wrapper_class' => 'warning',
                        'help' => 'contact_priority_logic'
                    ]
                )
                ->add('personName', DisplayType::class,
                    [
                        'label' => "Adult's Name",
                        'help' => 'This value cannot be changed',
                        'data' => $options['data']->getPerson()->formatName(['style' => 'formal']),
                        'mapped' => false,
                    ]
                )
                ->add('person', HiddenEntityType::class,
                    [
                        'class' => Person::class,
                    ]
                )
            ;
        } else {
            $parentRoles = SecurityHelper::getHierarchy()->getReachableRoleNames(['ROLE_PARENT']);
            $builder
                ->add('adultEditHeader', HeaderType::class,
                    [
                        'label' => 'Edit Adult / Guardian',
                        'help' => 'Family name: {name}',
                        'help_translation_parameters' => [
                            '{name}' => $options['data']->getFamily()->getName(),
                        ],
                    ]
                )
                ->add('showHideForm', ToggleType::class,
                    [
                        'label' => 'Add Adult to family',
                        'visible_by_choice' => 'showAdultAdd',
                        'mapped' => false,
                        'data' => 'N',
                    ]
                )
                ->add('adultNote', ParagraphType::class,
                    [
                        'visible_values' => ['showAdultAdd'],
                        'visible_parent' => 'family_adult_showHideForm',
                        'wrapper_class' => 'warning',
                        'help' => 'contact_priority_logic'
                    ]
                )
                ->add('person', EntityType::class,
                    [
                        'label' => "Adult's Name",
                        'class' => Person::class,
                        'choice_label' => 'fullNameReversed',
                        'placeholder' => 'Please select...',
                        'query_builder' => function(EntityRepository $er) use ($parentRoles) {
                            $query = $er->createQueryBuilder('p')
                                ->orderBy('p.surname', 'ASC')
                                ->groupBy('p.id')
                                ->addOrderBy('p.preferredName', 'ASC');
                            foreach($parentRoles as $q=>$role) {
                                $query->orWhere('p.securityRoles LIKE :role' . $q)
                                    ->setParameter('role' . $q, '%'.$role.'%');
                            }
                            return $query;
                        },
                        'visible_values' => ['showAdultAdd'],
                        'visible_parent' => 'family_adult_showHideForm',
                    ]
                )
            ;
        }
        $builder
            ->add('comment', TextareaType::class,
                [
                    'label' => 'Comment'   ,
                    'required' => false,
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'attr' => [
                        'rows' => 5,
                        'class' => 'w-full',
                    ],
                ]
            )
            ->add('childDataAccess', ToggleType::class,
                [
                    'label' => 'Data Access?',
                    'visible_parent' => 'family_adult_showHideForm',
                    'visible_values' => ['showAdultAdd'],
                    'help' => 'Access data on family members?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactPriority', IntegerType::class,
                [
                    'label' => 'Contact Priority',
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'help' => 'The order in which school should contact family members.',
                ]
            )
            ->add('contactCall', ToggleType::class,
                [
                    'label' => 'Contact by phone call?',
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'help' => 'Receive non-emergency phone calls from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactSMS', ToggleType::class,
                [
                    'label' => 'Contact by SMS?',
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'help' => 'Receive non-emergency SMS messages from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactEmail', ToggleType::class,
                [
                    'label' => 'Contact by Email?',
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'help' => 'Receive non-emergency emails from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactMail', ToggleType::class,
                [
                    'label' => 'Contact by Mail?',
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'help' => 'Receive postage mail from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('panelName', HiddenType::class,
                [
                    'data' => 'Adults',
                    'mapped' => false,
                ]
            )
            ->add('family', HiddenEntityType::class,
                [
                    'class' => Family::class,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'visible_values' => ['showAdultAdd'],
                    'visible_parent' => 'family_adult_showHideForm',
                    'label' => 'Submit',
                ]
            )
        ;
        $builder->addEventSubscriber(new FamilyAdultSubscriber());
    }
}
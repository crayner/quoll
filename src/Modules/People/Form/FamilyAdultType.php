<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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
use App\Modules\People\Entity\FamilyAdult;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\Subscriber\FamilyAdultSubscriber;
use App\Provider\ProviderFactory;
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
                'data_class' => FamilyAdult::class,
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
        $parentRole = 'ROLE_PARENT';
        if ($options['data']->getId() > 0) {
            $builder
                ->add('adultEditHeader', HeaderType::class,
                    [
                        'label' => 'Edit Adult / Guardian',
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
                        'label' => 'Adult\'s Name',
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
                ->add('adultNote', ParagraphType::class,
                    [
                        'wrapper_class' => 'warning',
                        'help' => 'contact_priority_logic'
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
                        'wrapper_class' => 'warning',
                        'help' => 'contact_priority_logic'
                    ]
                )
                ->add('person', EntityType::class,
                    [
                        'label' => 'Adult\'s Name',
                        'class' => Person::class,
                        'choice_label' => 'fullName',
                        'placeholder' => 'Please select...',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('p')
                                ->select(['p','s'])
                                ->leftjoin('p.studentEnrolments','se')
                                ->leftJoin('p.staff', 's')
                                ->where('se.id IS NOT NULL')
                                ->orderBy('p.surname', 'ASC')
                                ->groupBy('p.id')
                                ->addOrderBy('p.preferredName', 'ASC');
                        },
                        'visible_values' => ['showAdultAdd'],
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
                    'attr' => [
                        'rows' => 5,
                        'class' => 'w-full',
                    ],
                ]
            )
            ->add('childDataAccess', ToggleType::class,
                [
                    'label' => 'Data Access?',
                    'visible_values' => ['showAdultAdd'],
                    'help' => 'Access data on family members?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactPriority', IntegerType::class,
                [
                    'label' => 'Contact Priority',
                    'visible_values' => ['showAdultAdd'],
                    'help' => 'The order in which school should contact family members.',
                ]
            )
            ->add('contactCall', ToggleType::class,
                [
                    'label' => 'Contact by phone call?',
                    'visible_values' => ['showAdultAdd'],
                    'help' => 'Receive non-emergency phone calls from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactSMS', ToggleType::class,
                [
                    'label' => 'Contact by SMS?',
                    'visible_values' => ['showAdultAdd'],
                    'help' => 'Receive non-emergency SMS messages from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactEmail', ToggleType::class,
                [
                    'label' => 'Contact by Email?',
                    'visible_values' => ['showAdultAdd'],
                    'help' => 'Receive non-emergency emails from school?',
                    'wrapper_class' => 'flex-1 relative text-right',
                ]
            )
            ->add('contactMail', ToggleType::class,
                [
                    'label' => 'Contact by Mail?',
                    'visible_values' => ['showAdultAdd'],
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
                    'label' => 'Submit',
                ]
            )
        ;
        $builder->addEventSubscriber(new FamilyAdultSubscriber());
    }
}
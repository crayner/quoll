<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 22/11/2019
 * Time: 15:00
 */
namespace App\Modules\People\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
use App\Modules\People\Manager\AddressManager;
use App\Modules\People\Util\UserHelper;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\House;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Util\LocaleHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class PersonType
 * @package App\Modules\People\Form
 */
class PersonType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $hierarchy;

    /**
     * @var AddressManager
     */
    private $manager;

    /**
     * PersonType constructor.
     * @param RouterInterface $router
     * @param RoleHierarchyInterface $hierarchy
     * @param AddressManager $manager
     */
    public function __construct(RouterInterface $router, RoleHierarchyInterface $hierarchy, AddressManager $manager)
    {
        $this->router = $router;
        $this->hierarchy = $hierarchy;
        $this->manager = $manager;
    }


    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $options['data'];
        $builder
            ->add('basicHeader', HeaderType::class,
                [
                    'label' => 'Basic Information',
                ]
            )
            ->add('title', EnumType::class,
                [
                    'label' => 'Title',
                    'required' => true,
                    'placeholder' => ' ',
                ]
            )
            ->add('surname', TextType::class,
                [
                    'label' => 'Surname',
                    'help' => 'Family name as shown in ID documents.',
                ]
            )
            ->add('firstName', TextType::class,
                [
                    'label' => 'Given Names',
                    'help' => 'Given names as shown in ID documents.',
                ]
            )
            ->add('preferredName', TextType::class,
                [
                    'label' => 'Preferred Name',
                    'help' => 'Most common name, alias, nickname, etc.',
                ]
            )
            ->add('officialName', TextType::class,
                [
                    'label' => 'Official Name',
                    'help' => 'Full name as shown in ID documents.',
                ]
            )
            ->add('nameInCharacters', TextType::class,
                [
                    'label' => 'Name In Characters',
                    'help' => 'Chinese or other character-based name.',
                ]
            )
            ->add('gender', EnumType::class,
                [
                    'label' => 'Gender',
                ]
            )
            ->add('submitBasic', SubmitType::class)
        ;

        if ($person->canBeStaff()) {
            $builder
                ->add('addStaff', ButtonType::class,
                    [
                        'label' => 'Add to Staff',
                        'on_click' => [
                            'route' => '/staff/' . $person->getId() . '/add/',
                            'function' => 'callRoute',
                        ],
                    ]
                )
            ;
        }
        if ($person->canBeStudent()) {
            $builder
                ->add('addStudent', ButtonType::class,
                    [
                        'label' => 'Add to Student',
                        'on_click' => [
                            'route' => '/student/' . $person->getId() . '/add/',
                            'function' => 'callRoute',
                        ],
                    ]
                )
            ;
        }
        if ($person->canBeCareGiver()) {
            $builder
                ->add('addCareGiver', ButtonType::class,
                    [
                        'label' => 'Add to Care Giver',
                        'on_click' => [
                            'route' => '/care/giver/' . $person->getId() . '/add/',
                            'function' => 'callRoute',
                        ],
                    ]
                )
            ;
        }
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $emailConstraint = [];
        if (SettingFactory::getSettingManager()->getSettingByScopeAsBoolean('People','uniqueEmailAddress'))
            $emailConstraint = [
                new UniqueEntity(['fields' => ['email'], 'ignoreNull' => true]),
            ];
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Person::class,
                'constraints' => $emailConstraint,
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

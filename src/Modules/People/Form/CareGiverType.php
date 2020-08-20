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
 * Date: 21/07/2020
 * Time: 10:34
 */
namespace App\Modules\People\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactCollectionType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\Subscriber\CustomFieldDataSubscriber;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ParentType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CareGiverType extends AbstractType
{
    /**
     * buildForm
     *
     * 20/08/2020 08:49
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                ]
            )
            ->add('parentHeader', HeaderType::class,
                [
                    'label' => 'Care Giver Details',
                ]
            )
            ->add('receiveNotificationEmails', ToggleType::class,
                [
                    'label' => 'Receive Email Notifications?',

                ]
            )
            ->add('viewCalendarSchool', ToggleType::class,
                [
                    'label' => 'View School Calendar Details',

                ]
            )
            ->add('vehicleRegistration', TextType::class,
                [
                    'label' => 'Vehicle Registration',

                ]
            )
        ;
        if (ProviderFactory::create(CustomField::class)->hasCustomFields('Care Giver')) {
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
                            'category' => 'Care Giver',
                        ],
                        'allow_add' => false,
                        'allow_delete' => false,
                        'element_delete_route' => false,
                        'column_count' => 2,
                        'row_style' => 'transparent',
                    ]
                )
                ->get('customData')
                ->addEventSubscriber(new CustomFieldDataSubscriber());
        }
        $builder
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 20/08/2020 08:49
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => CareGiver::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 21/07/2020 10:34
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

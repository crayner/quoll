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
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Person;
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
     * @param FormBuilderInterface $builder
     * @param array $options
     * 21/07/2020 10:37
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
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 21/07/2020 10:35
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

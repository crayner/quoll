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
 * Date: 23/08/2019
 * Time: 07:45
 */
namespace App\Modules\People\Form;

use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Manager\SettingFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class StaffPreferenceType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PreferenceStaffType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (SettingFactory::getSettingManager()->get('People', 'personalBackground')) {
            $builder
                ->add('personalBackground', ReactFileType::class,
                    [
                        'label' => 'Personal Background',
                        'help_html' => true,
                        'data' => $options['data']->getPersonalBackground(),
                        'required' => false,
                        'file_prefix' => 'personal_bg',
                        'show_thumbnail' => true,
                        'image_method' => 'getPersonalBackground',
                        'delete_route' => $options['remove_background_image'],
                        'entity' => $options['data'],
                    ]
                )
            ;
        }
        $builder
            ->add('calendarFeedPersonal', EmailType::class,
                [
                    'label' => 'Personal Google Calendar ID',
                    'help' => 'Google Calendar ID for your personal calendar.<br/>Only enables timetable integration when logging in via Google.',
                    'help_html' => true,
                    'required' => false,
                    'constraints' => [
                        new Email(),
                    ],
                ]
            )
            ->add('receiveNotificationEmails', ToggleType::class,
                [
                    'label' => 'Receive Email Notifications?',
                    'help' => 'Notifications can always be viewed on screen.',
                ]
            )
            ->add('smartWorkflowHelp', ToggleType::class,
                [
                    'label' => 'Enable Smart Workflow Help?',
                    'required' => false,
                ]
            )
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
                'data_class' => Staff::class,
                'translation_domain' => 'Staff',
                'row_style' => 'transparent',
            ]
        );
        $resolver->setRequired(
            [
                'remove_background_image',
            ]
        );
    }
}

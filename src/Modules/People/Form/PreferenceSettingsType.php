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
 * Date: 20/08/2019
 * Time: 09:33
 */

namespace App\Modules\People\Form;

use App\Modules\People\Util\UserHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Entity\Theme;
use Doctrine\ORM\EntityRepository;
use App\Modules\People\Entity\Person;
use App\Form\Transform\ToggleTransformer;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class PreferenceSettingsType
 * @package App\Modules\People\Form
 */
class PreferenceSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('settingHeader', HeaderType::class,
                [
                    'label' => 'Settings',
                ]
            )
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
        ;
        if (ProviderFactory::create(Setting::class)->getSettingByScopeAsBoolean('People', 'personalBackground')) {
            $builder
                ->add('personalBackground', ReactFileType::class,
                    [
                        'label' => 'Personal Background',
                        'help_html' => true,
                        'data' => $options['data']->getPersonalBackground(),
                        'required' => false,
                        'file_prefix' => 'personal_bg',
                        'delete_security' => 'ROLE_USER',
                        'show_thumbnail' => true,
                        'image_method' => 'getPersonalBackground',
                        'entity' => $options['data'],
                    ]
                )
            ;
        }
        $builder
            ->add('theme', EntityType::class,
                [
                    'label' => 'Personal Theme',
                    'class' => Theme::class,
                    'choice_label' => 'name',
                    'help' => 'Override the system theme.',
                    'placeholder' => 'System Default',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->where('t.active = :yes')
                            ->setParameter('yes', 'Y')
                            ->orderBy('t.name', 'ASC')
                            ;
                    },
                    'required' => false,
                ]
            )
            ->add('i18nPersonal', EntityType::class,
                [
                    'label' => 'Personal Language',
                    'class' => I18n::class,
                    'help' => 'Override the system default language.',
                    'placeholder' => 'System Default',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                            ->where('i.active = :yes')
                            ->setParameter('yes', 'Y')
                            ->orderBy('i.code', 'ASC')
                        ;
                    },
                    'required' => false,
                ]
            )
            ->add('receiveNotificationEmails', ToggleType::class,
                [
                    'label' => 'Receive Email Notifications?',
                    'help' => 'Notifications can always be viewed on screen.',
                ]
            )
        ;
        if ($options['data'] instanceof Person && $options['data']->isStaff()) {
            $staff = ProviderFactory::getRepository(Staff::class)->findOneByPersonOrCreate($options['data']);
            $builder
                ->add('staff', StaffPreferenceSettingsType::class, ['data' => $staff]);
        }
        $builder
            ->add('submit', SubmitType::class, [
                'row_style' => 'single',
            ])
            ->setAction($options['action'])
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'action'
            ]
        );
        $resolver->setDefaults(
            [
                'data_class' => Person::class,
                'translation_domain' => 'People',
                'attr' => [
                    'className' => 'smallIntBorder fullWidth standardForm',
                    'autoComplete' => 'on',
                ],
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
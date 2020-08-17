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
 * Date: 17/01/2020
 * Time: 09:47
 */

namespace App\Modules\Attendance\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Form\SettingsType;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceReasonsType
 * @package App\Modules\Attendance\Form
 */
class AttendanceRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ip = $options['ip'];
        $registeredIPList = SettingFactory::getSettingManager()->get('Attendance', 'studentSelfRegistrationIPAddresses');
        $warning = 'Your current IP address ({ip}) is included in the saved list.';
        $class = 'success';
        if (!in_array($ip, $registeredIPList))
        {
            $warning = 'Your current IP address ({ip}) is not included in the saved list.';
            $class = 'warning';
        }
        $builder
            ->add('header', HeaderType::class,
                [
                    'label' => 'Student Self Registration',
                ]
            )
            ->add('hint', ParagraphType::class,
                [
                    'help' => $warning,
                    'wrapper_class' => $class,
                    'help_translation_parameters' => [
                        '{ip}' => $ip,
                    ],
                ]
            )
            ->add('settings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Attendance',
                            'name' => 'studentSelfRegistrationIPAddresses',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'selfRegistrationRedirect',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'translation_domain' => 'messages',
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
                'translation_domain' => 'Attendance',
                'data_class' => null,
            ]
        );
        $resolver->setRequired(
            [
                'ip',
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
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
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Person;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Form\SettingsType;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceReasonsType
 * @package App\Modules\Attendance\Form
 */
class AttendanceCLIType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $people = [];
        foreach(SettingFactory::getSettingManager()->get('Attendance', 'attendanceCLIAdditionalUsers') as $personID) {
            $people[] = ProviderFactory::getRepository(Staff::class)->find($personID);
        }
        if ($people === []) {
            $people = null;
        }

        $repository = ProviderFactory::getRepository(Staff::class);
        $builder
            ->add('header', HeaderType::class,
                [
                    'label' => 'Attendance CLI',
                ]
            )
            ->add('settings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Attendance',
                            'name' => 'attendanceCLINotifyByRollGroup',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'attendanceCLINotifyByClass',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'attendanceCLIAdditionalUsers',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Staff::class,
                                'multiple' => true,
                                'data' => $people,
                                'choice_label' => 'fullNameReversed',
                                'choice_translation_domain' => false,
                                'query_builder' => $repository->getStaffQuery(),
                                'attr' => [
                                    'style' => ['height' => '140px'],
                                ],
                            ],
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
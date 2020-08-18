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
 * Date: 2/12/2019
 * Time: 16:17
 */
namespace App\Modules\Student\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Student\Util\StudentHelper;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StudentNoteSettingsType
 * @package App\Modules\Student\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentNoteSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('studentNotesHeader', HeaderType::class,
                [
                    'label' => 'Student Notes',
                ]
            )
            ->add('studentNotesSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Students',
                            'name' => 'enableStudentNotes',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Students',
                            'name' => 'noteCreationNotification',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'choice_list_class' => StudentHelper::class,
                                'choice_list_method' => 'getNoteNotificationList',
                                'choice_list_prefix' => 'student.note_notification',
                            ],
                        ],
                    ],
                ]
            )
            ->add('submitNotes', SubmitType::class)
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
                'translation_domain' => 'Student',
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
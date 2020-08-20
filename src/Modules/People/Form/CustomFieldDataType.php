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
 * Date: 29/07/2020
 * Time: 10:45
 */
namespace App\Modules\People\Form;

use App\Form\Type\HiddenEntityType;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Entity\CustomFieldData;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomDataType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CustomFieldDataType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 29/07/2020 10:50
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customField', HiddenEntityType::class,
                [
                    'class' => CustomField::class
                ]
            )
        ;

        if ($options['category'] === 'Staff') {
            $builder
                ->add('staff', HiddenEntityType::class,
                    [
                        'class' => Staff::class
                    ]
                )
            ;
        }
        if ($options['category'] === 'Student') {
            $builder
                ->add('student', HiddenEntityType::class,
                    [
                        'class' => Student::class
                    ]
                )
            ;
        }
        if ($options['category'] === 'Care Giver') {
            $builder
                ->add('careGiver', HiddenEntityType::class,
                    [
                        'class' => CareGiver::class
                    ]
                )
            ;
        }

    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 29/07/2020 10:48
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => CustomFieldData::class,
                'row_style' => 'transparent',
            ]
        );
        $resolver->setRequired(
            [
                'category',
            ]
        );
    }
}

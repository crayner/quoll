<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 14/09/2019
 * Time: 08:35
 */

namespace App\Modules\System\Form;

use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\System\Entity\StringReplacement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class StringReplacementType
 * @package App\Modules\System\Form
 */
class StringReplacementType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('original', TextType::class,
                [
                    'label' => 'Original String',
                ]
            )
            ->add('replacement', TextType::class,
                [
                    'label' => 'Replacement String',
                ]
            )
            ->add('mode', EnumType::class,
                [
                    'label' => 'Mode',
                    'choice_list_prefix' => false,
                ]
            )
            ->add('caseSensitive', ToggleType::class,
                [
                    'label' => 'Case Sensitive',
                ]
            )
            ->add('priority', IntegerType::class,
                [
                    'label' => 'Priority',
                    'help' => 'Higher priorities are substituted first.',
                    'attr' => [
                        'min' => 0,
                        'max' => 99,
                    ],
                    'constraints' => [
                        new Range(['min' => 0, 'max' => 99]),
                    ],
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => StringReplacement::class,
                'translation_domain' => 'System',
            ]
        );
    }
}
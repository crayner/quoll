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
 * Date: 24/07/2019
 * Time: 08:04
 */

namespace App\Modules\System\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Manager\Hidden\Language;
use App\Modules\System\Entity\I18n;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LanguageType
 * @package App\Form\Installation
 */
class LanguageType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titleBar', HeaderType::class,
                [
                    'label' => 'Language Setting'
                ]
            )
            ->add('code', ChoiceType::class,
                [
                    'choices' => I18n::getLanguages(),
                    'choice_translation_domain' => false,
                    'placeholder' => 'Please select...',
                    'label' => 'System Language',
                    'required' => true,
                ]
            )->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            );
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Language::class,
                'translation_domain' => 'System',
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
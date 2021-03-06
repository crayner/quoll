<?php
namespace App\Form\Type;

use App\Form\Transform\ToggleTransformer;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ToggleType
 * @package App\Form\Type
 */
class ToggleType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ToggleTransformer($options['use_boolean_values']));
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'toggle';
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
       $resolver->setDefaults(
           [
               'visible_by_choice' => false,
               'values' => [
                   'Y',
                   'N'
               ],
               'wrapper_class' => 'text-right',
               'label_class' => 'inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs',
               'required' => false,
               'use_boolean_values' => true,
           ]
       );
        $resolver->setAllowedTypes('visible_by_choice', ['boolean', 'string']);
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['visible_by_choice'] = $options['visible_by_choice'];
        $view->vars['visible_values'] = $options['visible_values'];
        if (is_string($options['visible_by_choice'])) {
            $view->vars['choices'] = [
                'Y' => ['data' => $options['visible_parent'] . '_' . $options['visible_by_choice'], 'value' => 'Y', 'label' => TranslationHelper::translate('Yes', [], 'messages')],
                'N' => ['value' => 'N', 'data' => 'N', 'label' => TranslationHelper::translate('No', [], 'messages')],
            ];
        }
        if ($options['visible_by_choice'] === true) {
            throw new InvalidArgumentException(sprintf('The toggle options "visible_by_choice" must be false or a string to identify the value. for %s.', $view->vars['id']));
        }
        $view->vars['choice_translation_domain'] = false;
        $view->vars['values'] = $options['values'];
        $view->vars['errors'] = $form->getParent()->getErrors();
    }
}
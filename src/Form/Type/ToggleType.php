<?php
namespace App\Form\Type;

use App\Form\Transform\ToggleToBooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToggleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['use_boolean_values'])
            $builder->addModelTransformer(new ToggleToBooleanTransformer());
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
               'visibleByClass' => false,
               'visibleWhen' => 'Y',
               'values' => ['Y', 'N'],
               'wrapper_class' => 'text-right',
               'label_class' => 'inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs',
               'required' => false,
               'use_boolean_values' => false,
           ]
       );
        $resolver->setAllowedTypes('visibleByClass', ['boolean', 'string']);
        $resolver->setAllowedTypes('visibleWhen', ['string']);
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['visibleByClass'] = $options['visibleByClass'];
        $view->vars['visibleWhen'] = $options['visibleWhen'];
        $view->vars['values'] = $options['values'];
        $view->vars['errors'] = $form->getParent()->getErrors();
    }
}
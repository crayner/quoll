<?php
namespace App\Form\Type;

use App\Form\EventSubscriber\EnumSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnumType extends AbstractType
{
    /**
     * @return null|string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults(
             [
                 // Translations Prefix
                 'choice_list_prefix' => null,
                 'choice_list_class' => null,
                 'choice_list_method' => null,
                 'visible_by_choice' => false,
                 'visibleWhen' => null,
                 'values' => [],
             ]
         );
        $resolver->setAllowedTypes('visible_by_choice', ['boolean', 'string']);
        $resolver->setAllowedTypes('visibleWhen', ['string','null']);
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'enum_choice';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new EnumSubscriber());
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
        $view->vars['visibleWhen'] = $options['visibleWhen'];
        $view->vars['values'] = $options['values'];
    }
}

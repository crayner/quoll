<?php
namespace App\Form\Type;

use App\Form\Transform\EntityToStringTransformer;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HiddenEntityType
 * @package App\Form\Type
 */
class HiddenEntityType extends AbstractType
{
	/**
	 * @var EntityManagerInterface
	 */
	private $manager;

	/**
	 * HiddenEntityType constructor.
	 *
	 * @param EntityManagerInterface $manager
	 */
	public function __construct(EntityManagerInterface $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addModelTransformer(new EntityToStringTransformer($this->manager, $options));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'hidden_entity';
	}

	public function getParent()
	{
		return HiddenType::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setRequired(
			[
				'class',
			]
		);
		$resolver->setDefaults(
			[
				'multiple' => false,
			]
		);
	}

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
	public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['data'] = ProviderFactory::getRepository($options['class'])->find($view->vars['value']);
    }
}
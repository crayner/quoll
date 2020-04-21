<?php
namespace App\Form\Type;

use App\Form\Transform\FileToStringTransformer;
use App\Form\EventSubscriber\FileSubscriber;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FilePathType extends AbstractType
{
	/**
	 * @var FileSubscriber
	 */
	private $fileSubscriber;

	/**
	 * FileSubscriber constructor.
	 *
	 * @param FileSubscriber $fileSubscriber
	 */
	public function __construct(FileSubscriber $fileSubscriber)
	{
		$this->fileSubscriber = $fileSubscriber;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			[
				'compound'     => false,
				'multiple'     => false,
				'type'         => 'file',
			]
		);

		$resolver->setRequired(
			[
				'file_prefix',
			]
		);
	}

	/**
	 * @return string
	 */
	public function getBlockPrefix()
	{
		return 'file_path';
	}

	/**
	 * @return mixed
	 */
	public function getParent()
	{
		return FileType::class;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addModelTransformer(new FileToStringTransformer());
		$builder->addEventSubscriber($this->fileSubscriber);
	}
}
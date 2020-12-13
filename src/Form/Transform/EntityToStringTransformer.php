<?php
namespace App\Form\Transform;

use App\Manager\EntityInterface;
use App\Provider\ProviderFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityToStringTransformer
 * @package App\Form\Transform
 */
class EntityToStringTransformer implements DataTransformerInterface
{
	/**
	 * @var EntityManager
	 */
	private $om;

    /**
     * @var string
     */
	private $entityClass;

    /**
     * @var
     */
	private $entityType;

    /**
     * @var ServiceEntityRepositoryInterface
     */
	private $entityRepository;

    /**
     * @var bool
     */
    private $multiple;

    /**
     * @var array
     */
    private $options;

    /**
     * EntityToStringTransformer constructor.
     * @param EntityManager|array $om
     * @param array $options
     */
	public function __construct($om, array $options = [])
	{
	    if ($om instanceof EntityManager) {
            $this->om = $om;
            trigger_error(sprintf('The injection of the %s is deprecated in %s.  Use only the options.', EntityManager::class, __CLASS__), E_USER_DEPRECATED);
        } else {
	        $this->om = ProviderFactory::getEntityManager();
	        $options = $om ?: [];
        }

		$resolver = new OptionsResolver();
		$resolver->setDefault('multiple', false);
		$resolver->setRequired('class');
        $resolver->setDefined(array_keys($options));
        $resolver->setAllowedTypes('multiple', ['boolean']);
        $resolver->setAllowedTypes('class', ['string']);
		$options = $resolver->resolve($options);

		if (!is_subclass_of($options['class'], EntityInterface::class))
		    throw new OptionDefinitionException(sprintf('The class "%s" must implement "%s"', $options['class'],EntityInterface::class));

		$this->setEntityClass($options['class']);
		$this->setMultiple($options['multiple']);
		$this->options = $options;
	}

    /**
     * @param $entityClass
     */
    public function setEntityClass($entityClass)
	{
		$this->entityClass = $entityClass;
		$this->setEntityRepository($entityClass);
	}

    /**
     * @param $entityClass
     */
    public function setEntityRepository($entityClass)
	{
		$this->entityRepository = $this->om->getRepository($entityClass);
	}

	/**
	 * @param mixed $entity
	 *
	 * @return string|array
	 */
	public function transform($entity)
	{
        if (!$this->isMultiple())
		{
			if (is_null($entity) || ! $entity instanceof $this->entityClass)
			{
				return '';
			}

			return strval($entity->getId());
		}

		if (is_array($entity))
		    $entity = new ArrayCollection($entity);
		if (is_iterable($entity)) {
            if ($entity->count() === 0)
                return [];
            else {
                $result = [];
                foreach ($entity as $item) {
                    dump($item,$this);
                    if (is_object($item) && $item instanceof $this->entityClass) {
                        $result[] = $item->getId();
                    } else {
                        $result[] = $item;
                    }
                }
                return $result;
            }
        }

		throw new \Exception('What to do with: ' . json_encode($entity) . ' for class ' . $this->entityClass);
	}

	/**
	 * @param mixed $id
	 *
	 * @throws TransformationFailedException
	 *
	 * @return mixed|object
	 */
	public function reverseTransform($id)
	{
        if (!$id || $id === 'Add' || empty($id))
		{
			return null;
		}

		if (is_string($id) || is_int($id))
		{

			$entity = $this->entityRepository->find($id);
			if (null === $entity)
			{
				throw new TransformationFailedException(
					sprintf(
						'A %s with id "%s" does not exist!',
						$this->entityType,
						$id
					)
				);
			}

			return $entity;
		}

		return $id;
	}

	public function setEntityType($entityType)
	{
		$this->entityType = $entityType;
	}

	/**
	 * @return bool
	 */
	public function isMultiple(): bool
	{
		return $this->multiple;
	}

	/**
	 * @param bool $multiple
	 *
	 * @return EntityToStringTransformer
	 */
	public function setMultiple(bool $multiple): EntityToStringTransformer
	{
		$this->multiple = $multiple;

		return $this;
	}

    /**
     * @return null|string
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }
}
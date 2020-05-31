<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 14/05/2020
 * Time: 10:04
 */
namespace App\Modules\People\Form\Transform;

use App\Manager\AbstractEntity;
use App\Manager\EntityInterface;
use App\Modules\People\Manager\AddressManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class PostCodeTransform
 * @package App\Modules\People\Form\Transform
 */
class PostCodeTransform implements DataTransformerInterface
{
    /**
     * @var AddressManager
     */
    private $manager;

    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * PostCodeTransform constructor.
     * @param AddressManager $manager
     */
    public function __construct(AddressManager $manager)
    {
        $this->manager = $manager;
    }


    /**
     * transform
     * @param mixed $value
     * @return mixed|void
     */
    public function transform($value)
    {
        return $this->manager->formatPostCode($this->getEntity());
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return mixed|void
     */
    public function reverseTransform($value)
    {
        return $value;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * Entity.
     *
     * @param EntityInterface $entity
     * @return PostCodeTransform
     */
    public function setEntity($entity): PostCodeTransform
    {
        if (!is_subclass_of($entity, EntityInterface::class)) {
            throw new \TypeError(sprintf('The argument passed to %s must be an instance of %s, instance of %s given, called in %s on line %s', __METHOD__, EntityInterface::class, get_class($entity), __FILE__, __LINE__));
        }
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return AddressManager
     */
    public function getManager(): AddressManager
    {
        return $this->manager;
    }
}
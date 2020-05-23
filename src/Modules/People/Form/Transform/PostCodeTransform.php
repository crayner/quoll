<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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
    public function setEntity(EntityInterface $entity): PostCodeTransform
    {
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
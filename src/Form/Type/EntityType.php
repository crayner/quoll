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
 * Date: 7/11/2019
 * Time: 09:33
 */
namespace App\Form\Type;

use App\Form\Transform\EntityToStringTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class EntityType
 * @package App\Form\Type
 * @deprecated Never Use, use Symfony EntityType
 */
class EntityType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * EntityType constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        trigger_error('Do not use this class, use Symfony EntityType.', E_USER_DEPRECATED);
        $this->em = $em;
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return \Symfony\Bridge\Doctrine\Form\Type\EntityType::class;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new EntityToStringTransformer($this->getEm(), $options));
    }

    /**
     * getEm
     * @return EntityManagerInterface
     */
    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }
}
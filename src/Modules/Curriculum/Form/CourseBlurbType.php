<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 31/08/2020
 * Time: 15:37
 */
namespace App\Modules\Curriculum\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Department\Entity\Department;
use App\Modules\School\Entity\YearGroup;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CourseType
 * @package App\Modules\Curriculum\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseBlurbType extends AbstractType
{
    /**
     * buildForm
     *
     * 31/08/2020 16:14
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', CKEditorType::class,
                [
                    'row_style' => 'single',
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 31/08/2020 15:39
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Curriculum',
                'data_class' => Course::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 31/08/2020 15:38
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}

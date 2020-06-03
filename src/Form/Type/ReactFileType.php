<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 4/09/2019
 * Time: 13:12
 */
namespace App\Form\Type;

use App\Form\EventSubscriber\ReactFileListener;
use App\Form\Transform\ReactFileTransformer;
use App\Manager\EntityInterface;
use App\Twig\Sidebar\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReactFileType
 * @package App\Form\Type
 */
class ReactFileType extends AbstractType
{
    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * ReactFileType constructor.
     * @param RequestStack $stack
     */
    public function __construct(RequestStack $stack)
    {
        $this->stack = $stack;
    }


    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return FileType::class;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ReactFileTransformer())
            ->addEventSubscriber(new ReactFileListener($this->stack));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'compound'          => false,
                'multiple'          => false,
                'type'              => 'file',
                'delete_security'   => false,
                'show_thumbnail'     => false,
                'image_method'       => null,
                'entity'            => null,
            ]
        );

        $resolver->setRequired(
            [
                'file_prefix',
            ]
        );

        $resolver->setAllowedTypes('delete_security', ['boolean', 'string']);
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['public_dir'] = realpath(__DIR__ . '/../../../public');
        $view->vars['value'] = $options['data'];
        $view->vars['delete_security'] = $options['delete_security'];
        $view->vars['photo'] = $this->buildPhoto($options, $view);
    }

    /**
     * buildPhoto
     * @param array $options
     * @param FormView $view
     * @return array
     */
    private function buildPhoto(array $options, FormView $view): array
    {
        if ($options['show_thumbnail'] === false)
            return ['exists' => false];

        $method = $options['image_method'];
        if ($method === null)
            throw new \TypeError(sprintf('The image_method in "%s" must be set when show_thumbnail is set to true.', $options['label']));
        if ($options['entity'] === null || !$options['entity'] instanceof EntityInterface)
            throw new \TypeError(sprintf('The entity in "%s" must be set or must be an object of type "App\Manager\EntityInterface" when show_thumbnail is set to true.', $options['label']));
        if (!method_exists($options['entity'], $method))
            throw new \TypeError(sprintf('The entity "%s" does not contain the image method "%s"', get_class($options['entity']), $method));

        $photo = new Photo($options['entity'], $method, '75', 'user max75 float-left');
        $domain = null;
        $formView = $view;
        while ($domain === null) {
            $domain = $formView->vars['translation_domain'];
            $formView = $formView->parent;
            if ($formView === null && $domain === null)
                $domain = 'messages';
        }

        return $photo->setTransDomain($domain)->setTitle($options['label'])->toArray();
    }
}
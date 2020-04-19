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
 * Date: 5/09/2019
 * Time: 09:37
 */

namespace App\Form\EventSubscriber;

use App\Util\ImageHelper;
use App\Util\JsonFileUploadHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;

/**
 * Class ReactFileListener
 * @package App\Form\EventSubscriber
 */
class ReactFileListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var integer
     */
    private $parents = 0;

    /**
     * ReactFileListener constructor.
     * @param RequestStack $stack
     * @param int $parents
     */
    public function __construct(RequestStack $stack, int $parents = 0)
    {
        $this->stack = $stack;
        $this->parents = $parents;
    }

    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'saveFile',
        ];
    }

    /**
     * saveFile
     * @param SubmitEvent $event
     */
    public function saveFile(SubmitEvent $event)
    {
        $request = $this->stack->getCurrentRequest();
        if ($request->getContentType() === 'json') {
            $form = $event->getForm();
            $this->getParentNames($form);
            $value = $this->getValueFromContent($form, json_decode($request->getContent(), true));
            if (empty($value)) {
                $event->setData(null);
                return;
            }
            $file = JsonFileUploadHelper::saveFile($value, $form->getConfig()->getOption('file_prefix'));
            if (null === $file) {
                $data = null;
            } else {
                $validator = Validation::createValidator();
                $x = $validator->validate($file, $form->getConfig()->getOption('constraints'));
                if ($x->count() > 0) {
                    unlink($file->getRealPath());
                    foreach ($x as $constraint)
                        $form->addError(new FormError($constraint->getMessage()));
                    $data = null;
                } else {
                    $public = realpath(__DIR__ . '/../../../public');
                    $data = str_replace($public, '', $file->getRealPath());

                    // Remove existing file..
                    $file = $form->getData();
                    if (!in_array($file, [null, '']) && $file !== $data) {
                        $file = realpath($file) ?: ($public . DIRECTORY_SEPARATOR . $file ?: false);
                        ImageHelper::deleteImage($file);
                    }
                }
            }
            $event->setData($data);
        }
    }

    /**
     * @return int
     */
    public function getParents(): int
    {
        return $this->parents;
    }

    /**
     * getContentValue
     * @param string $name
     * @param array $content
     * @param null $value
     * @return mixed
     */
    public function getContentValue(string $name, array $content, $value = null)
    {
        foreach($content as $q=>$w)
        {
            if ($q === $name)
                $value = $w;

            if (is_array($w) && $value === null)
                $value = $this->getContentValue($name, $w, $value);

            if (null !== $value)
                break;
        }
        return $value;
    }

    /**
     * @return RequestStack
     */
    public function getStack(): RequestStack
    {
        return $this->stack;
    }

    /**
     * getParentNames
     * @param FormInterface $form
     * @return array
     */
    public function getParentNames(FormInterface $form): array
    {
        $result = [];
        $result[] = $form->getName();

        for($x=0; $x < $this->getParents(); $x++) {
            $form = $form->getParent();
            array_unshift($result, $form->getName());
        }

        return $result;
    }

    /**
     * getValueFromContent
     * @param FormInterface $form
     * @param array|null $content
     * @return mixed
     */
    public function getValueFromContent(FormInterface $form, ?array $content)
    {
        if (null === $content)
            return $content;
        foreach($this->getParentNames($form) as $q=>$name) {
            if ($q === 0)
                $content = $this->getContentValue($name, $content);
            else
                $content = $content[$name];
        }
        return $content;
    }
}
<?php
namespace App\Form\Transform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FileToStringTransformer
 * @package App\Form\Transform
 */
class FileToStringTransformer implements DataTransformerInterface
{
    /**
	 * Transforms an string to File
	 *
	 * @param  string|null $data
	 *
	 * @return string
	 */
	public function transform($data): ?File
	{
	    if (null === $data)
	        return $data;
        $relative = __DIR__ . '/../../../public';
        $file = is_file($relative.$data) ? realpath($relative.$data) : '';

        $data = new File($file, $file !== '' ? true : false);
        return $data ?: null;
	}

	/**
	 * Transforms a File into a string.
	 *
	 * @param mixed $data
	 *
	 * @return null|string
	 * @internal param $ null|File
	 */
	public function reverseTransform($data)
	{
	    if (null === $data)
	        return $data;
        $relative = __DIR__ . '/../../../public';
        $file = is_file($data) ? $data : (is_file($relative.$data) ? realpath($relative.$data) : '');

        $data = new File($file, $file !== '' ? true : false);
        return $data ?: null;
	}
}
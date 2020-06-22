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
 * Date: 1/01/2020
 * Time: 13:56
 */

namespace App\Manager\Traits;


use App\Util\ImageHelper;
use Doctrine\ORM\Mapping as ORM;

trait ImageRemovalTrait
{
    /**
     * @var array
     */
    private $existingFiles = [];

    /**
     * clearExistingImage
     * @return $this
     * @ORM\PostUpdate())
     */
    public function clearExistingFile(): self
    {
        foreach($this->existingFiles as $property)
        {
            ImageHelper::deleteImage($this->existingFiles[$property]);
        }

        return $this;
    }

    /**
     * setExistingFile
     * @param string $property
     * @param string|null $default
     * @return $this
     */
    public function setExistingFile(string $property, ?string $default) : self
    {
        $method = 'get' . ucfirst($property);
        if (method_exists($this, $method))
        {
            if (null !== $this->$method() && $default !== $this->$method())
            {
                $this->existingFiles[$property] = $this->$method();
            }
        }
        return $this;
    }

    /**
     * removeFileContent
     * @return $this
     * @ORM\PostRemove()
     */
    public function removeFileContent(): self
    {
        if (property_exists($this, 'filePropertyList'))
        {
            foreach($this->filePropertyList as $property)
            {
                $method = 'get' . ucfirst($property);
                if (method_exists($this, $method))
                    ImageHelper::deleteImage($this->$method());
            }
        }

        return $this;
    }

    /**
     * isFileInPublic
     * @param string|null $file
     * @return bool
     */
    public function isFileInPublic(?string $file): bool
    {
        if (empty($file))
            return false;

        return ImageHelper::isFileInPublic($file);
    }
}
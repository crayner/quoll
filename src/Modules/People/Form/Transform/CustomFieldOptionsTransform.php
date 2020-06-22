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
 * Date: 18/05/2020
 * Time: 14:36
 */
namespace App\Modules\People\Form\Transform;

use App\Modules\People\Entity\CustomField;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CustomFieldOptionsTransform
 * @package App\Modules\People\Form\Transform
 */
class CustomFieldOptionsTransform implements DataTransformerInterface
{
    /**
     * @var CustomField
     */
    private $entity;

    /**
     * CustomFieldOptionsTransform constructor.
     * @param CustomField $entity
     */
    public function __construct(CustomField $entity)
    {
        $this->entity = $entity;
    }

    /**
     * transform
     * @param mixed $value
     * @return mixed|void
     */
    public function transform($value)
    {
        $type = $this->entity->getFieldType();
        if (empty($type)) {
            return $value;
        }

        switch($type) {
            case 'text':
                if (is_array($value) && key_exists('rows', $value)) {
                    return $value['rows'];
                }
                if (null === $value) {
                    return '4';
                }
            case 'short_string':
                if (is_array($value) && key_exists('length', $value)) {
                    return $value['length'];
                }
                if (null === $value) {
                    return 191;
                }
            case 'choice':
                if (is_array($value)) {
                    return $value;
                }
                break;
            default:
                return '';
        }

    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return mixed|void
     */
    public function reverseTransform($value)
    {
        $type = $this->entity->getFieldType();
        if (empty($type)) {
            return $value;
        }

        switch($type) {
            case 'text':
                return ['rows' => $value ?: '4'];
                break;
            case 'short_string':
                return ['length' => $value ?: 191];
                break;
            case 'choice':
                return $value ?: [];
                break;
            default:
                return [];
        }
    }

}
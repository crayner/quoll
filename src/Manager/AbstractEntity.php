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
 * Date: 21/05/2020
 * Time: 16:03
 */
namespace App\Manager;

use App\Modules\School\Entity\Scale;
use App\Provider\ProviderFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractEntity
 * @package App\Manager
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * getUpdates
     * @return array
     */
    public function getUpdates(): array
    {
        return [];
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): array
    {
        return [];
    }

    /**
     * isUpdateRequired
     * @param string|null $version
     * @return bool
     */
    public function isUpdateRequired(?string $version): bool
    {
        return version_compare(static::getVersion(), $version, '<') || null === $version;
    }

    /**
     * loadData
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function loadData(array $data)
    {
        foreach($data as $name=>$value) {
            if (is_array($value)) {
                if (method_exists($this, 'isArrayField') && $this->isArrayField($name)) {
                    $method = 'set' . ucfirst($name);
                    $this->$method($value);
                } else if ($name === 'convertDate') {
                    $this->convertDate($value);
                } else {
                    $resolver = new OptionsResolver();
                    $resolver->setRequired(
                        [
                            'table',
                            'reference',
                            'value',
                        ]
                    );
                    $value = $resolver->resolve($value);
                    $table = $value['table'];
                    $entity = ProviderFactory::create($table)->findOneByAndStore($value['reference'], $value['value']);
                    $method = 'set' . ucfirst($name);
                    if (!$entity instanceof Scale) dd($entity, ProviderFactory::create($table));
                    $this->$method($entity);
                }
            } else {
                $method = 'set' . ucfirst($name);
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * convertDate
     * @param array $field
     * @return mixed
     * @throws \Exception
     */
    public function convertDate(array $field)
    {
        $method = 'set' . ucfirst(array_key_first($field));
        $value = reset($field);
        return $this->$method(new \DateTimeImmutable($value));
    }
}
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
 * Date: 21/05/2020
 * Time: 16:03
 */
namespace App\Manager;

use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractEntity
 * @package App\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * getVersion
     * @return string
     * 17/07/2020 08:38
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

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
                if ((method_exists($this, 'isArrayField') && $this->isArrayField($name))) {
                    $method = 'set' . ucfirst($name);
                    $this->$method($value);
                } else if (array_key_first($value) === 'convertDate') {
                    $this->convertDate($name, $value);
                } else if (array_key_first($value) === 'arrayField') {
                    $method = 'set' . ucfirst($name);
                    $this->$method($value['arrayField']);
                } else if (key_exists('table', $value) || key_exists('reference', $value) || key_exists('value', $value)){
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
                    $this->$method($entity);
                } else {
                    $method = 'set' . ucfirst($name);
                    $this->$method($value);
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
     * @param string $name
     * @param array $field
     * @return mixed
     * @throws \Exception
     * 4/07/2020 12:45
     */
    public function convertDate(string $name, array $field)
    {
        $method = 'set' . ucfirst($name);
        $value = $field['convertDate'];
        return $this->$method(new \DateTimeImmutable($value));
    }

    /**
     * translateBoolean
     *
     * 26/10/2020 10:42
     * @param bool $value
     * @return string
     */
    public function translateBoolean(bool $value): string
    {
        return $value ? TranslationHelper::translate('Yes', [], 'messages') : TranslationHelper::translate('No', [], 'messages');
    }
}
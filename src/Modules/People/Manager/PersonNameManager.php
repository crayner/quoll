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
 * Date: 1/12/2019
 * Time: 08:40
 */

namespace App\Modules\People\Manager;

use App\Modules\People\Entity\Person;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonNameManager
 * @package App\Modules\People\Manager
 */
class PersonNameManager
{
    /**
     * @var array
     */
    private static $formats;

    /**
     * @return array
     */
    public static function getFormats(): array
    {
        return self::$formats ?: [];
    }

    /**
     * @param array $formats
     */
    public static function setFormats(array $formats = []): void
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults(
            [
                'staff' => [],
                'student' => [],
                'parent' => [],
                'other' => [],
            ]
        );

        $formats = $resolver->resolve($formats);

        foreach($formats as $q=>$w) {
            $resolver->clear();
            $resolver->setDefaults(
                [
                    'first' => [],
                    'preferred' => [],
                    'formal' => 'title first surname',
                ]
            );
            $formats[$q] = $resolver->resolve($w);
            foreach($formats[$q] as $e=>$r) {
                if ($e === 'formal')
                    continue;
                $resolver->clear();
                $resolver->setDefaults(
                    [
                        'short' => [],
                        'long' => [],
                    ]
                );
                $formats[$q][$e] = $resolver->resolve($r);
                foreach($formats[$q][$e] as $t=>$y) {
                    $resolver->clear();
                    $resolver->setDefaults(
                        [
                            'reversed' => 'surname, given',
                            'normal' => 'given surname',
                        ]
                    );
                    $formats[$q][$e][$t] = $resolver->resolve($y);
                }
            }
        }

        self::$formats = $formats;
    }

    /**
     * formatName
     * @param Person|array $person
     * @param array $options
     * @return string
     */
    public static function formatName($person, array $options): string
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'preferred' => true,
                'preferredName' => true,
                'reverse' => false,
                'informal' => true,
                'initial' => false,
                'title' => false,
                'style' => null,
                'debug' => false,
            ]
        );

        $resolver->setAllowedValues('style', ['long','short','formal',null]);

        $options = $resolver->resolve($options);
        if ($options['debug'])
        // Backwards Compat
        $options['preferred'] = $options['preferredName'] = $options['preferred'] && $options['preferredName'];

        $personType = $person instanceof Person ? $person->getPersonType() : (isset($person['personType']) ? $person['personType'] : 'other');

        $template = 'title first surname';

        if ($options['style'] === null)
        {
            if ($options['reverse'])
                $template = 'surname, first';

            if (!$options['title'])
                $template = str_replace('title ', '', $template);

            if ($options['informal'] || $options['preferredName'])
                $template = str_replace('first', 'preferred', $template);

            if ($options['informal'] )
                $template = str_replace('title', '', $template);

            if ($options['initial'])
                $template = str_replace(['first', 'preferred', 'given'], 'initial', $template);
        } else {
            $styles = self::getFormatByPersonType($personType);
            if ($options['style'] === 'formal')
            {
                $options['preferred'] = $options['preferredName'] = false;
            }
            $template = 'formal';
            $length = 'long';
            if ($options['initial']) {
                $template = 'first';
                $length = 'short';
            }
            $direction = 'normal';
            if ($options['reverse']) {
                $direction = 'reversed';
                $template = 'first';
            }
            if ($options['informal'] || $options['preferred'])
                $template = 'preferred';

            if ($template === 'formal')
                $template = 'title first surname';
            else
                $template = isset($styles[$template][$length][$direction]) ? $styles[$template][$length][$direction] : 'title first surname';

            $template = str_replace('given', 'first', $template);
            if ($options['informal'] || $options['preferred'])
                $template = str_replace('first', 'preferred', $template);
        }

        $template = trim($template);

        if ($options['debug'])
            dump($template,$person,$options);

        $data = $person;
        if ($person instanceof Person)
            $data = [
                'first' => $person->getFirstName(),
                'surname' => $person->getSurname(),
                'preferred' => $person->getPreferredName(),
                'title' => $person->getTitle(),
                'initial' => substr($person->getFirstName(), 0,1).'.',
            ];

        $name = trim(str_replace(
            array_keys($data),
            array_values($data),
            $template)
        );
        return $name;
    }

    /**
     * getFormatByPersonType
     * @param string $personType
     * @return array
     */
    private static function getFormatByPersonType(string $personType): array
    {
        return isset(self::getFormats()[strtolower($personType)]) ? self::getFormats()[strtolower($personType)] : [];
    }
}
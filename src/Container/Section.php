<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/06/2020
 * Time: 10:04
 */
namespace App\Container;

use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

/**
 * Class Section
 * @package App\Container
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Section
{
    /**
     * @var string|null
     */
    private $style;

    /**
     * @var string[]
     */
    private static $styleList = [
        'html',
        'text',
        'pagination',
        'form',
        'special',
    ];

    /**
     * @var mixed
     */
    private $content;

    /**
     * Section constructor.
     * @param string|null $style
     * @param mixed $content
     */
    public function __construct(?string $style, $content)
    {
        $this->setStyle($style);
        $this->setContent($content);
    }

    /**
     * @return string|null
     */
    public function getStyle(): ?string
    {
        return $this->style;
    }

    /**
     * setStyle
     * @param string|null $style
     * @return $this
     * 20/06/2020 10:20
     */
    public function setStyle(?string $style): Section
    {
        if (!in_array($style, self::$styleList)) {
            throw new InvalidArgumentException(sprintf('The style "%s" must be one of "%s."', $style, implode('","', self::$styleList)));
        }
        $this->style = $style;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Section
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * toArray
     * @return array
     * 21/06/2020 10:50
     */
    public function toArray(): array
    {
        if (is_object($this->getContent())) {
            return [
                'content' => $this->getContent()->toArray(),
                'style' => $this->getStyle(),
            ];
        }
        return [
            'style' => $this->getStyle(),
            'content' => $this->getContent(),
        ];
    }
}